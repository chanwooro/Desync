<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Plugin;

/**
 * Shipment comment plugin class
 */
class ShipmentCommentPlugin
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
     * @param \Magento\Sales\Model\Order\Shipment\Comment $subject
     * @param \Closure $proceed
     * @return \Magento\Sales\Model\Order\Shipment\Comment
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBeforeSave(
        \Magento\Sales\Model\Order\Shipment\Comment $subject,
        \Closure $proceed
    ) {
        $returnValue = $proceed();
        $this->eventDispatcher->dispatch('sales_order_shipment_comment_save_before', ['comment' => $returnValue]);
        return $returnValue;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Comment $subject
     * @param \Closure $proceed
     * @return \Magento\Sales\Model\Order\Shipment\Comment
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAfterSave(
        \Magento\Sales\Model\Order\Shipment\Comment $subject,
        \Closure $proceed
    ) {
        $returnValue = $proceed();
        $this->eventDispatcher->dispatch('sales_order_shipment_comment_save_after', ['comment' => $returnValue]);
        return $returnValue;
    }
}
