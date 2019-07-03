<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\Process\Edit\Tab;

/**
 * Abstract tab class
 */
abstract class TabAbstract extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * Construct
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Retreive the current process from the registry
     *
     * @return \Dsync\Dsync\Model\Process
     */
    public function getProcess()
    {
        return $this->coreRegistry->registry('dsync_process');
    }

    /**
     * Check if you can show the tab
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if the tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get the formatted section title for the tab
     *
     * @return string
     */
    public function getSectionTitle()
    {
        $process = $this->getProcess();
        $id = $process->getId();
        $date = $this->formatProcessDate($process->getCreatedAt());
        return __(
            '%1 %2 Request (%3) | %4',
            ucwords($process->getEntityType()),
            ucwords($process->getMethod()),
            $id,
            $date
        );
    }

    /**
     * Format a date to be output
     *
     * @param datetime $date
     * @return string
     */
    public function formatProcessDate($date)
    {
        return $this->formatDate(
            $date,
            \IntlDateFormatter::MEDIUM,
            true
        );
    }
}
