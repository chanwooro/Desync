<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Dsync\Dsync\Observer\AbstractObserver;

/**
 * Check product image observer class
 */
class CheckImageObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Product\Image $image
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Product\Image $image
    ) {
        $this->entity = $image;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $image = $observer->getImage();
        $this->getEntity()->setEntity($image);
        $this->setCheckEntity(false);
        
        $this->getRegistry()->set(
            $this->getEntity()->getEntityType(),
            \Dsync\Dsync\Model\Registry::NEW_METHOD
        );
        
        $this->processEntity();
    }
}
