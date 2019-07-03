<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Plugin;

/**
 * Gift message plugin class
 */
class GiftMessagePlugin
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
     * @param \Magento\GiftMessage\Model\Message $subject
     * @param \Closure $proceed
     * @return \Magento\GiftMessage\Model\Message
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAfterSave(
        \Magento\GiftMessage\Model\Message $subject,
        \Closure $proceed
    ) {
        $returnValue = $proceed();
        $this->eventDispatcher->dispatch('giftmessage_message_save_after', ['message' => $returnValue]);
        return $returnValue;
    }
}
