<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Cron;

/**
 * Send notifications cron class
 */
class SendNotifications
{
    /**
     * @var \Dsync\Dsync\Model\Cron\Processor $processor
     */
    protected $processor;

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Dsync\Dsync\Model\Cron\Processor $processor
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Model\Cron\Processor $processor,
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->processor = $processor;
        $this->helper = $helper;
    }

    /**
     * Retry source and destination requests cron job
     */
    public function execute()
    {
        if ($this->helper->isModuleActive()) {
            $this->processor->sendNotifications();
        }
    }
}
