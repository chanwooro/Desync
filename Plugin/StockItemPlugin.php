<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Plugin;

/**
 * Stock item plugin class
 */
class StockItemPlugin
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
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $this->eventDispatcher->dispatch('dsync_cataloginventory_stock_item_resource_save_before', ['item' => $object]);
        $returnValue = $proceed($object);
        $this->eventDispatcher->dispatch('dsync_cataloginventory_stock_item_resource_save_after', ['item' => $object]);
        return $returnValue;
    }
}
