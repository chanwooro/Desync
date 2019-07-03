<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Customer;

/**
 * Entity customer class
 */
class Address extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    const MULTI_DELIMITER = ',';

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Customer\Address $validatorModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Customer\Address $validatorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {
        $this->entityFactory = $addressFactory;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::CUSTOMER_ADDRESS;
    }

    /**
     * Check to see if this entity is a primary entity and not a sub entity
     *
     * @return boolean
     */
    public function isEntityPrimary()
    {
        return true;
    }

    /**
     * Create the customer address entity
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();
        $customerAddressData = $data;

        if (isset($data['country_id'])) {
            $countryId = $data['country_id'];
            unset($data['country_id']);
        }

        if (isset($data['region'])) {
            $data['region_id'] = $data['region'];
        }

        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);

            if (!$attribute) {
                continue;
            }
            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        $customerAddressData[$field] = $value;
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getSourceOptionId($source, $value);
                $value = $optionId;
            }
            $customerAddressData[$field] = $value;
        }

        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('email', $customerAddressData['email']);
        unset($customerAddressData['email']);
        $customer = $collection->getFirstItem();
        $customerAddressData['parent_id'] = $customer->getId();
        $customerAddressData['country_id'] = $countryId;
        $customerAddress = $this->getEntityFactory()->create();
        try {
            $customerAddress
                ->addData($customerAddressData)
                ->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customerAddress);
    }

    /**
     * Process an entity read
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processRead()
    {
        $customerAddress = $this->getEntity();
        $customerAddressArray = $customerAddress->getData();

        foreach ($customerAddressArray as $field => $value) {
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }
            if ($attribute->usesSource()) {
                // skip the country id
                if ($field == 'country_id') {
                    continue;
                } else {
                    $option = $attribute->getSource()->getOptionText($value);
                }
                if ($value && empty($option) && $option != '0') {
                    continue;
                }
                if (is_array($option)) {
                    $value = join(self::MULTI_DELIMITER, $option);
                } else {
                    $value = $option;
                }
                unset($option);
            } elseif (is_array($value)) {
                continue;
            }
            $customerAddressArray[$field] = $value;
        }
        if ($customerAddress->getCustomer()) {
            $customerAddressArray['email'] = $customerAddress->getCustomer()->getEmail();
        }
        if ($customerAddress->getCountryId()) {
            $customerAddressArray['country_id'] = $customerAddress->getCountryId();
        }
        return $customerAddressArray;
    }

    /**
     * Update the customer address entity
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processUpdate()
    {
        $customerAddress = $this->getEntity();
        $data = $this->getDestinationDataArray();

        if (isset($data['country_id'])) {
            $customerAddress->setCountryId($data['country_id']);
            unset($data['country_id']);
        }

        if (isset($data['region'])) {
            $data['region_id'] = $data['region'];
        }

        if (isset($data['email'])) {
            unset($data['email']);
        }

        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                $customerAddress->setData($field, $value);
                continue;
            }

            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        $customerAddress->setData($field, $value);
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getSourceOptionId($source, $value);
                $value = $optionId;
            }
            $customerAddress->setData($field, $value);
        }
        try {
            $customerAddress->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customerAddress);
    }

    /**
     * Delete the customer address entity
     *
     * @throws \Exception
     */
    protected function processDelete()
    {
        $customerAddress = $this->getEntity();
        try {
            $customerAddress->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customerAddress);
    }

    /**
     * Creates schema based on attributes for a customer
     *
     * @return array
     */
    protected function processSchema()
    {
        $schemaArray = array();
        $entityModel = $this->entityFactory->create();
        $attributes = $entityModel->getAttributes();

        foreach ($attributes as $attribute) {
            $type = $attribute->getBackendType();
            if ($attribute->usesSource()) {
                $type = 'text';
            }
            $schemaArray[$attribute->getName()] = $type;
        }
        return $schemaArray;
    }

    /**
     * Get the excluded fields
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
            'store_id',
            'is_customer_save_transaction',
            'increment_id',
            'attribute_set_id',
            'region_id',
            'is_active',
            'vat_request_success',
            'vat_is_valid',
            'vat_request_id',
            'vat_request_date'
        );
    }

    /**
     * A list of required fields in order to create an entity
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array(
            'customer_address.email',
            'customer_address.firstname',
            'customer_address.lastname',
            'customer_address.street',
            'customer_address.country_id',
            'customer_address.city',
            'customer_address.postcode',
            'customer_address.telephone'
        );
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'customer_address.created_at' => 'Read only.',
            'customer_address.updated_at' => 'Read only.',
            'customer_address.email' => 'Read only. Required to create addresses.',
            'customer_address.entity_id' => 'Read only.'
        );
    }

    /**
     * A list of schema fields for an entity that might not be
     * available on the entity itself and need to be included
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        $schemaArray = array(
            'created_at' => 'date',
            'updated_at' => 'date',
        );
        $schemaArray['email'] = 'text';
        $schemaArray['is_default_billing'] = 'boolean';
        $schemaArray['is_default_shipping'] = 'boolean';
        return $schemaArray;
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
     * Load entity by the shared key
     *
     * @param mixed $id
     * @return \Dsync\Dsync\Model\Entity\Customer\Address
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityBySharedKey($id)
    {
        if ($this->getSharedKey() == 'email') {
            throw new \Dsync\Dsync\Exception('Invalid shared key: email');
        }
        $error = _('This entity could not be loaded with the selected shared key.');
        /* @var $collection \Magento\Customer\Model\ResourceModel\Address\Collection */
        $collection = $this->entityFactory->create()->getCollection()
            ->addAttributeToFilter($this->getSharedKey(), $id);
        $entity = $collection->getFirstItem();
        if ($entity->getId()) {
            $this->setEntity($entity);
            return $this;
        } else {
            throw new \Dsync\Dsync\Exception($error);
        }
    }
}
