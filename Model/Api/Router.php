<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api;

/**
 * Api router class
 */
class Router
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * @var \Dsync\Dsync\Model\Api\Response $response
     */
    protected $response;

    /**
     * @var \Dsync\Dsync\Model\Api\Route\RouteAbstract $route
     */
    protected $route;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Response $response
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Response $response
    ) {
        $this->helper = $helper;
        $this->response = $response;
    }

    /**
     * Route an api request and return a response
     *
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function route()
    {
        if (!$this->isAuthorized()) {
            return $this->getResponse()
                ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_UNAUTHORIZED);
        }

        try {
            return $this
                ->getRoute()
                ->setResponse($this->getResponse())
                ->dispatch($this->getRequest());
        } catch (\Dsync\Dsync\Exception $e) {
            $code = $e->getCode() ? $e->getCode() : \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST;
            $message = $e->getMessage() ? $e->getMessage() : null;
            $this->getHelper()->log($message);
            $this->getResponse()
                ->setResponseCode($code)
                ->setResponseMessage($message);
        } catch (\Exception $e) {
            $this->getHelper()->log($e->getMessage());
            $this->getResponse()
                ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_INTERNAL_ERROR)
                ->setResponseMessage($e->getMessage());
        }
        return $this->getResponse();
    }

    /**
     * Check to see if the auth token is valid
     *
     * @return boolean
     */
    protected function isAuthorized()
    {
        $token = $this->getRequest()->getHeader('Auth-Token');
        if ($token != $this->getHelper()->getAuthToken() ||
                $this->getHelper()->getAuthToken() == null
        ) {
            return false;
        }
        return true;
    }

    /**
     * Set the http request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Router
     */
    public function setRequest(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the route
     *
     * @param \Dsync\Dsync\Model\Api\Route\RouteAbstract $route
     * @return \Dsync\Dsync\Model\Api\Router
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get the route
     *
     * @return \Dsync\Dsync\Model\Api\Route\RouteAbstract
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get the request
     *
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response
     *
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function getResponse()
    {
        return $this->response;
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
