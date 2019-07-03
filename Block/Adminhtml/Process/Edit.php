<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\Process;

/**
 * Process edit class
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Construct
     *
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_process';
        $this->_blockGroup = 'Dsync_Dsync';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
    }
    // @codingStandardsIgnoreEnd
}
