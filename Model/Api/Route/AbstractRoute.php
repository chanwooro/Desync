<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api\Route;

/**
 * Abstract route class
 */
abstract class AbstractRoute
{

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Response $response
     */
    protected $response;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set a response
     *
     * @param \Dsync\Dsync\Model\Api\Response $response
     * @return \Dsync\Dsync\Model\Api\Route\AbstractRoute
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get a response
     *
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Dispatch the request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Response
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dispatch($request)
    {
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        return $this->getResponse();
    }

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
