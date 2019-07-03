<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Controller\Api;

/**
 * Notification api controller class
 */
class Notification extends \Dsync\Dsync\Controller\Api\AbstractApi
{
    /**
     * @var \Dsync\Dsync\Model\Api\Route\Notification $route
     */
    protected $route;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Dsync\Dsync\Model\Api\Server $server
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Route\Notification $notification
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Dsync\Dsync\Model\Api\Server $server,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Route\Notification $notification
    ) {
        $this->route = $notification;
        parent::__construct($context, $server, $helper);
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function execute()
    {
        $this->runServer();
    }
}
