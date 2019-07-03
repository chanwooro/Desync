<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\Process\Edit;

/**
 * Process edit form class
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare the form
     *
     * @return \Magento\Backend\Block\Widget\Form
     *
     * @codingStandardsIgnoreStart
     */
    protected function _prepareForm()
    {
        /* @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    // @codingStandardsIgnoreEnd
}
