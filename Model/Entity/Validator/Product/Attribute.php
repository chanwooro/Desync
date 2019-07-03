<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator\Product;

/**
 * Entity validator product attribute class
 */
class Attribute extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
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
     * @return boolean
     */
    public function validateCreate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();
        $attribute = $this->checkAttribute($data['attribute_code']);
        if ($attribute) {
            $error = __('The requested attribute code already exists (%1).', $data['attribute_code']);
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
        return true;
    }

    /**
     * Validate an update request
     *
     * @return boolean
     */
    public function validateUpdate()
    {

        $entity = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();
        // check the source model
        $this->checkSourceModel($data);

        // check if the attribute code already exists on another attribute before updating
        $attribute = $this->checkAttribute($data['attribute_code']);
        if ($attribute) {
            if ($attribute->getId() != $entity->getId()) {
                $error = __(
                    'The requested attribute code already exists on another attribute (%1).',
                    $data['attribute_code']
                );
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }
        return true;
    }

    /**
     * Try to get the attribute by the code
     *
     * @param string $code
     * @return object
     */
    protected function checkAttribute($code)
    {
        return $this
            ->productFactory
            ->create()
            ->getResource()
            ->getAttribute($code);
    }
}
