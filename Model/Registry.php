<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model;

/**
 * Registry Class
 */
class Registry
{
    const DSYNC_REGISTRY = 'dsync';

    const NEW_METHOD = 'new';

    const BLOCK_REQUEST_METHOD = 'block_request';

    const BLOCK_UPDATE = 'block_update_';

    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Entity\TypeFactory $entityTypeFactory
     */
    protected $entityTypeFactory;

    /**
     * @var \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     */
    protected $requestMethodModel;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\TypeFactory $entityTypeFactory
     * @param \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Dsync\Dsync\Model\System\Config\Source\Entity\TypeFactory $entityTypeFactory,
        \Dsync\Dsync\Model\Api\Request\Method $requestMethodModel
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->entityTypeFactory = $entityTypeFactory;
        $this->requestMethodModel = $requestMethodModel;
    }

    /**
     * Set an item in the registry
     *
     * @param string $entity
     * @param string $method
     * @param mixed $value
     * @return \Dsync\Dsync\Model\Registry
     */
    public function set($entity, $method = self::NEW_METHOD, $value = true)
    {
        if ($this->get($entity, $method)) {
            $this->del($entity, $method);
        }
        $this->register($this->getEntityRegistryName($entity, $method), $value);
        return $this;
    }

    /**
     * Get an item from the registry
     *
     * @param string $entity
     * @param string $method
     * @return mixed|null
     */
    public function get($entity, $method = self::NEW_METHOD)
    {
        if ($this->registry($this->getEntityRegistryName($entity, $method))) {
            return $this->registry($this->getEntityRegistryName($entity, $method));
        }
        return null;
    }

    /**
     * Delete an item from the registry
     *
     * @param string $entity
     * @param string $method
     */
    public function del($entity, $method = self::NEW_METHOD)
    {
        if ($this->registry($this->getEntityRegistryName($entity, $method))) {
            $this->unregister($this->getEntityRegistryName($entity, $method));
        }
    }

    /**
     * Flush all entities and methods from the registry
     */
    public function flushAll()
    {
        $entityTypeModel = $this->entityTypeFactory->create();
        $entities = $entityTypeModel->getEntityTypes();

        foreach (array_keys($entities) as $entity) {
            foreach ($this->getMethods() as $method) {
                if ($this->get($entity, $method)) {
                    $this->del($entity, $method);
                }
            }
        }
    }

    /**
     * Flush all methods for a single entity
     *
     * @param string $entity
     * @return
     */
    public function flush($entity)
    {
        if (!$entity) {
            return;
        }
        foreach ($this->getMethods() as $method) {
            if ($this->get($entity, $method)) {
                $this->del($entity, $method);
            }
        }
    }

    /**
     * Get all methods available to be used with the registry
     *
     * @return array
     */
    protected function getMethods()
    {
        $methodArray = array();
        $methods = $this->requestMethodModel->getMethods();
        foreach (array_keys($methods) as $method) {
            $methodArray[] = $method;
        }
        return array_merge($methodArray, $this->getAdditionalMethods());
    }

    /**
     * Get additional methods that are not part of api request method
     *
     * @return array
     */
    protected function getAdditionalMethods()
    {
        return array(self::NEW_METHOD, self::BLOCK_REQUEST_METHOD);
    }

    /**
     * Get the registry name from the entity and method
     *
     * @param string $entity
     * @param string $method
     * @return string
     */
    protected function getEntityRegistryName($entity, $method)
    {
        return $entity . '_' . $method;
    }

    /**
     * Get the Dsync registry name
     *
     * @return string
     */
    protected function getDsyncRegistry()
    {
        return self::DSYNC_REGISTRY . '_';
    }

    /**
     * Get a registry value from the core registry by the Dsync
     * registry name and a key
     *
     * @param string $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->coreRegistry->registry($this->getDsyncRegistry() . $key);
    }

    /**
     * Register a value in the core registry by the Dsync registry
     * name and a key
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->coreRegistry->register($this->getDsyncRegistry() . $key, $value, $graceful);
    }

    /**
     * Unregister a value in the core registry by the Dsync registry
     * name and a key
     *
     * @param string $key
     */
    public function unregister($key)
    {
        $this->coreRegistry->unregister($this->getDsyncRegistry() . $key);
    }
}
