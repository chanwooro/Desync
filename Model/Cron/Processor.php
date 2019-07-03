<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Cron;

/**
 * Cron processor class
 */
class Processor
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Request $request
     */
    protected $request;

    /**
     * @var \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    protected $processFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\ProcessFactory $processFactory
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->processFactory = $processFactory;
    }

    /**
     * Retry source and destination requests cron job
     */
    public function retryProcesses()
    {
        $this->getHelper()->log('Running: retry', null, 'dsync-cron.log');

        $processModel = $this->processFactory->create();
        $processCollection = $processModel->getCollection();
        $processCollection
            ->addFieldToFilter('retry', array('neq' => 0))
            ->addFieldToFilter('is_locked', false);

        // lock the collection before trying to process it
        $this->lock($processCollection);

        foreach ($processCollection as $process) {
            // flush registry values
            $this->getHelper()->getRegistry()->flushAll();

            try {
                $process->retry();
            } catch (\Exception $e) {
                $this->getHelper()->log($e->getMessage(), null, 'dsync-cron.log');
                $retry = $process->getRetry() - 1;
                $process
                    ->setRetry($retry)
                    ->setMessage($e->getMessage());
                if ($retry == 0) {
                    switch ($process->getRequestType()) {
                        case \Dsync\Dsync\Model\System\Config\Source\Request\Type::SOURCE:
                            $status = \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR;
                            break;
                        case \Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION:
                            $status = \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR;
                            break;
                    }
                    $process
                        ->setStatus($status);
                } else {
                    $process
                        ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY);
                }
                $process->save();
            }
            if ($process->getRequestType() == \Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION) {
                $process->sendProcessNotification();
            }
        }
        // unlock the collection after it has been processed
        $this->unlock($processCollection);
        $this->getHelper()->log('Finished: retry', null, 'dsync-cron.log');
    }

    /**
     * Send the notifications cron job
     */
    public function sendNotifications()
    {
        $this->getHelper()->log('Running: send notifications', null, 'dsync-cron.log');
        $processModel = $this->processFactory->create();
        $processCollection = $processModel->getCollection();
        $processCollection
            ->addFieldToFilter('request_type', \Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION)
            ->addFieldToFilter('notification_needed', true);

        // lock the collection before trying to process it
        $this->lock($processCollection);

        foreach ($processCollection as $process) {
            $process->sendProcessNotification();
        }
        // unlock the collection after it has been processed
        $this->unlock($processCollection);
        $this->getHelper()->log('Finished: send notifications', null, 'dsync-cron.log');
    }

    /**
     * Lock a collection of items
     *
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $collection
     */
    protected function lock($collection)
    {
        foreach ($collection as $entity) {
            $entity->setIsLocked(true)->save();
        }
    }

    /**
     * Unlock a collection of items
     *
     * @param \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection $collection
     */
    protected function unlock($collection)
    {
        foreach ($collection as $entity) {
            $entity->setIsLocked(false)->save();
        }
    }

    /**
     * Check for process errors cron job
     */
    public function checkErrors()
    {
        $this->getHelper()->log('Running: check errors', null, 'dsync-cron.log');
        $processModel = $this->processFactory->create();
        $processCollection = $processModel->getCollection();
        $processCollection->saveErrors();
        $this->getHelper()->log('Finished: check errors', null, 'dsync-cron.log');
    }

    /**
     * Clean data cron job
     */
    public function cleanData()
    {
        $this->getHelper()->log('Running: clean data', null, 'dsync-cron.log');
        if (!$this->getHelper()->isCleaningEnabled()) {
            $this->getHelper()->log('-- clean data is not enabled', null, 'dsync-cron.log');
            $this->getHelper()->log('Finished: clean data', null, 'dsync-cron.log');
            return;
        }

        $cleanMinutes = $this->getHelper()->getCleaningMinutes();

        // clean everything associated with a complete or unrecoverable status
        $processModel = $this->processFactory->create();
        $processCollection = $processModel->getCollection();
        $processCollection
            ->addFieldToFilter('status', array(
                'in' => array(
                    \Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE,
                    \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR
                )
            ))
            ->addFieldToFilter('notification_needed', array('neq' => 1));
        foreach ($processCollection as $process) {
            if ($this
                    ->getHelper()
                    ->getMinutesBetweenDates(
                        $process->getUpdatedAt(),
                        $this->getHelper()->getUtcDate()
                    ) > $cleanMinutes
            ) {
                $this->deleteProcess($process);
            }
        }
        $this->getHelper()->log('Finished: clean data', null, 'dsync-cron.log');
    }

    /**
     * Delete a process if it is not locked
     *
     * @param \Dsync\Dsync\Model\Process $process
     */
    protected function deleteProcess(\Dsync\Dsync\Model\Process $process)
    {
        if ($process->getIsLocked()) {
            return;
        }
        $process->delete();
    }

    /**
     * Get the helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
