<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Plugin;

/**
 * Store management plugin class
 */
class StockManagementPlugin
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
     * @param \Magento\CatalogInventory\Model\StockManagement $stockManagement
     * @param \Closure $proceed
     * @param array $items
     * @param int $websiteId
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterProductsSale(
        \Magento\CatalogInventory\Model\StockManagement $stockManagement,
        \Closure $proceed,
        $items,
        $websiteId = null
    ) {
        $returnValue = $proceed($items, $websiteId);
        $this->dispatchItems($items);
        return $returnValue;
    }

    /**
     * @param \Magento\CatalogInventory\Model\StockManagement $stockManagement
     * @param \Closure $proceed
     * @param array $items
     * @param int $websiteId
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRevertProductsSale(
        \Magento\CatalogInventory\Model\StockManagement $stockManagement,
        \Closure $proceed,
        $items,
        $websiteId = null
    ) {
        $returnValue = $proceed($items, $websiteId);
        $this->dispatchItems($items);
        return $returnValue;
    }

    /**
     * @param array $items
     */
    protected function dispatchItems($items)
    {
        $this->eventDispatcher->dispatch('dsync_stock_items_update', ['items' => $items]);
    }
}
