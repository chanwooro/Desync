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
 * Delete product observer class
 */
class DeleteProductObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Product $product
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Product $product
    ) {
        $this->entity = $product;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $this->getEntity()->setEntity($product);
        $this->setIsDelete(true);
        $this->processEntity();
    }
}
