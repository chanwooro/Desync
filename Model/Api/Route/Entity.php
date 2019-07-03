<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api\Route;

/**
 * Entity route class
 */
class Entity extends \Dsync\Dsync\Model\Api\Route\AbstractRoute
{

    const SET_JOB = 'set_job';
    const SET_SHAREDKEY = 'set_sharedkey';

    /**
     * @var \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     */
    protected $requestMethodModel;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var \Dsync\Dsync\Model\Entity\AbstractEntity $entityModel
     */
    protected $entityModel;

    /**
     *
     * @var \Dsync\Dsync\Model\Process $processModel
     */
    protected $processModel;

    /**
     * @var \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    protected $processFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     * @param \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\ProcessFactory $processFactory,
        \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
    ) {
        $this->processFactory = $processFactory;
        $this->requestMethodModel = $requestMethodModel;
        parent::__construct($helper);
    }

    /**
     * Set the entity model
     *
     * @param \Dsync\Dsync\Model\Entity\AbstractEntity $entityModel
     * @return \Dsync\Dsync\Model\Api\Route\Entity
     */
    public function setEntityModel($entityModel)
    {
        $this->entityModel = $entityModel;
        return $this;
    }

    /**
     * Set the process model
     *
     * @param \Dsync\Dsync\Model\Process $processModel
     * @return \Dsync\Dsync\Model\Api\Route\Entity
     */
    public function setProcessModel($processModel)
    {
        $this->processModel = $processModel;
        return $this;
    }

    /**
     * Set the method
     *
     * @param string $method
     * @return \Dsync\Dsync\Model\Api\Route\Entity
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the entity model
     *
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function getEntityModel()
    {
        return $this->entityModel;
    }

    /**
     * Get the process model
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function getProcessModel()
    {
        return $this->processModel;
    }

    /**
     * Dispatch the request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function dispatch($request)
    {
        $entityType = null;
        foreach ($request->getParams() as $key => $value) {
            if ($key) {
                $entityType = $key;
                $setJob = $value;
                break;
            }
        }

        $entityModel = $this->getHelper()->createEntity($entityType);
        if (!$entityModel) {
            throw new \Dsync\Dsync\Exception('Invalid entity supplied.');
        }

        $destinationData = json_decode($request->getContent());

        if ($setJob == self::SET_JOB) {
            return $this->setJob($entityType, $destinationData);
        }

        if ($setJob == self::SET_SHAREDKEY) {
            return $this->setSharedKey($entityType, $destinationData);
        }

        if (!$entityModel->isProcessable()) {
            throw new \Dsync\Dsync\Exception('This entity is not enabled on this system.', 406);
        }
        $this->setEntityModel($entityModel);

        $method = $this->requestMethodModel->getMethod($request->getMethod());

        // verify that the data node is the correct type
        // if this is not a delete request
        if ($method != \Dsync\Dsync\Model\Api\Request\Method::DELETE) {
            if (!is_object($destinationData)) {
                $error = __(
                    'Invalid type supplied for entity data, %1 provided.',
                    gettype($destinationData)
                );
                throw new \Dsync\Dsync\Exception($error);
            }
        }

        $processId = $request->getHeader('Process-Id');
        $this->getEntityModel()->setDestinationData($destinationData);

        if (!$processId) {
            throw new \Dsync\Dsync\Exception('Process-Id is required for this entity request.');
        }
        if (!$method) {
            throw new \Dsync\Dsync\Exception(__('Invalid method type used (%1).', $request->getMethod()));
        }
        $dsyncEntityId = null;
        if ($dsyncEntityId = $request->getHeader('Entity-Id')) {
            $this->getEntityModel()->setDsyncEntityId($dsyncEntityId);
        }
        if ($method != \Dsync\Dsync\Model\Api\Request\Method::CREATE) {
            if (!$dsyncEntityId) {
                $error = __('Entity-Id is required for this %1 request.', $method);
                throw new \Dsync\Dsync\Exception($error);
            }
        }

        // set the method to be used with processing
        $this->setMethod($method);

        // if it gets here the request can be saved before trying to process it
        $processModel = $this->processFactory->create();
        $this->setProcessModel($processModel);
        $this
            ->getProcessModel()
            ->setProcessId($processId)
            ->setMethod($method)
            ->setEntityType($entityType)
            ->setDsyncEntityId($dsyncEntityId)
            ->setSystemType($this->getHelper()->getSystemType())
            ->setRequestData(json_encode($destinationData))
            ->setRequestType(\Dsync\Dsync\Model\System\Config\Source\Request\Type::DESTINATION)
            ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PROCESSING)
            ->setIsLocked(true)
            ->save();

        return $this->process();
    }

    /**
     * Save a job id against a entity type in system config
     *
     * @param string $entityType
     * @param object $destinationData
     * @return \Dsync\Dsync\Model\Api\Response
     * @throws \Dsync\Dsync\Exception
     */
    protected function setJob($entityType, $destinationData)
    {
        try {
            if (!isset($destinationData->job_id)) {
                $jobId = null;
                $this->getHelper()->saveStoreConfig('entity_config/' . $entityType, false);
            } else {
                $jobId = $destinationData->job_id;
                $this->getHelper()->saveStoreConfig('entity_config/' . $entityType, true);
            }
            $this->getHelper()->saveStoreConfig('job_id/' . $entityType, $jobId);
            // clean the configuration cache so that the updated
            // values can be used right away
            $this->getHelper()->cleanCache('config');
            $this->getResponse()
                ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
            return $this->getResponse();
        } catch (\Exception $e) {
            throw new \Dsync\Dsync\Exception($e->getMessage());
        }
    }

