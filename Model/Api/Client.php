<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api;

/**
 * Dsync api client class
 */
class Client
{
    /**
     * @var \Zend\Http\Client $helper
     */
    protected $client;

     /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var int
     */
    protected $systemType;

    /**
     * @var string
     */
    protected $entityToken;

    /**
     * @var string
     */
    protected $processId;

    /**
     * @var string
     */
    protected $dsyncEntityIdField;

    /**
     * @var string
     */
    protected $dsyncEntityId;

    /**
     * @var object
     */
    protected $requestEntity;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Send a request
     *
     * @param array $request
     * @param string $requestMethod
     * @param string $uri
     * @return object
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     */
    public function send($request, $requestMethod, $uri)
    {
        $this
            ->initializeBaseUri()
            ->getClient()
            ->setHeaders($this->getHeaders())
            ->setUri($this->baseUri . $uri);
        if ($requestMethod != \Zend\Http\Request::METHOD_GET) {
            $this->getClient()->setRawBody(\Zend\Json\Json::encode($request));
        }
        try {
            $response = $this->getClient()->setMethod($requestMethod)->send();
            $body = $response->getBody();

            $this->getHelper()->log($this->getClient()->getLastRawRequest(), null, 'dsync-client-request.log');
            $this->getHelper()->log($this->getClient()->getLastRawResponse(), null, 'dsync-client-response.log');

            if ($response->isClientError() || $response->isServerError()) {
                $responseBody = json_decode($body);
                $responseMessage = isset($responseBody->message) ? ' - ' . $responseBody->message : null;
                throw new \Exception(
                    __('Request error: ') .
                    $response->getStatusCode() . ' - ' .
                    $response->getReasonPhrase() .
                    $responseMessage
                );
            }
        } catch (\Exception $e) {
            $this->getHelper()->log($e->getMessage());
            throw new \Dsync\Dsync\Exception($e->getMessage());
        }
        return $body;
    }

    /**
     * Return the set headers for the request
     *
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array(
            'Auth-Token' => $this->getHelper()->getAuthToken(),
            'Content-type' => 'application/json'
        );
        if ($entityToken = $this->getEntityToken()) {
            $headers['Entity-Token'] = $entityToken;
        }
        if ($processId = $this->getProcessId()) {
            $headers['Process-Id'] = $processId;
        }
        if ($dsyncEntityId = $this->getDsyncEntityId()) {
            $headers['Entity-Id'] = $dsyncEntityId;
        }
        if ($dsyncEntityIdField = $this->getDsyncEntityIdField()) {
            $headers['Entity-Id-Field'] = $dsyncEntityIdField;
        }
        return $headers;
    }

    /**
     * Set the base URI for the request
     *
     * @return \Client
     */
    protected function initializeBaseUri()
    {
        switch ($this->getSystemType()) {
            case \Dsync\Dsync\Model\System\Config\Source\System\Type::STAGE:
                $this->baseUri = $this->getHelper()->getStoreConfig('module_config/endpoint_stage');
                break;
            case \Dsync\Dsync\Model\System\Config\Source\System\Type::PRODUCTION:
                $this->baseUri = $this->getHelper()->getStoreConfig('module_config/endpoint');
                break;
            case \Dsync\Dsync\Model\System\Config\Source\System\Type::CUSTOM:
                $this->baseUri = $this->getHelper()->getStoreConfig('module_config/endpoint_custom');
                break;
        }
        return $this;
    }

    /**
     * Set a http client
     *
     * @param mixed $client
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Set the system type
     *
     * @param int $systemType
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;
        return $this;
    }

    /**
     * Set the entity token
     *
     * @param string $entityToken
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setEntityToken($entityToken)
    {
        $this->entityToken = $entityToken;
        return $this;
    }

    /**
     * Set the Process ID
     *
     * @param string $processId
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setProcessId($processId)
    {
        $this->processId = $processId;
        return $this;
    }

    /**
     * Set the request entity
     *
     * @param object $requestEntity
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setRequestEntity($requestEntity)
    {
        $this->requestEntity = $requestEntity;
        return $this;
    }

    /**
     * Set the entity field id
     *
     * @param string $dsyncEntityIdField
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setDsyncEntityIdField($dsyncEntityIdField)
    {
        $this->dsyncEntityIdField = $dsyncEntityIdField;
        return $this;
    }

    /**
     * Set the entity id
     *
     * @param string $dsyncEntityId
     * @return \Dsync\Dsync\Model\Api\Client
     */
    public function setDsyncEntityId($dsyncEntityId)
    {
        $this->dsyncEntityId = $dsyncEntityId;
        return $this;
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
     * Get the entity field id
     *
     * @return string
     */
    public function getDsyncEntityIdField()
    {
        return $this->dsyncEntityIdField;
    }

    /**
     * Get the entity id
     *
     * @return string
     */
    public function getDsyncEntityId()
    {
        return $this->dsyncEntityId;
    }

    /**
     * Get the http client
     *
     * @return \Zend\Http\Client
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new \Zend\Http\Client();
        }
        return $this->client;
    }

    /**
     * Get the system type
     *
     * @return int
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * Get the entity token
     *
     * @return string
     */
    public function getEntityToken()
    {
        return $this->entityToken;
    }

    /**
     * Get the Process ID
     *
     * @return string
     */
    public function getProcessId()
    {
        return $this->processId;
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
}
