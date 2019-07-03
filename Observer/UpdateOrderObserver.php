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
 * Update order observer class
 */
class UpdateOrderObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Order $order
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Order $order
    ) {
        $this->entity = $order;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        // if this order object has just been created, ignore subsequent updates for this request
        if ($this->getRegistry()->get('order', \Dsync\Dsync\Model\Registry::BLOCK_UPDATE . $order->getId())) {
            return;
        }
        // if this order object has just been created, set it to block subsequent update requests
        if ($this->getRegistry()->get('order', \Dsync\Dsync\Model\Registry::NEW_METHOD)) {
            $this->getRegistry()->set('order', \Dsync\Dsync\Model\Registry::BLOCK_UPDATE . $order->getId());
        }
        $this->getEntity()->setEntity($order);
        $this->processEntity();
    }
}
