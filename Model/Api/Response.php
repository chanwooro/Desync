<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api;

/**
 * Api response class
 */
class Response
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel
     */
    protected $responseCodeModel;

    /**
     * @var int
     */
    protected $responseCode;

    /**
     * @var string
     */
    protected $responseMessage;

    /**
     * @var array
     */
    protected $responseDetail;

    /**
     * @var array
     */
    protected $responseData;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Response\Code $responseCodeModel
    ) {
        $this->helper = $helper;
        $this->responseCodeModel = $responseCodeModel;
    }

    /**
     * Set the response code
     *
     * @param int $responseCode
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * Set the response message
     *
     * @param string $responseMessage
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
        return $this;
    }

    /**
     * Set the response detail
     *
     * @param array $responseDetail
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function setResponseDetail($responseDetail)
    {
        $this->responseDetail = $responseDetail;
        return $this;
    }

    /**
     * Set the response data
     *
     * @param array $responseData
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function setResponseData($responseData)
    {
        $this->responseData = $responseData;
        return $this;
    }

    /**
     * Add a header
     *
     * @param string $key
     * @param string $value
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Get the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
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
     * Get the response code
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Get the response message or get a default one from the
     * response code
     *
     * @return string
     */
    public function getResponseMessage()
    {
        if (!$this->responseMessage) {
            return $this->responseCodeModel
                ->getDefaultStatusMessage($this->getResponseCode());
        }
        return $this->responseMessage;
    }

    /**
     * Get the response detail or set an empty array
     *
     * @return array
     */
    public function getResponseDetail()
    {
        if (!$this->responseDetail) {
            return array();
        }
        return $this->responseDetail;
    }

    /**
     * Get the response data or set an empty array
     *
     * @return array
     */
    public function getResponseData()
    {
        if (!$this->responseData) {
            return array();
        }
        return $this->responseData;
    }

    /**
     * Get the response body from set parameters
     *
     * @return array
     */
    public function getBody()
    {
        return array(
            'status' => $this->getResponseCode(),
            'message' => $this->getResponseMessage(),
            'detail' => $this->getResponseDetail(),
            'data' => $this->getResponseData()
        );
    }
}
