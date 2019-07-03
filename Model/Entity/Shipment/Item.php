<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Shipment;

/**
 * Entity shipment item class
 */
class Item extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Shipment\Item $validatorModel
     * @param \Magento\Sales\Model\Order\Shipment\ItemFactory $itemFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Shipment\Item $validatorModel,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $itemFactory
    ) {
        $this->entityFactory = $itemFactory;
        $this->validatorModel = $validatorModel;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::SHIPMENT_ITEM;
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $item = $this->getEntity();
        $itemArray = $item->getData();
        return $itemArray;
    }

    /**
     * Get allowed fields
     *
     * @return array
     */
    public function getAllowedFields()
    {
        return array(
            'name',
            'sku',
            'price',
            'weight',
            'qty',
        );
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('shipment_item.sku', 'shipment_item.qty');
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'shipment_item.created_at' => 'Read only.',
            'shipment_item.updated_at' => 'Read only.'
        );
    }
}
