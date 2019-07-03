<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api;

/**
 * Api request class
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Request
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Client $client
     */
    protected $client;

    /**
     * @var \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     */
    protected $requestMethodModel;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var int
     */
    protected $systemType;

    /**
     * @var boolean
     */
    protected $isRetry;

    /**
     * @var \Dsync\Dsync\Model\Process $processModel
     */
    protected $processModel;

    /**
     * @var \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    protected $processFactory;

    /**
     * @var string
     */
    protected $requestJobId;

    /**
     * @var string
     */
    protected $requestEntityToken;

    /**
     * @var string
     */
    protected $requestDsyncEntityIdField;

    /**
     * @var string
     */
    protected $requestDsyncEntityId;

    /**
     * @var string
     */
    protected $requestProcessId;

    /**
     * @var string
     */
    protected $requestMessage;

    /**
     * @var string
     */
    protected $requestStatus;

    /**
     * @var array
     */
    protected $requestDetail;

    /**
     * @var string
     */
    protected $requestEntityName;

    /**
     * @var string
     */
    protected $requestMethod;

    /**
     * @var object
     */
    protected $requestEntity;

    /**
     * @var array
     */
    protected $requestData;

    /**
     * @var \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel
     */
    protected $responseCodeModel;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Client $client
     * @param \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel
     * @param \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Client $client,
        \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel,
        \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel,
        \Dsync\Dsync\Model\ProcessFactory $processFactory
    ) {
        $this->helper = $helper;
        $this->client = $client;
        $this->responseCodeModel = $responseCodeModel;
        $this->requestMethodModel = $requestMethodModel;
        $this->processFactory = $processFactory;
    }

    /**
     * Reset the values of the request class
     * Used for making multiple requests within a single call
     *
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function resetRequest()
    {
        $this->processModel = null;
        $this->isRetry = null;
        $this->method = null;
        $this->uri = null;
        $this->systemType = null;
        $this->requestJobId = null;
        $this->requestEntityToken = null;
        $this->requestProcessId = null;
        $this->requestMessage = null;
        $this->requestStatus = null;
        $this->requestDetail = null;
        $this->requestEntityName = null;
        $this->requestMethod = null;
        $this->requestEntity = null;
        $this->requestData = null;
        $this->requestDsyncEntityIdField = null;
        $this->requestDsyncEntityId = null;
        return $this;
    }

    /**
     * Set the method
     *
     * @param string $method
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set if this is a retry
     *
     * @param boolean $isRetry
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setIsRetry($isRetry)
    {
        $this->isRetry = $isRetry;
        return $this;
    }

    /**
     * Set the uri
     *
     * @param string $uri
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Set the system type
     *
     * @param int $systemType
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
        return $this;
    }

    /**
     * Set the process model
     *
     * @param \Dsync\Dsync\Model\Process $processModel
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setProcessModel($processModel)
    {
        $this->processModel = $processModel;
        return $this;
    }

    /**
     * Set the job id
     *
     * @param string $requestJobId
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestJobId($requestJobId)
    {
        $this->requestJobId = $requestJobId;
        return $this;
    }

    /**
     * Set the entity token
     *
     * @param type $requestEntityToken
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestEntityToken($requestEntityToken)
    {
        $this->requestEntityToken = $requestEntityToken;
        return $this;
    }

    /**
     * Set the entity id field
     *
     * @param string $requestDsyncEntityIdField
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestDsyncEntityIdField($requestDsyncEntityIdField)
    {
        $this->requestDsyncEntityIdField = $requestDsyncEntityIdField;
        return $this;
    }

    /**
     * Set the dsync entity id
     *
     * @param string $requestDsyncEntityId
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestDsyncEntityId($requestDsyncEntityId)
    {
        $this->requestDsyncEntityId = $requestDsyncEntityId;
        return $this;
    }

    /**
     * Set the process id
     *
     * @param string $requestProcessId
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestProcessId($requestProcessId)
    {
        $this->requestProcessId = $requestProcessId;
        return $this;
    }

    /**
     * Set the entity name
     *
     * @param string $requestEntityName
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestEntityName($requestEntityName)
    {
        $this->requestEntityName = $requestEntityName;
        return $this;
    }

    /**
     * Set the request method
     *
     * @param string $requestMethod
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        return $this;
    }

    /**
     * Set the request entity
     *
     * @param object $requestEntity
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestEntity($requestEntity)
    {
        $this->requestEntity = $requestEntity;
        return $this;
    }

    /**
     * Set the request data
     *
     * @param array $requestData
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }

    /**
     * Set the request status
     *
     * @param string $requestStatus
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestStatus($requestStatus)
    {
        $this->requestStatus = $requestStatus;
        return $this;
    }

    /**
     * Set the request message
     *
     * @param string $requestMessage
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestMessage($requestMessage)
    {
        $this->requestMessage = $requestMessage;
        return $this;
    }

    /**
     * Set the request detail
     *
     * @param array $requestDetail
     * @return \Dsync\Dsync\Model\Api\Request
     */
    public function setRequestDetail($requestDetail)
    {
        $this->requestDetail = $requestDetail;
        return $this;
    }

    /**
     * Get the method or automatically set one
     *
     * @return string
     */
    public function getMethod()
    {
        if (!$this->method) {
            return \Zend\Http\Request::METHOD_POST;
        }
        return $this->method;
    }

    /**
     * Get the uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the system type
     *
     * @return int
     */
    public function getSystemType()
    {
        if (!$this->systemType) {
            $this->systemType = $this->getHelper()->getSystemType();
        }
        return $this->systemType;
    }

    /**
     * Get if this is a retry
     *
     * @return boolean
     */
    public function isRetry()
    {
        return $this->isRetry;
    }

    /**
     * Get the set process model or create a blank one
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function getProcessModel()
    {
        if (!$this->processModel) {
            $this->processModel = $this->processFactory->create();
        }
        return $this->processModel;
    }

    /**
     * Get the job id
     *
     * @return string
     */
    public function getRequestJobId()
    {
        return $this->requestJobId;
    }

    /**
     * Get the request entity token
     *
     * @return string
     */
    public function getRequestEntityToken()
    {
        return $this->requestEntityToken;
    }

    /**
     * Get the entity id
     *
     * @return string
     */
    public function getRequestDsyncEntityIdField()
    {
        return $this->requestDsyncEntityIdField;
    }

    /**
     * Get the dsync entity id field
     *
     * @return string
     */
    public function getRequestDsyncEntityId()
    {
        return $this->requestDsyncEntityId;
    }

    /**
     * Get the process id
     *
     * @return string
     */
    public function getRequestProcessId()
    {
        return $this->requestProcessId;
    }

    /**
     * Get the entity name
     *
     * @return string
     */
    public function getRequestEntityName()
    {
        return $this->requestEntityName;
    }

    /**
     * Get the request method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Get the request entity
     *
     * @return object
     */
    public function getRequestEntity()
    {
        return $this->requestEntity;
    }

    /**
     * Get the request data that was set or return
     * an empty array
     *
     * @return array
     */
    public function getRequestData()
    {
        if (!$this->requestData) {
            return array();
        }
        return $this->requestData;
    }

    /**
     * Get the request detail or set an empty array
     *
     * @return array
     */
    public function getRequestDetail()
    {
        if (!$this->requestDetail) {
            return array();
        }
        return $this->requestDetail;
    }

    /**
     * Get the request status
     *
     * @return string
     */
    public function getRequestStatus()
    {
        return $this->requestStatus;
    }

    /**
     * Get the request message
     *
     * @return string
     */
    public function getRequestMessage()
    {
        if (!$this->requestMessage) {
            if ($this->getRequestStatus()) {
                return $this->responseCodeModel
                    ->getDefaultStatusMessage($this->getRequestStatus());
            }
        }
        return $this->requestMessage;
    }

    /**
     * Send a request based on this class
     *
     * @return object
     */
    public function send()
    {
        try {
            $this->processEntityRequest();
            $response = $this
                ->getClient()
                ->setSystemType($this->getSystemType())
                ->setEntityToken($this->getRequestEntityToken())
                ->setProcessId($this->getRequestProcessId())
                ->setDsyncEntityIdField($this->getRequestDsyncEntityIdField())
                ->setDsyncEntityId($this->getRequestDsyncEntityId())
                ->setRequestEntity($this->getRequestEntity())
                ->send($this->getBody(), $this->getMethod(), $this->getUri());
            $this->processCompleteEntityRequest($response);
            return $response;
        } catch (\Dsync\Dsync\Exception $e) {
            // save in process table to send again if applicable or throw an error
            $this->processFailedEntityRequest($e->getMessage());
        }
    }

    /**
     * Process an entity request if applicable
     *
     * @throws \Exception
     * @throws \Dsync\Dsync\Exception
     */
    protected function processEntityRequest()
    {
        if ($this->getRequestEntity()) {
            if (!$this->getRequestMethod()) {
                throw new \Exception('A request method is required to process an entity.');
            }
            $this->generateRequestFromEntity();

            // if it is new, add the data to the process model
            // and lock the process because it is not being retried
            if (!$this->isRetry()) {
                $this
                    ->getProcessModel()
                    ->setRequestData(json_encode($this->getRequestData()))
                    ->setEntityId($this->getRequestEntity()->getEntityId())
                    ->setDsyncEntityId($this->getRequestEntity()->getDsyncEntityId())
                    ->setDsyncEntityIdField($this->getRequestEntity()->getDsyncEntityIdField())
                    ->setMethod($this->getRequestMethod())
                    ->setSystemType($this->getSystemType())
                    ->setEntityType($this->getRequestEntityName())
                    ->setRequestType(\Dsync\Dsync\Model\System\Config\Source\Request\Type::SOURCE)
                    ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PROCESSING)
                    ->setIsLocked(true)
                    ->save();
            } else {
                $this->setSystemType($this->getProcessModel()->getSystemType());
//                $this
//                    ->getProcessModel()
//                    ->setRequestData(json_encode($this->getRequestData()))
//                    ->save();
            }
        }
    }

    /**
     * Process a complete entity request if applicable
     *
     * @param string $response
     */
    protected function processCompleteEntityRequest($response)
    {
        if ($this->getRequestEntity()) {
            $response = json_decode($response);

            // unlock the process if it is not being retried
            if (!$this->isRetry()) {
                $this->getProcessModel()->setIsLocked(false);
            }

            $this
                ->getProcessModel()
                ->setProcessId($response->data->process_id)
                ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_NOTIFICATION)
                ->setMessage(null)
                ->setRetry(0)
                ->save();
        }
    }

    /**
     * Process a failed entity request if applicable
     *
     * @param string $errorMessage
     * @throws \Dsync\Dsync\Exception
     */
    protected function processFailedEntityRequest($errorMessage)
    {
        if ($this->getRequestEntity()) {
            if ($this->isRetry()) {
                throw new \Dsync\Dsync\Exception($errorMessage);
            } else {
                $this
                    ->getProcessModel()
                    ->setStatus(\Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY)
                    ->setRetry($this->getHelper()->getMaxRetries())
                    ->setMessage($errorMessage)
                    ->setIsLocked(false)
                    ->save();
            }
            // register the failed process to be able to generate the entity id
            // on the process table.
            // this should only work when creating entities as there will not be
            // an id on the entity object.
            if (!$this->getRequestEntity()->getEntityId()) {
                $this
                    ->getRegistry()
                    ->register(
                        'failed_process_' . $this->getRequestEntityName(),
                        $this->getProcessModel()
                    );
            }
        } else {
            throw new \Dsync\Dsync\Exception($errorMessage);
        }
    }

    /**
     * Generate a request from an entity object
     *
     * @throws \Dsync\Dsync\Exception
     */
    protected function generateRequestFromEntity()
    {
        $methodType = $this->requestMethodModel
            ->getMethodType($this->getRequestMethod());

        $entity = $this->getRequestEntity();

        $this
            ->setMethod($methodType)
            ->setRequestJobId($entity->getJobId())
            ->setRequestEntityToken($entity->getEntityToken())
            ->setRequestEntityName($entity->getEntityType());
        // always use the dsync entity id from the process table if it is retrying
        if ($this->isRetry()) {
            $this
                ->setRequestDsyncEntityId($this->getProcessModel()->getDsyncEntityId())
                ->setRequestDsyncEntityIdField($this->getProcessModel()->getDsyncEntityIdField());
        } else {
            $this
                ->setRequestDsyncEntityId($entity->getEntityId())
                ->setRequestDsyncEntityIdField($entity->getDsyncEntityIdField());
        }
        // do not read the data for deleting an object as it might not exist on
        // the system anymore
        if ($this->getRequestMethod() != \Dsync\Dsync\Model\Api\Request\Method::DELETE) {
            if ($this->isRetry()) {
                $this->setRequestData(json_decode($this->getProcessModel()->getRequestData(), true));
            } else {
                $this->setRequestData($entity->read());
            }
        }
    }

    /**
     * Get the body of the request from set request parameters
     *
     * @return array
     */
    public function getBody()
    {
        if ($this->getRequestEntity()) {
            return $this->getRequestData();
        }

        $body = array(
            'data' => $this->getRequestData()
        );

        if ($this->getRequestStatus()) {
            $body['status'] = $this->getRequestStatus();
        }

        if ($this->getRequestMessage()) {
            $body['message'] = $this->getRequestMessage();
        }

//        if ($this->getRequestJobId()) {
//            $body['job_id'] = $this->getRequestJobId();
//        }
//
//        if ($this->getRequestProcessId()) {
//            $body['process_id'] = $this->getRequestProcessId();
//        }
//
//        if ($this->getRequestEntityName()) {
//            $body['entity_name'] = $this->getRequestEntityName();
//        }
//
//        if ($this->getRequestMethod()) {
//            $body['method'] = $this->getRequestMethod();
//        }
        return $body;
    }

    /**
     * Return the client
     *
     * @return \Dsync\Dsync\Model\Api\Client
     */
    protected function getClient()
    {
        return $this->client;
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
