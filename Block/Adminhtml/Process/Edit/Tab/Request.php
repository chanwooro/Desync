<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\Process\Edit\Tab;

use Dsync\Dsync\Block\Adminhtml\Process\Edit\Tab\TabAbstract;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Tab Request Class
 */
class Request extends TabAbstract implements TabInterface
{

    /**
     * @var string
     *
     * @codingStandardsIgnoreStart
     */
    protected $_template = 'request.phtml';
    // @codingStandardsIgnoreEnd

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
        parent::__construct($coreRegistry, $context, $data);
        $this->setTemplate($this->_template);
    }

    /**
     * Get the request data from the process formatted with JSON PRETTY PRINT
     *
     * @return string
     */
    public function getPrettyRequest()
    {
        $json = json_decode($this->getProcess()->getRequestData());
        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * Get the tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Request');
    }

    /**
     * Get the tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Request');
    }
}
