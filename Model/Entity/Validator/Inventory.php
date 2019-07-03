<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator inventory class
 */
class Inventory extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($helper);
    }

    /**
     * Validate a create request
     *
     * This method checks to see if the inventory item exists already and
     * returns that it has been created as the product entity "creates" it
     * when the product is created.
     *
     * @return boolean
     */
    public function validateCreate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();
        $productId = $this->productFactory->create()->getIdBySku($data['sku']);

        if (!$productId) {
            $error = __(
                'The requested item does not exist & is pending a retry (%1).',
                $data['sku']
            );
            // throw an exception to retry to process
            throw new \Exception($error);
        }
        return true;
    }

    /**
     * Validate an update request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validateUpdate()
    {
        $stockItem = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();

        $inventoryDataArray = array();
        foreach ($data as $field => $value) {
            $inventoryDataArray[$field] = $value;
        }

        // the restricted/required fields will validate automatically
        // do something here to validate the fields further if needed

        return true;
    }
}
