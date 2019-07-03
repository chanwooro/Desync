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
 * Update inventory observer class
 */
class UpdateInventoryObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Inventory $inventory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Inventory $inventory
    ) {
        $this->entity = $inventory;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $inventory = $observer->getItem();
        $this->getEntity()->setEntity($inventory);
        $this->processEntity();
    }
}
