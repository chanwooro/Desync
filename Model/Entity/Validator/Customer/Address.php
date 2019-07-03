<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator\Customer;

/**
 * Entity validator customer address class
 */
class Address extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($helper);
    }

    /**
     * Validate a create request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateCreate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();
        $customerData = array();

        if (isset($data['country_id'])) {
            // validate the country id
            $country = $this
                ->countryCollectionFactory
                ->create()
                ->addCountryIdFilter($data['country_id'])
                ->getFirstItem();
            if (!$country->getId()) {
                $error = __('Invalid country id (%1).', $data['country_id']);
                throw new \Dsync\Dsync\Exception($error);
            }
            unset($data['country_id']);
        }

        foreach ($data as $field => $value) {
            $attribute = $this->getEntityObject()->getAttribute($field);

            if (!$attribute) {
                continue;
            }
            // check to see if the attribute source is valid
            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getEntityObject()->getSourceOptionId($source, $value);
                if (is_null($optionId)) {
                    $error = __('Invalid attribute option specified for attribute %1 (%2).', $field, $value);
                    throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
                }
                $value = $optionId;
            }
            $customerData[$field] = $value;
        }

        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('email', $data['email']);

        $customer = $collection->getFirstItem();
        if (!$customer->getId()) {
            throw new \Dsync\Dsync\Exception('Can not find customer: ' . $data['email']);
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
        $data = $this->getEntityObject()->getDestinationDataArray();
        // trying to get the entity validates that it exists
        $address = $this->getEntityObject()->getEntity();
        $customerData = array();

        if (isset($data['country_id'])) {
            // validate the country id
            $country = $this
                ->countryCollectionFactory
                ->create()
                ->addCountryIdFilter($data['country_id'])
                ->getFirstItem();
            if (!$country->getId()) {
                $error = __('Invalid country id (%1).', $data['country_id']);
                throw new \Dsync\Dsync\Exception($error);
            }
            unset($data['country_id']);
        }

        foreach ($data as $field => $value) {
            $attribute = $this->getEntityObject()->getAttribute($field);
            if (!$attribute) {
                continue;
            }
            // check to see if the attribute source is valid
            if ($attribute->usesSource()) {
                if (!$value) {
                    if ($value !== 0) {
                        continue;
                    }
                }
                $source = $attribute->getSource();
                $optionId = $this->getEntityObject()->getSourceOptionId($source, $value);
                if (is_null($optionId)) {
                    $error = __('Invalid attribute option specified for attribute %1 (%2).', $field, $value);
                    throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
                }
                $value = $optionId;
            }
            $customerData[$field] = $value;
        }
        return true;
    }
}
