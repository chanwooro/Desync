<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity customer class
 */
class Customer extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    const MULTI_DELIMITER = ',';

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\GroupFactory $customerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Dsync\Dsync\Model\Entity\Customer\AddressFactory $inventoryFactory
     */
    protected $addressFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Customer $validatorModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Customer $validatorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Dsync\Dsync\Model\Entity\Customer\AddressFactory $addressFactory
    ) {
        $this->entityFactory = $customerFactory;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->addressFactory = $addressFactory;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::CUSTOMER;
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
     * Create the customer entity
     *
     * @throws \Exception
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();
        $customerData = $data;

        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);

            if (!$attribute) {
                continue;
            }
            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        $customerData[$field] = $value;
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getSourceOptionId($source, $value);
                $value = $optionId;
            }
            $customerData[$field] = $value;
        }
        $customer = $this->getEntityFactory()->create();
        try {
            $customer->addData($customerData)->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customer);
    }

    /**
     * Process an entity read
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processRead()
    {
        $customer = $this->getEntity();

        $customerArray = $customer->getData();

        // load the customer group manually because the option text is null in
        // Mage 2 when trying to retrieve the name from the source
        $group = $this->customerGroupFactory->create()->load($customer->getGroupId());

        // remove the group id from the array as it will be added after the
        // attributes are processed
        unset($customerArray['group_id']);

        // remove the store id from the array
        unset($customerArray['store_id']);

        foreach ($customerArray as $field => $value) {
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }
            if ($attribute->usesSource()) {
                $option = $attribute->getSource()->getOptionText($value);
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
            $customerArray[$field] = $value;
        }
        $addresses = [];
        foreach ($customer->getAddressesCollection() as $address) {
            $addressEntity = $this->addressFactory->create();
            $addressEntity->setEntity($address);
            $addresses[] = $addressEntity->read();
        }
        // add the group id back to the array
        $customerArray['group_id'] = $group->getCode();
        // add addresses
        $customerArray['addresses'] = $addresses;
        return $customerArray;
    }

    /**
     * Update the customer entity
     */
    protected function processUpdate()
    {
        $customer = $this->getEntity();
        $data = $this->getDestinationDataArray();

        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                $customer->setData($field, $value);
                continue;
            }

            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        $customer->setData($field, $value);
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getSourceOptionId($source, $value);
                $value = $optionId;
            }
            $customer->setData($field, $value);
        }
        try {
            $customer->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customer);
    }

    /**
     * Delete the customer entity
     *
     * @throws \Exception
     */
    protected function processDelete()
    {
        $customer = $this->getEntity();
        try {
            $customer->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($customer);
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
     * Get excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'password_hash',
            'default_billing',
            'default_shipping',
            'entity_type_id',
            'attribute_set_id',
            'increment_id',
            'store_id',
            'confirmation',
            'is_active',
            'rp_token',
            'rp_token_created_at',
            'reward_update_notification',
            'reward_warning_notification',
            'parent_id'
        );
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array(
            'customer.firstname',
            'customer.lastname',
            'customer.email',
            'customer.website_id',
            'customer.group_id'
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
            'customer.created_at' => 'Read only.',
            'customer.updated_at' => 'Read only.',
            'customer.entity_id' => 'Read only.',
            'customer.addresses' => 'Read only.',
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
        // add addresses item
        $addressEntity = $this->addressFactory->create();
        $schemaArray['addresses'] = array($addressEntity->schema());
        return $schemaArray;
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'addresses', 'entity_id');
    }

    /**
     * A list of boolean fields for an entity
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return array('disable_auto_group_change');
    }

    /**
     * Get fields and formats for specific date fields
     *
     * @return array
     */
    public function getDateFormatFields()
    {
        return array(
            'customer.dob' => 'YYYY-MM-DD'
        );
    }

    /**
     * Load entity by the shared key
     *
     * @param mixed $id
     * @return \Dsync\Dsync\Model\Entity\Customer
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityBySharedKey($id)
    {
        $error = _('This entity could not be loaded with the selected shared key.');
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
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
