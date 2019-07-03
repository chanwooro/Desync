<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\Process\Edit;

/**
 * Process edit tabs class
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Construct
     *
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('dsync_adminhtml_process_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Dsync Request'));
    }
    // @codingStandardsIgnoreEnd

    /**
     * Add the tabs
     *
     * @return \Dsync\Dsync\Block\Adminhtml\Process\Edit\Tabs
     *
     * @codingStandardsIgnoreStart
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'process_status',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'content' => $this->getLayout()->createBlock(
                    'Dsync\Dsync\Block\Adminhtml\Process\Edit\Tab\Status'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'process_request',
            [
                'label' => __('Request'),
                'title' => __('Request'),
                'content' => $this->getLayout()->createBlock(
                    'Dsync\Dsync\Block\Adminhtml\Process\Edit\Tab\Request'
                )->toHtml(),
                'active' => false
            ]
        );
        return parent::_beforeToHtml();
    }
    // @codingStandardsIgnoreEnd
}
