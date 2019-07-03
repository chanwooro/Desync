<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api;

/**
 * Api server class
 */
class Server
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
     * @var \Magento\Framework\App\Response\Http $response
     */
    protected $response;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response\Renderer\Json $renderer
     */
    protected $renderer;

    /**
     * @var \Dsync\Dsync\Model\Api\Router $router
     */
    protected $router;

    /**
     * @var \Dsync\Dsync\Model\Api\Route\RouteAbstract $route
     */
    protected $route;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Framework\Webapi\Rest\Response\Renderer\Json $renderer
     * @param \Dsync\Dsync\Model\Api\Router $router
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Framework\Webapi\Rest\Response\Renderer\Json $renderer,
        \Dsync\Dsync\Model\Api\Router $router
    ) {
        $this->helper = $helper;
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * Run the request to the correct route and send the returned response
     */
    public function run()
    {
        $response = $this
            ->getRouter()
            ->setRoute($this->getRoute())
            ->setRequest($this->getRequest())
            ->route();
        $this->getHelper()->log(
            $this->getRequest()->getRequestUri() . PHP_EOL . $this->getRequest()->getContent(),
            null,
            'dsync-server-request.log'
        );
        $this->getHelper()->log($this->getRenderer()->render($response->getBody()), null, 'dsync-server-response.log');
        $this->sendResponse($response);
    }

    /**
     * Send the rendered response
     *
     * @param \Dsync\Dsync\Model\Api\Response $response
     */
    protected function sendResponse(\Dsync\Dsync\Model\Api\Response $response)
    {
        foreach ($response->getHeaders() as $key => $value) {
            $this->getResponse()->setHeader($key, $value, true);
        }
        $this
            ->getResponse()
            ->setHeader('Content-type', $this->getRenderer()->getMimeType(), true)
            ->setHttpResponseCode($response->getResponseCode())
            ->setBody(
                $this->getRenderer()->render($response->getBody())
            );
    }

    /**
     * Set the http response
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return \Dsync\Dsync\Model\Api\Server
     */
    public function setResponse(\Magento\Framework\App\Response\Http $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the http request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Server
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
     * @return \Dsync\Dsync\Model\Api\Server
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
     * Get the router
     *
     * @return \Dsync\Dsync\Model\Api\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Get the http response
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the http request
     *
     * @return \Magento\Framework\App\Request\Http
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the json renderer
     *
     * @return \Magento\Framework\Webapi\Rest\Response\Renderer\Json
     */
    public function getRenderer()
    {
        return $this->renderer;
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
