<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity inventory class
 */
class Inventory extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @var \Dsync\Dsync\Model\ResourceModel\Stock\Item\CollectionFactory $itemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Inventory $validatorModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Inventory $validatorModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory,
        \Dsync\Dsync\Model\ResourceModel\Stock\Item\CollectionFactory $itemCollectionFactory
    ) {
        $this->entityFactory = $itemFactory;
        $this->productFactory = $productFactory;
        $this->validatorModel = $validatorModel;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::INVENTORY;
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
     * Function to emulate the create process of an inventory item.
     */
    protected function processCreate()
    {
        // once there is an item,
        // update it to have the details from the create request
        $data = $this->getDestinationDataArray();

        $productId = $this->productFactory->create()->getIdBySku($data['sku']);

        $stockItem = $this
            ->getEntityFactory()
            ->create()
            ->load($productId, 'product_id');

        foreach ($data as $field => $value) {
            $stockItem->setData($field, $value);
        }
        try {
            $stockItem->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($stockItem);
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $inventory = $this->getEntity();
        $inventoryArray = $inventory->getData();
        $productId = $this->getEntity()->getProductId();
        $product = $this->productFactory->create()->load($productId);
        $inventoryArray['sku'] = $product->getSku();
        return $inventoryArray;
    }

    /**
     * Update the entity
     *
     * @throws \Exception
     */
    protected function processUpdate()
    {
        $stockItem = $this->getEntity();
        $data = $this->getDestinationDataArray();
        foreach ($data as $field => $value) {
            $stockItem->setData($field, $value);
        }
        try {
            $stockItem->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        $product = $this->productFactory->create()->load($stockItem->getProductId());
        // add the sku so it can be used in the response if needed
        $stockItem->setData('sku', $product->getSku());
        return $this->generateResponseArray($stockItem);
    }

    /**
     * A list of boolean fields for an entity
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return array(
            'use_config_min_qty',
            'is_qty_decimal',
            'use_config_backorders',
            'use_config_min_sale_qty',
            'use_config_max_sale_qty',
            'use_config_notify_stock_qty',
            'manage_stock',
            'use_config_manage_stock',
            'use_config_qty_increments',
            'use_config_enable_qty_inc',
            'enable_qty_increments',
            'is_decimal_divided',
            'notify_stock_qty',
            'is_in_stock'
        );
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'item_id');
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('inventory.sku');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'inventory.created_at' => 'Read only.',
            'inventory.updated_at' => 'Read only.',
            'inventory.sku' => 'Read only.',
            'inventory.item_id' => 'Read only.'
        );
    }

    /**
     * Get the excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array('product_id', 'stock_id', 'low_stock_date', 'type_id',
            'stock_status_changed_auto', 'stock_status_changed_automatically', 'product_name', 'store_id',
            'product_type_id', 'product_status_changed', 'product_changed_websites',
            'use_config_enable_qty_increments', 'website_id');
    }

    /**
     * Get included schema fields
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        return array('sku' => 'string');
    }

    /**
     * @return \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection
     */
    public function getEntityCollection()
    {
        return $this->itemCollectionFactory->create();
    }

    /**
     * Get the entity id field
     *
     * @return string
     */
    public function getEntityIdField()
    {
        return 'item_id';
    }

    /**
     * Load an entity on this model by the entity id
     * but check for a SKU first
     *
     * @param mixed $id
     * @return \Dsync_Dsync_Model_Entity_Abstract
     * @throws Dsync_Dsync_Exception
     */
    public function loadEntity($id)
    {
        $productId = $this->productFactory->create()->getIdBySku($id);
        if ($productId) {
            $entity = $this->getEntityFactory()->create()->load($productId, 'product_id');
            if ($entity->getId()) {
                $id = $entity->getId();
            }
        }
        return parent::loadEntity($id);
    }

    /**
     * Load an entity on this model by the shared key
     *
     * @param mixed $id
     * @return \Dsync\Dsync\Model\Entity\Inventory
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityBySharedKey($id)
    {
        if ($this->getSharedKey() == 'sku') {
            $error = __('This entity could not be loaded with the selected SKU.');
            $productId = $this->productFactory->create()->getIdBySku($id);
            if (!$productId) {
                throw new \Dsync\Dsync\Exception($error);
            }
            $entity = $this->getEntityFactory()->create()->load($productId, 'product_id');
            if ($entity->getId()) {
                $this->setEntity($entity);
                return $this;
            } else {
                throw new \Dsync\Dsync\Exception($error);
            }
        }
        return parent::loadEntityBySharedKey($id);
    }
}
