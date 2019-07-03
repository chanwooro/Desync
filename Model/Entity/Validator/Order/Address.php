<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator\Order;

/**
 * Entity validator order address class
 */
class Address extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
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
        
        // validate the country code
        if (isset($data['country_id'])) {
            $country = $this
                ->countryCollectionFactory
                ->create()
                ->addCountryIdFilter($data['country_id'])
                ->getFirstItem();
            if (!$country->getId()) {
                $error = __('Invalid country id (%1).', $data['country_id']);
                throw new \Dsync\Dsync\Exception($error);
            }
        }

        // validate the email address
        if (isset($data['email'])) {
            if ($data['email']) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $error = __('Invalid email address (%1).', $data['email']);
                    throw new \Dsync\Dsync\Exception($error);
                }
            }
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
        $address = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();
        if (!$address->getId()) {
            throw new \Dsync\Dsync\Exception('Invalid address entity set.');
        }
        if (!$data) {
            throw new \Dsync\Dsync\Exception('Invalid data set to update.');
        }

        // validate the country code
        if (isset($data['country_id'])) {
            $country = $this
                ->countryCollectionFactory
                ->create()
                ->addCountryIdFilter($data['country_id'])
                ->getFirstItem();
            if (!$country->getId()) {
                $error = __('Invalid country id (%1).', $data['country_id']);
                throw new \Dsync\Dsync\Exception($error);
            }
        }

        // validate the email address
        if (isset($data['email'])) {
            if ($data['email']) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $error = __('Invalid email address (%1).', $data['email']);
                    throw new \Dsync\Dsync\Exception($error);
                }
            }
        }
        return true;
    }

    /**
     * Get restricted update fields
     *
     * @return array
     */
    public function getRestrictedUpdateFields()
    {
        return array('address_type');
    }
}
