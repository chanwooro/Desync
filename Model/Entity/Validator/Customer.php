<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator customer class
 */
class Customer extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
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

        // check the date of birth date format
        if (isset($data['dob'])) {
            if ($dob = $data['dob']) {
                $dob = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                if (!$dob) {
                    throw new \Dsync\Dsync\Exception('Invalid date format.');
                }
            }
        }

        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('email', $customerData['email']);

        $customer = $collection->getFirstItem();
        if ($customer->getId()) {
            throw new \Dsync\Dsync\Exception('This customer already exists. EMAIL: ' . $data['email']);
        }
        return true;
    }

    /**
     * Validate an update request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validateUpdate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();
        // trying to get the entity validates that it exists
        $customer = $this->getEntityObject()->getEntity();
        $customerData = array();
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

        // check the date of birth date format
        if (isset($data['dob'])) {
            if ($dob = $data['dob']) {
                $dob = \DateTime::createFromFormat('Y-m-d', $data['dob']);
                if (!$dob) {
                    throw new \Dsync\Dsync\Exception('Invalid date format.');
                }
            }
        }

        return true;
    }
}
