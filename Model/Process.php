<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

use Dsync\Dsync\Api\Data\ProcessInterface;

/**
 * Process class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Process extends AbstractModel implements IdentityInterface, ProcessInterface
{
    const CACHE_TAG = 'dsync_dsync_process';

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Request $apiRequest
     */
    protected $apiRequest;

    /**
     * @var \Dsync\Dsync\Model\RequestFactory $requestFactory
     */
    protected $requestFactory;

    /**
     * @var \Dsync\Dsync\Model\Request $request
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestData;

    /**
     * @var \Dsync\Dsync\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var array
     */
    protected $processNotifications = [];

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     */
    protected $entityTypeModel;

    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init('Dsync\Dsync\Model\ResourceModel\Process');
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\RequestFactory $requestFactory
     * @param \Dsync\Dsync\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
     * @param \Dsync\Dsync\Model\Api\Request $apiRequest
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\RequestFactory $requestFactory,
        \Dsync\Dsync\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory,
        \Dsync\Dsync\Model\Api\Request $apiRequest,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->apiRequest = $apiRequest;
        $this->requestFactory = $requestFactory;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->entityTypeModel = $entityTypeModel;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get the unique identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set a request object
     *
     * @param \Dsync\Dsync\Model\Request $request
     * @return \Dsync\Dsync\Model\Process
     */
    public function setRequest(\Dsync\Dsync\Model\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set request data
     *
     * @param string $requestData
     * @return \Dsync\Dsync\Model\Process
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
        $this->setIsNewRequestData(true);
        return $this;
    }

    /**
     * Get a request based on this id
     *
     * @return \Dsync\Dsync\Model\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $request = $this->requestFactory->create();
            $this->request = $request->load($this->getId(), 'process_id');
        }
        return $this->request;
    }

    /**
     * Get request Data
     *
     * @return string
     */
    public function getRequestData()
    {
        if (!$this->request) {
            $this->requestData = $this->getRequest()->getRequestData();
        }
        return $this->requestData;
    }

    /**
     * Before save event
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function beforeSave()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($this->getHelper()->getUtcDate());
        }
        $this->setUpdatedAt($this->getHelper()->getUtcDate());
        if ($this->getStatus() == \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR ||
                $this->getStatus() == \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR
        ) {
            $this->setIsError(true);
        } else {
            $this->setIsError(false);
        }
        return parent::beforeSave();
    }

    /**
     * After save event
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function afterSave()
    {
        if ($this->getIsNewRequestData()) {
            $this
                ->getRequest()
                ->setProcessId($this->getId())
                ->setRequestData($this->getRequestData())
                ->save();
        }
        foreach ($this->processNotifications as $notification) {
            $notification->save();
        }
        return parent::afterSave();
    }

    /**
     * After delete event
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function afterDelete()
    {
        $request = $this->getRequest();
        if ($request->getId()) {
            $request->delete();
        }
        foreach ($this->getProcessNotifications() as $notification) {
            $notification->delete();
        }
        return parent::afterDelete();
    }

    /**
     * Cancel a process
     */
    public function cancel()
    {
        switch ($this->getRequestType()) {
            case \Dsync\Dsync\Model\System\Config\Source\Request\Type::SOURCE:
                $status = \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR;
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION:
                $status = \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR;
                break;
        }
        $this
            ->setRetry(0)
            ->setStatus($status)
            ->save();
    }

    /**
     * Retry to process a request
     *
     * @return \Dsync\Dsync\Model\Process
     * @throws \Exception
     */
    public function retry()
    {
        if ($this->getRequestType()) {
            $entity = $this->getHelper()->createEntity($this->getEntityType());
            if (!$entity->isProcessable()) {
                throw new \Exception('Process # %1 : This entity is not enabled on this system.', $this->getId());
            }
            $this
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PROCESSING)
                ->save();
            switch ($this->getRequestType()) {
                case \Dsync\Dsync\Model\System\Config\Source\Request\Type::SOURCE:
                    return $this->retrySource($entity);
                case \Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION:
                    return $this->retryDestination($entity);
            }
        }
    }

    /**
     * Retry to process a source request
     *
     * @param \Dsync\Dsync\Model\Entity\EntityAbstract $entity
     * @return \Dsync\Dsync\Model\Process
     * @throws \Exception
     */
    protected function retrySource($entity)
    {
        $this->apiRequest
            ->resetRequest()
            ->setIsRetry(true)
            ->setRequestMethod($this->getMethod())
            ->setRequestEntity($entity)
            ->setProcessModel($this)
            ->send();
        // allow the request to save the successful data and change the status
        return $this;
    }

    /**
     * Retry to process a destination request
     *
     * @param \Dsync\Dsync\Model\Entity\EntityAbstract $entity
     * @return \Dsync\Dsync\Model\Process
     */
    protected function retryDestination($entity)
    {
        $destinationData = json_decode($this->getRequestData());
        $responseArray = [];
        $entity
            ->setDsyncEntityId($this->getDsyncEntityId())
            ->setDestinationData($destinationData);
        switch ($this->getMethod()) {
            case \Dsync\Dsync\Model\Api\Request\Method::CREATE:
                $responseArray = $entity->create();
                break;
            case \Dsync\Dsync\Model\Api\Request\Method::READ:
                $entity->read();
                break;
            case \Dsync\Dsync\Model\Api\Request\Method::UPDATE:
                $responseArray = $entity->update();
                break;
            case \Dsync\Dsync\Model\Api\Request\Method::DELETE:
                $responseArray = $entity->delete();
                break;
        }
        if (!empty($responseArray)) {
            foreach ($responseArray as $key => $value) {
                $this->setDsyncEntityIdField($key);
                $this->setDsyncEntityId($value);
            }
        }
        $this
            ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE)
            ->setRetry(0)
            ->setMessage(null)
            ->save();

        return $this;
    }

    /**
     * Send a process notification for the current process based on the status
     */
    public function sendProcessNotification()
    {
        $entityToken = $this->entityTypeModel->getEntityTokenByEntityType($this->getEntityType());

        $request = $this
            ->apiRequest
            ->resetRequest()
            ->setRequestProcessId($this->getProcessId())
            ->setRequestEntityToken($entityToken)
            ->setSystemType($this->getSystemType())
            ->setUri('/notification');
        $final = false;
        $error = false;
        switch ($this->getStatus()) {
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY:
                $request
                    ->setRequestStatus(\Dsync\Dsync\Model\Api\Response\Code::HTTP_ACCEPTED);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR:
                $request
                    ->setRequestStatus(\Dsync\Dsync\Model\Api\Response\Code::HTTP_INTERNAL_ERROR)
                    ->setRequestMessage($this->getMessage());
                $final = true;
                break;
            default:
                if ($this->getMethod() == \Dsync\Dsync\Model\Api\Request\Method::CREATE) {
                    $request
                        ->setRequestStatus(\Dsync\Dsync\Model\Api\Response\Code::HTTP_CREATED);
                } else {
                    $request
                        ->setRequestStatus(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
                }
                $requestData = array(
                    $this->getDsyncEntityIdField() => $this->getDsyncEntityId()
                );
                $request->setRequestData($requestData);
                $final = true;
                break;
        }
        try {
            $request->send();
            if ($final) {
                $this->setNotificationNeeded(false)->save();
            }
        } catch (\Dsync\Dsync\Exception $e) {
            $this->getHelper()->log($e->getMessage(), null, 'dsync-process-notification.log');
            $error = true;
        } catch (\Exception $e) {
            $this->getHelper()->log($e->getMessage(), null, 'dsync-process-notification.log');
            $error = true;
        }
        if ($final && $error) {
            $this->setNotificationNeeded(true)->save();
        }
    }

    /**
     * Add a process notification
     *
     * @param \Dsync\Dsync\Model\Notification $processNotification
     * @return \Dsync\Dsync\Model\Process
     */
    public function addProcessNotification(\Dsync\Dsync\Model\Notification $processNotification)
    {
        $processNotifications = $this->processNotifications;
        $processNotifications[] = $processNotification;
        $this->processNotifications = $processNotifications;
        return $this;
    }

    /**
     * Get an array of process notifications
     *
     * @return array
     */
    public function getProcessNotifications()
    {
        if (empty($this->processNotifications)) {
            $processNotifications = $this->processNotifications;
            $notificationCollection = $this->notificationCollectionFactory
                ->create()
                ->addFieldToFilter('process_id', $this->getProcessId());
            foreach ($notificationCollection as $notification) {
                $processNotifications[] = $notification;
                $this->processNotifications = $processNotifications;
            }
        }
        return $this->processNotifications;
    }

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }

    /**
     * Return the Dsync Registry
     *
     * @return \Dsync\Dsync\Model\Registry
     */
    protected function getRegistry()
    {
        return $this->getHelper()->getRegistry();
    }
}
