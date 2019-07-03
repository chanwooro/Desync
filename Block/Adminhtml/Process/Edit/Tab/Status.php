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
 * Tab Status Class
 */
class Status extends TabAbstract implements TabInterface
{

    /**
     * @var string
     *
     * @codingStandardsIgnoreStart
     */
    protected $_template = 'status.phtml';
    // @codingStandardsIgnoreEnd

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Process\Status $processStatusModel
     */
    protected $processStatusModel;

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Request\Type $requestTypeModel
     */
    protected $requestTypeModel;

    /**
     * Construct
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Dsync\Dsync\Model\System\Config\Source\Process\Status $processStatusModel
     * @param \Dsync\Dsync\Model\System\Config\Source\Request\Type $requestTypeModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        \Dsync\Dsync\Model\System\Config\Source\Process\Status $processStatusModel,
        \Dsync\Dsync\Model\System\Config\Source\Request\Type $requestTypeModel,
        array $data = array()
    ) {
        $this->processStatusModel = $processStatusModel;
        $this->requestTypeModel = $requestTypeModel;
        parent::__construct($coreRegistry, $context, $data);
        $this->setTemplate($this->_template);
    }

    /**
     * Get the tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Status');
    }

    /**
     * Get the tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Status');
    }

    /**
     * Get the request type label from the current process
     *
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestTypeModel
            ->getRequestTypeLabel($this->getProcess()->getRequestType());
    }

    /**
     * Get the status label from the current process
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->processStatusModel
            ->getStatusLabel($this->getProcess()->getStatus());
    }
}
