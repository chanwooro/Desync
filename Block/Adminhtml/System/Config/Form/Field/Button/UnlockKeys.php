<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Block\Adminhtml\System\Config\Form\Field\Button;

/**
 * Button unlock keys class
 */
class UnlockKeys extends \Magento\Config\Block\System\Config\Form\Field
{
     const BUTTON_TEMPLATE = 'system/config/form/field/button/unlock_keys.phtml';

    /**
     * Prepare the layout
     *
     * @return \Dsync\Dsync\Block\Adminhtml\System\Config\Form\Field\Button\Job\Mapping
     *
     * @codingStandardsIgnoreStart
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Render the button
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the html element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'dsync_button_unlock_keys',
                'button_label'  => _('Unlock Primary Keys'),
                'onclick' => '',
            ]
        );
        return $this->_toHtml();
    }
    // @codingStandardsIgnoreEnd
}
