<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Plugin;

/**
 * Backend media plugin class
 */
class BackendMediaPlugin
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $valueId;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia
     * @param \Closure $proceed
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundInsertGallery(
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia,
        \Closure $proceed,
        array $data
    ) {
        $returnValue = $proceed($data);

        $this->valueId = $returnValue;

        return $returnValue;
    }

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia
     * @param \Closure $proceed
     * @param int $valueId
     * @param int $entityId
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBindValueToEntity(
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia,
        \Closure $proceed,
        $valueId,
        $entityId
    ) {
        $returnValue = $proceed($valueId, $entityId);

        if ($this->valueId) {
            if ($this->valueId == $valueId) {
                $newObject = new \Magento\Framework\DataObject();
                $newData = [
                    'id' => $valueId,
                    'entity_id' => $entityId
                ];
                $newObject->setData($newData);
                $this->valueId = null;
                $this->dispatchObject('create', $newObject);
            }
        }

        return $returnValue;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia
     * @param \Closure $proceed
     * @param mixed $valueId
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDeleteGallery(
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $backendMedia,
        \Closure $proceed,
        $valueId
    ) {
        $returnValue = $proceed($valueId);

        if (is_array($valueId) && count($valueId) > 0) {
            foreach ($valueId as $id) {
                $newObject = new \Magento\Framework\DataObject();
                $newObject->setId($id);
                $this->dispatchObject('delete', $newObject);
            }
        } elseif (!is_array($valueId)) {
            $newObject = new \Magento\Framework\DataObject();
            $newObject->setId($valueId);
            $this->dispatchObject('delete', $newObject);
        }

        return $returnValue;
    }

    /**
     * @param string $type
     * @param object $object
     */
    protected function dispatchObject($type, $object)
    {
        $this->eventDispatcher->dispatch('dsync_backend_media_' . $type, ['image' => $object]);
    }
}
