<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Observer\Shipment;

use Magento\Framework\Event\ObserverInterface;
use Dsync\Dsync\Observer\AbstractObserver;

/**
 * Shipment check comment observer class
 */
class CheckCommentObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Api\Request $request
     * @param \Dsync\Dsync\Model\Entity\Shipment\Comment $comment
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Api\Request $request,
        \Dsync\Dsync\Model\Entity\Shipment\Comment $comment
    ) {
        $this->entity = $comment;
        parent::__construct($helper, $request);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $comment = $observer->getComment();
        $this->getEntity()->setEntity($comment);
        $this->setCheckEntity(true);
        $this->processEntity();
    }
}
