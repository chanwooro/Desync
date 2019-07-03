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
 * Update customer observer class
 */
class UpdateCustomerObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Customer $customer
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Customer $customer,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->entity = $customer;
        $this->customerFactory = $customerFactory;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();

        // reload the customer to get the full data set from the entity
        $customerEntity = $this
            ->customerFactory
            ->create()
            ->load($customer->getId());

        $this->getEntity()->setEntity($customerEntity);
        $this->processEntity();
    }
}
