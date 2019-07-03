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
 * Update gift message observer class
 */
class UpdateGiftMessageObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $message = $observer->getMessage();

        $orderCollection = $this
            ->orderCollectionFactory
            ->create()
            ->addFieldToFilter('gift_message_id', $message->getId());
        $order = $orderCollection->getFirstItem();

        if ($order->getId()) {
            $this->eventDispatcher->dispatch('dsync_sales_order_updater', ['order' => $order]);
        }
    }
}
