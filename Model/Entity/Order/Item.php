<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Order;

/**
 * Entity order item class
 */
class Item extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Dsync\Dsync\Model\Entity\ProductFactory $productEntityFactory
     */
    protected $productEntityFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Dsync\Dsync\Model\Entity\ProductFactory $productEntityFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Dsync\Dsync\Model\Entity\ProductFactory $productEntityFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->entityFactory = $itemFactory;
        $this->productEntityFactory = $productEntityFactory;
        $this->productFactory = $productFactory;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER_ITEM;
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
        if ($item->getProduct()) {
            $product = $this
                ->productFactory
                ->create()
                ->setStoreId($item->getStoreId())
                ->load($item->getProductId());
            $productEntity = $this
                ->productEntityFactory
                ->create()
                ->setEntity($product);
            $itemArray['product'] = $productEntity->read();
        }
        return $itemArray;
    }

    /**
     * Get the allowed fields
     *
     * @return array
     */
    public function getAllowedFields()
    {
        return array(
//            'name',
//            'sku',
//            'price',
//            'weight',
//            'base_price',
//            'base_original_price',
//            'tax_percent',
//            'tax_amount',
//            'base_tax_amount',
//            'base_discount_amount',
//            'base_row_total',
//            'base_price_incl_tax',
//            'base_row_total_incl_tax',
//            'qty_backordered',
//            'qty_canceled',
//            'qty_invoiced',
//            'qty_ordered',
//            'qty_refunded',
//            'qty_shipped',
//            'product'
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
        $productEntity = $this->productEntityFactory->create();
        return array('product' => $productEntity->schema());
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
            'order_item.created_at' => 'Read only.',
            'order_item.updated_at' => 'Read only.'
        );
    }
}
