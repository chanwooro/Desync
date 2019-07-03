<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Product;

/**
 * Entity product image class
 */
class Image extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $mediaResourceModel;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaResourceModel
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaResourceModel
    ) {
        $this->productFactory = $productFactory;
        $this->mediaResourceModel = $mediaResourceModel;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::PRODUCT_IMAGE;
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
     * Read the entity
     *
     * @return array
     */
    public function processRead()
    {
        $imageData = $this->getEntity()->getData();

        $imageDataArray = [];

        $valueId = $imageData['id'];
        $productId = $imageData['entity_id'];

        $product = $this->productFactory->create()->load($productId);

        foreach ($product->getMediaGalleryImages() as $image) {
            if ($image['value_id'] == $valueId) {
                $imageDataArray = $image->getData();
                $imageDataArray['sku'] = $product->getSku();
                break;
            }
        }
        return $imageDataArray;
    }

    /**
     * Creates schema based on product image
     *
     * @return array
     */
    public function processSchema()
    {
        $schemaArray = [];
        $schemaArray['value_id'] = 'number';
        $schemaArray['file'] = 'text';
        $schemaArray['product_id'] = 'number';
        $schemaArray['label'] = 'text';
        $schemaArray['position'] = 'number';
        $schemaArray['disabled'] = 'number';
        return $schemaArray;
    }

    /**
     * Get included schema fields
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        return array(
            'url' => 'string',
            'sku' => 'string'
        );
    }

    /**
     * Get the entity id field
     *
     * @return string
     */
    public function getEntityIdField()
    {
        return 'value_id';
    }

    public function getEntityCollection()
    {
        return new \Magento\Framework\Data\Collection();
    }
}
