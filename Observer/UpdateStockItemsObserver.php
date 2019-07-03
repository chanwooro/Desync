<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Observer;

use Magento\Framework\Event\ObserverInterface;
use Dsync\Dsync\Observer\AbstractObserver;

/**
 * Update stock item observer class
 */
class UpdateStockItemsObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->itemFactory = $itemFactory;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $itemModel = $this->itemFactory->create();
        foreach ($observer->getItems() as $productId => $item) {
            if (empty($item['item'])) {
                $stockItem = $itemModel->load($productId, 'product_id');
            } else {
                $stockItem = $item['item'];
            }
            if ($stockItem->getId()) {
                $stockItem = $itemModel
                    ->load($stockItem->getId());
                $this->eventDispatcher->dispatch('dsync_cataloginventory_stock_item_updater', ['item' => $stockItem]);
            }
        }
    }
}
