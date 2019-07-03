<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Controller\Api;

/**
 * Abstract api controller class
 */
abstract class AbstractApi extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Server $server
     */
    protected $server;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Dsync\Dsync\Model\Api\Server $server
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Dsync\Dsync\Model\Api\Server $server,
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->server = $server;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function runServer()
    {
        if ($this->getHelper()->isModuleActive()) {
            $this->server
                ->setRoute($this->route)
                ->setRequest($this->getRequest())
                ->setResponse($this->getResponse())
                ->run();
        }
    }

    /**
     * Get the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
