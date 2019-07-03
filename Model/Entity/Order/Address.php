<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Order;

/**
 * Entity order address class
 */
class Address extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Order\Address $validatorModel
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Order\Address $validatorModel,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory
    ) {
        $this->entityFactory = $addressFactory;
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
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER_ADDRESS;
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $address = $this->getEntity();
        $addressArray = $address->getData();
        return $addressArray;
    }

    /**
     * Update the order address entity
     *
     * @throws \Exception
     */
    protected function processUpdate()
    {
        $address = $this->getEntity();
        $data = $this->getDestinationDataArray();

        foreach ($data as $field => $value) {
            $address->setData($field, $value);
        }
        try {
            $address->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get an array of excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'customer_id',
            'customer_address_id',
            'quote_address_id',
            'giftregistry_item_id',
            'entity_type_id',
            'post_index',
            'parent_id',
            'created_at',
            'updated_at',
            'store_id',
            'is_customer_save_transaction',
            'increment_id',
            'attribute_set_id',
            'region_id',
            'is_active',
            'vat_request_success',
            'vat_id',
            'vat_is_valid',
            'vat_request_id',
            'vat_request_date',
            'address_type'
        );
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'entity_id');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'order_address.created_at' => 'Read only.',
            'order_address.updated_at' => 'Read only.',
            'order_address.entity_id' => 'Read only.'
        );
    }
}
