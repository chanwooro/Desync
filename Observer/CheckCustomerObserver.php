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
 * Check customer observer class
 */
class CheckCustomerObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Customer $customer
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Customer $customer
    ) {
        $this->entity = $customer;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $this->getEntity()->setEntity($customer);
        $this->setCheckEntity(true);
        $this->processEntity();
    }
}
