<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Product;

/**
 * Entity product attribute class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    protected $attributeOptionFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $eavEntityFactory;

    /**
     * @var Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attributeOptionCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Product\Attribute $validatorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $attributeOptionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionCollectionFactory
    ) {
        $this->entityFactory = $attributeFactory;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attributeOptionCollectionFactory = $attributeOptionCollectionFactory;
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::PRODUCT_ATTRIBUTE;
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
     * Create a new entity
     *
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     *
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();
        $attribute = $this->getEntityFactory()->create();

        if (isset($data['options'])) {
            $this->addOptionsData($data);
        }
        // get the store labels from the request
        $labels = array();
        foreach ($data['store_labels'] as $label) {
            try {
                $store = $this->storeManager->getStore($label['store']);
                $store->getId();
            } catch (\Exception $e) {
                continue;
            }
            $labels[$store->getId()] = $label['label'];
        }
        $data['frontend_label'] = $labels;
        $attribute->setData($data);
        $attribute->setEntityTypeId($this->getEntityTypeId());
        try {
            $attribute->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($attribute);
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $entity = $this->getEntity();

        $attribute = $this->getEntityFactory()->create()->load($entity->getId());
        $attributeArray = $attribute->getData();
        if ($attribute->usesSource()) {
            $optionsArray = $this->getCurrentOptions($attribute);
            if (!empty($optionsArray)) {
                $attributeArray['options'] = $optionsArray;
            }
        }
        $storeLabelsArray = array(
            array(
                'store' => $this->storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getCode(),
                'label' => $attribute->getFrontendLabel()
            )
        );
        foreach ($attribute->getStoreLabels() as $storeId => $label) {
            $storeLabelsArray[] = array(
                'store' => $this->storeManager->getStore($storeId)->getCode(),
                'label' => $label
            );
        }
        if (!empty($storeLabelsArray)) {
            $attributeArray['store_labels'] = $storeLabelsArray;
        }
        return $attributeArray;
    }

    /**
     * Update an entity
     *
     * @throws \Exception
     *
     */
    protected function processUpdate()
    {
        $entity = $this->getEntity();
        $data = $this->getDestinationDataArray();
        if (isset($data['options'])) {
            if ($entity->usesSource()) {
                $this->updateOptionsData($data, $entity);
            }
        }
        // get the store labels from the request
        $labels = array();
        foreach ($data['store_labels'] as $label) {
            try {
                $store = $this->storeManager->getStore($label['store']);
                $store->getId();
            } catch (\Exception $e) {
                continue;
            }
            $labels[$store->getId()] = $label['label'];
        }
        $data['frontend_label'] = $labels;
        foreach ($data as $field => $value) {
            $entity->setData($field, $value);
        }
        try {
            $entity->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($entity);
    }



    /**
     * Delete an entity
     *
     * @throws \Exception
     */
    protected function processDelete()
    {
        $entity = $this->getEntity();
        try {
            $entity->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($entity);
    }

    /**
     * Get the entity type id for the catalog/product
     *
     * @return int
     */
    protected function getEntityTypeId()
    {
        return $this
            ->eavEntityFactory
            ->create()
            ->setType(\Magento\Catalog\Model\Product::ENTITY)
            ->getTypeId();
    }

    /**
     * Add the options data
     *
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function addOptionsData(&$data)
    {
        $adminStoreId = $this->storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();
        $optionArray = array();
        $i = 1;
        foreach ($data['options'] as $option) {
            $optionLabels = array();
            // add the admin label from the option array
            $optionLabels[$adminStoreId] = $option['label'];
            if (isset($option['store_labels'])) {
                foreach ($option['store_labels'] as $label) {
                    // ignore stores that might not exist in this system
                    try {
                        $store = $this->storeManager->getStore($label['store']);
                        $store->getId();
                    } catch (\Exception $e) {
                        continue;
                    }
                    // ignore the admin label
                    if ($store->getId() == $adminStoreId) {
                        continue;
                    }
                    $optionLabels[$store->getId()] = $label['label'];
                }
            }
            if (isset($option['is_default'])) {
                if ($option['is_default']) {
                    $data['default'][] = 'option_' . $i;
                }
            }
            $optionArray[$i] = array(
                'labels' => $optionLabels,
                'sort_order' => isset($option['sort_order']) ? $option['sort_order'] : 0
            );
            $i++;
        }
        unset($data['options']);
        foreach ($optionArray as $key => $value) {
            $data['option']['value']['option_' . $key] = $value['labels'];
            $data['option']['order']['option_' . $key] = (int) $value['sort_order'];
        }
    }

    /**
     * Update the options data
     *
     * @param array $data
     * @param object $attribute
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateOptionsData(&$data, $attribute)
    {
        $adminStoreId = $this->storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();

        $currentOptions = $this->getCurrentOptions($attribute);
        $usedOptionIds = array();
        $optionArray = array();
        $i = 1;
        foreach ($data['options'] as $option) {
            $optionLabels = array();
            // add the admin label from the option array
            $optionLabels[$adminStoreId] = $option['label'];
            if (isset($option['store_labels'])) {
                foreach ($option['store_labels'] as $label) {
                    // ignore stores that might not exist in this system
                    try {
                        $store = $this->storeManager->getStore($label['store']);
                        $store->getId();
                    } catch (\Exception $e) {
                        continue;
                    }
                    // ignore the admin label
                    if ($store->getId() == $adminStoreId) {
                        continue;
                    }
                    $optionLabels[$store->getId()] = $label['label'];
                }
            }
            $optionId = null;
            foreach ($currentOptions as $currentOption) {
                if ($currentOption['label'] == $option['label']) {
                    $optionId = $currentOption['option_id'];
                    break;
                }
            }
            if ($optionId) {
                $optionKey = $optionId;
                $usedOptionIds[] = $optionId;
            } else {
                $optionKey = 'option_' . $i;
            }
            if (isset($option['is_default'])) {
                if ($option['is_default']) {
                    $data['default'][] = $optionKey;
                }
            }
            $optionArray[$optionKey] = array(
                'labels' => $optionLabels,
                'sort_order' => isset($option['sort_order']) ? $option['sort_order'] : 0
            );
            $i++;
        }
        // add or update options
        foreach ($optionArray as $key => $value) {
            $data['option']['value'][$key] = $value['labels'];
            $data['option']['order'][$key] = (int) $value['sort_order'];
        }
        // remove unused options
        foreach ($currentOptions as $currentOption) {
            if (in_array($currentOption['option_id'], $usedOptionIds)) {
                continue;
            }
            $data['option']['value'][$currentOption['option_id']] = array();
            $data['option']['delete'][$currentOption['option_id']] = 1;
        }
        unset($data['options']);
    }

    /**
     * Get the current options from an attribute
     *
     * @param object $attribute
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getCurrentOptions($attribute)
    {
        $options = $attribute->getSource()->getAllOptions(false);
        $optionsArray = array();
        if (!empty($options)) {
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    $optionsData = $option['value'];
                } else {
                    $optionsData = array(
                        'label' => $option['label'],
                        'value' => $option['value']
                    );
                }
                $optionsData['is_default'] = false;
                // set if it is the default option
                if ($attribute->getDefaultValue() == $optionsData['value']) {
                    $optionsData['is_default'] = true;
                }
                if (isset($optionsData['value'])) {
                    $stores = $this->storeManager->getStores(true);
                    $optionModel = $this
                        ->attributeOptionFactory
                        ->create()
                        ->load($optionsData['value']);
                    $optionsData['sort_order'] = $optionModel->getSortOrder();
                    $optionsData['option_id'] = $optionModel->getId();
                    $storeLabelArray = array();
                    foreach ($stores as $store) {
                        $valuesCollection = $this
                            ->attributeOptionCollectionFactory
                            ->create()
                            ->setStoreFilter($store->getId(), false)->load();
                        foreach ($valuesCollection as $item) {
                            if ($item->getId() == $optionModel->getId()) {
                                $storeLabelArray[] = array(
                                    'store' => $store->getCode(),
                                    'label' => $item->getValue()
                                );
                                break;
                            }
                        }
                    }
                    if (!empty($storeLabelArray)) {
                        $optionsData['store_labels'] = $storeLabelArray;
                    }
                }
                $optionsArray[] = $optionsData;
            }
        }
        return $optionsArray;
    }

    /**
     * Get excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'entity_type_id',
            'default_value',
            'frontend_label',
            'backend_model',
            'source_model',
            'frontend_model'
        );
    }

    /**
     * Get read only fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('attribute_id');
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array(
            'product_attribute.attribute_code',
            'product_attribute.backend_type',
            'product_attribute.frontend_input',
            'product_attribute.store_labels'
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
            'product_attribute.options' => 'Required to create dynamic options for an attribute.',
            'product_attribute.source_model' => 'Required to create dynamic options for an attribute.',
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
        return array(
            'options' => array(
                array(
                    'label' => 'string',
                    'sort_order' => 'number',
                    'is_default' => 'boolean',
                    'store_labels' => array(
                        array(
                            'store' => 'string',
                            'label' => 'string',
                        )
                    )
                )
            ),
            'store_labels' => array(
                array(
                    'store' => 'string',
                    'label' => 'string',
                )
            )
        );
    }

    /**
     * Get the entity id field
     *
     * @return string
     */
    public function getEntityIdField()
    {
        return 'attribute_id';
    }
}
