<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process retry controller
 */
class Retry extends \Magento\Backend\App\Action
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    protected $processFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\ProcessFactory $processFactory
    ) {
        $this->helper = $helper;
        $this->processFactory = $processFactory;
        parent::__construct($context);
    }

    /**
     * Retry action
     *
     * @return
     */
    public function execute()
    {

        $processId = $this->getRequest()->getParam('id');
        $processModel = $this->processFactory->create();
        $process = $processModel->load($processId);

        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$process->getId()) {
            $this->messageManager->addError(__('This process does not exist.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->retry($process);
            $processCollection = $processModel->getCollection();
            $processCollection->saveErrors();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Retry a process
     *
     * @param \Dsync\Dsync\Model\Process $process
     * @throws \Exception
     */
    protected function retry(\Dsync\Dsync\Model\Process $process)
    {
        if ($process->getIsLocked()) {
            throw new \Exception(__(
                'Process # %1 : The process is currently locked, please try again later.',
                $process->getId()
            ));
        }

        // lock the process before trying to process it
        $process
            ->setIsLocked(true)
            ->save();

        try {
            //add another retry
            $process->setRetry($process->getRetry() + 1)->save();
            $process->retry();
            $this->messageManager->addSuccess(__(
                'Process # %1 : This entity was successfully processed.',
                $process->getId()
            ));
        } catch (\Exception $e) {
            $this->getHelper()->log($e->getMessage());
            $this->messageManager->addError(__('Process # %1 : ' . $e->getMessage(), $process->getId()));
            $retry = $process->getRetry() - 1;
            $process->setRetry($retry)->save();
            if ($retry == 0) {
                $process
                    ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR);
            } else {
                $process
                    ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY);
            }
            $process->save();
        }
        // unlock the source after it has been processed
        $process->setIsLocked(false)->save();
    }

    /**
     * @return bool
     *
     * @codingStandardsIgnoreStart
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dsync_Dsync::config_dsync');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
