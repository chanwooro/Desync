<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Observer;

/**
 * Abstract observer class
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractObserver
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Request $request
     */
    protected $request;

    /**
     * @var mixed $entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var boolean
     */
    protected $checkEntity;

    /**
     * @var boolean
     */
    protected $isDelete;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Set an entity
     *
     * @param mixed $entity
     * @return \Dsync\Dsync\Observer\AbstractObserver
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get an entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set to check an entity
     *
     * @param boolean $checkEntity
     * @return \Dsync\Dsync\Observer\AbstractObserver
     */
    public function setCheckEntity($checkEntity)
    {
        $this->checkEntity = $checkEntity;
        return $this;
    }

    /**
     * Get check entity
     *
     * @return boolean
     */
    public function isCheckEntity()
    {
        return $this->checkEntity;
    }

    /**
     * Set if this is a delete request
     *
     * @param boolean $isDelete
     * @return \Dsync\Dsync\Observer\AbstractObserver
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;
        return $this;
    }

    /**
     * Get if this is a delete request
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->isDelete;
    }

    /**
     * Set the method
     *
     * @param string $method
     * @return \Dsync\Dsync\Observer\AbstractObserver
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
     * Send the request
     */
    public function send()
    {
        $this
            ->request
            ->resetRequest()
            ->setRequestEntity($this->getEntity())
            ->setRequestMethod($this->getMethod())
            ->send();
    }

    /**
     * Process an entity if if it processable
     *
     * @return
     */
    protected function processEntity()
    {
        if (!$this->getEntity()->isProcessable()) {
            $entityType = $this->getEntity()->getEntityType();
            if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Registry::NEW_METHOD)) {
                $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Registry::NEW_METHOD);
            }
            return;
        }

        //set the default method
        $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::UPDATE);

        if ($this->isCheckEntity()) {
            $this->processCheckEntity();
        } else {
            $this->processUpdateEntity();
        }
    }

    /**
     * Process a check enity (before save)
     *
     * If the entity is new, the process entity will send a create
     * request and prevent further processing from sending an update
     * request right after
     *
     * @return
     */
    protected function processCheckEntity()
    {
        $entityType = $this->getEntity()->getEntityType();

        // if there is a block request on this entity, ignore sending the request
        if ($this->getRegistry()->get(
            $entityType,
            \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD
        )
        ) {
            return;
        }
        if ($this->getEntity()->getEntity()->isObjectNew()) {
            // if the destination data is creating the new entity ignore sending the create request
            if ($this->getRegistry()->get(
                $entityType,
                \Dsync\Dsync\Model\Api\Request\Method::CREATE
            )
            ) {
                return;
            }

            // set there is a new entity
            $this->getRegistry()->set($entityType, \Dsync\Dsync\Model\Registry::NEW_METHOD);
        }
    }

    /**
     * Process an update entity (after save)
     *
     * Will process an entity after the save event (it has been updated) and
     * send the request as a create, update or delete depending on the
     * registry parameters
     *
     * @return
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processUpdateEntity()
    {
        $entityType = $this->getEntity()->getEntityType();

        // process a failed process
        $this->processFailedProcess();

        // if this entity is currently blocked for a request ignore it
        if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD)) {
            $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD);
            return;
        }

        // if a new entity has just been created and sent ignore it
        if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Registry::NEW_METHOD)) {
            $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
            $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Registry::NEW_METHOD);
        }

        // if the destination data is creating the new entity ignore sending the update request
        if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Api\Request\Method::CREATE)) {
            $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Api\Request\Method::CREATE);
            return;
        }
        // if destination data is updating the entity ignore this in the request
        if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Api\Request\Method::UPDATE)) {
            $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Api\Request\Method::UPDATE);
            return;
        }
        // if destination data is deleting the entity ignore this in the request
        if ($this->getRegistry()->get($entityType, \Dsync\Dsync\Model\Api\Request\Method::DELETE)) {
            $this->getRegistry()->del($entityType, \Dsync\Dsync\Model\Api\Request\Method::DELETE);
            return;
        }

        if ($this->isDelete()) {
            $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::DELETE);
        }
        $this->send();
    }

    /**
     * Process a failed process
     *
     * Sets the entity identifier on the process table to be used when
     * trying to load the entity on a retry
     */
    protected function processFailedProcess()
    {
        $entityType = $this->getEntity()->getEntityType();

        // if the last process failed, save the new entity id on the process
        // table
        if ($this->getRegistry()->registry('failed_process_' . $entityType)) {
            $process = $this->getRegistry()->registry('failed_process_' . $entityType);
            $process->setEntityId($this->getEntity()->getEntity()->getId())->save();
            $this->getRegistry()->unregister('failed_process_' . $entityType);
        }
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
