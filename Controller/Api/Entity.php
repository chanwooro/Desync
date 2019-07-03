<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Controller\Api;

/**
 * Entity api controller class
 */
class Entity extends \Dsync\Dsync\Controller\Api\AbstractApi
{
    /**
     * @var \Dsync\Dsync\Model\Api\Route\Entity $route
     */
    protected $route;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Dsync\Dsync\Model\Api\Server $server
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Route\Entity $entity
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Dsync\Dsync\Model\Api\Server $server,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Route\Entity $entity
    ) {
        $this->route = $entity;
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
