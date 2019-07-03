<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api\Route;

/**
 * Notification route class
 */
class Notification extends \Dsync\Dsync\Model\Api\Route\AbstractRoute
{

    /**
     * @var \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
     */
    protected $processCollectionFactory;

    /**
     * @var \Dsync\Dsync\Model\NotificationFactory $notificationFactory
     */
    protected $notificationFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\NotificationFactory $notificationFactory
     * @param \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\NotificationFactory $notificationFactory,
        \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->processCollectionFactory = $processCollectionFactory;
        parent::__construct($helper);
    }

    /**
     * Dispatch the request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function dispatch($request)
    {
        $rawBody = $request->getContent();
        $body = json_decode($rawBody);

        $processId = $request->getHeader('Process-Id');
        $body->process_id = $processId;

        if (!$processId) {
            throw new \Dsync\Dsync\Exception('Process ID is missing from the request.');
        }

        $this->validateBody($body);

        $collection = $this->processCollectionFactory
            ->create()
            ->addFieldToFilter('status', array(
                'neq' => \Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE
            ))
            ->addFieldToFilter('process_id', $body->process_id);

        $process = $collection->getFirstItem();

        if (!$process->getId()) {
            throw new \Dsync\Dsync\Exception('Invalid process id: ' . $body->process_id);
        }
        $error = null;
        if ($body->status != \Dsync\Dsync\Model\Api\Response\Code::HTTP_OK) {
            $error = $body->message;
        }
        foreach ($body->data->notifications as $notification) {
            $processNotification = $this->notificationFactory->create()
                ->setProcessId($body->process_id)
                ->setRelationId($notification->relation_id)
                ->setStatus($notification->status)
                ->setMessage($notification->message);
            $process->addProcessNotification($processNotification);
        }
        if ($error) {
            $process
                ->setMessage($error)
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR);
        } else {
            $process
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE);
        }
        $process
            ->save();
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
        return $this->getResponse();
    }

    /**
     * Validate the body of the notification request
     *
     * @param object $body
     * @throws \Dsync\Dsync\Exception
     */
    protected function validateBody($body)
    {
        if (!isset($body->status)) {
            throw new \Dsync\Dsync\Exception('Status is missing from the request.');
        }

        if (!isset($body->message)) {
            throw new \Dsync\Dsync\Exception('Message is missing from the request.');
        }

        if (!isset($body->data->notifications)) {
            throw new \Dsync\Dsync\Exception('Notification data is missing from the request.');
        }
    }
}
