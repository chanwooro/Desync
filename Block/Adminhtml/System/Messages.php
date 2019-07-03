<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Block\Adminhtml\System;

/**
 * System messages class
 */
class Messages extends \Magento\Backend\Block\Template
{
    /**
     * @var int
     */
    protected $errors;

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     *  Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Dsync\Dsync\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     *
     * @codingStandardsIgnoreStart
     */
    protected function _toHtml()
    {
        if ($this->getErrors()) {
            return parent::_toHtml();
        }
        return '';
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get the current amount of errors stored in the system
     *
     * @return int|null
     */
    public function getErrors()
    {
        if (!$this->errors) {
            $this->errors = $this->getHelper()->getCache('errors');
        }
        return $this->errors;
    }

    /**
     * Get the formatted process grid url
     *
     * @return string
     */
    public function getProcessUrl()
    {
        return $this->getUrl('dsync/process');
    }

    /**
     * Get the formatted dismiss url
     *
     * @return string
     */
    public function getDismissUrl()
    {
        return $this->getUrl('dsync/process/dismiss');
    }

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