    /**
     * Save a shared key against a entity type in system config
     *
     * @param string $entityType
     * @param object $destinationData
     * @return \Dsync\Dsync\Model\Api\Response
     * @throws \Dsync\Dsync\Exception
     */
    protected function setSharedKey($entityType, $destinationData)
    {
        try {
            if (!isset($destinationData->shared_key)) {
                $key = null;
            } else {
                $key = $destinationData->shared_key;
            }
            $this->getHelper()->saveStoreConfig('shared_key/' . $entityType, $key);
            // clean the configuration cache so that the updated
            // values can be used right away
            $this->getHelper()->cleanCache('config');
            $this->getResponse()
                ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
            return $this->getResponse();
        } catch (\Exception $e) {
            throw new \Dsync\Dsync\Exception($e->getMessage());
        }
    }

    /**
     * Process the request
     *
     * @return \Dsync\Dsync\Model\Api\Response
     * @throws \Dsync\Dsync\Exception
     */
    protected function process()
    {
        try {
            // try to process the current request
            switch ($this->getMethod()) {
                case \Dsync\Dsync\Model\Api\Request\Method::CREATE:
                    $this->create();
                    break;
                case \Dsync\Dsync\Model\Api\Request\Method::READ:
                    $this->read();
                    break;
                case \Dsync\Dsync\Model\Api\Request\Method::UPDATE:
                    $this->update();
                    break;
                case \Dsync\Dsync\Model\Api\Request\Method::DELETE:
                    $this->delete();
                    break;
                default:
                    throw new \Dsync\Dsync\Exception('Unknown request method.');
            }
            $this
                ->getProcessModel()
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE)
                ->setMessage(null)
                ->setIsLocked(false)
                ->save();
        } catch (\Dsync\Dsync\Exception $e) {
            // change the status as it can not be processed again
            $this
                ->getProcessModel()
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR)
                ->setMessage($e->getMessage())
                ->setIsLocked(false)
                ->save();
            // throw Dsync Exception again as this is a client error
            throw new \Dsync\Dsync\Exception($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // catch exceptions here so we can save the request and retry later
            $this
                ->getProcessModel()
                ->setRetry($this->getHelper()->getMaxRetries())
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY)
                ->setMessage($e->getMessage())
                ->setIsLocked(false)
                ->save();
            $this->getResponse()
                ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_ACCEPTED);
        }
        return $this->getResponse();
    }

    /**
     * Process a create request
     */
    public function create()
    {
        $responseArray = $this->getEntityModel()->create();
        if (is_array($responseArray)) {
            $this->getResponse()->setResponseData($responseArray);
        }
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_CREATED);
    }

    /**
     * Process a read request
     */
    public function read()
    {
        $responseData = $this->getEntityModel()->read();

        $this->getResponse()->setResponseData($responseData);

        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
    }

    /**
     * Process an update request
     */
    public function update()
    {
        $responseArray = $this->getEntityModel()->update();
        if (is_array($responseArray)) {
            $this->getResponse()->setResponseData($responseArray);
        }
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
    }

    /**
     * Process a delete request
     */
    public function delete()
    {
        $responseArray = $this->getEntityModel()->delete();
        if (is_array($responseArray)) {
            $this->getResponse()->setResponseData($responseArray);
        }
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK);
    }
}
