<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model\System\Config\Source\Entity;

/**
 * System config source entity type
 */
class Type implements \Magento\Framework\Data\OptionSourceInterface
{

    const SOURCE_ENTITY_TOKEN_PREFIX = 'magento-2';

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    const PRODUCT = 'product';
    const PRODUCT_ATTRIBUTE = 'product_attribute';
    const PRODUCT_IMAGE = 'product_image';
    const INVENTORY = 'inventory';
    const ORDER = 'order';
    const ORDER_COMMENT = 'order_comment';
    const ORDER_ITEM = 'order_item';
    const ORDER_ADDRESS = 'order_address';
    const CUSTOMER = 'customer';
    const CUSTOMER_ADDRESS = 'customer_address';
    const SHIPMENT = 'shipment';
    const SHIPMENT_ITEM = 'shipment_item';
    const SHIPMENT_TRACKING = 'shipment_tracking';
    const SHIPMENT_COMMENT = 'shipment_comment';

    public function __construct(
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get all entity types
     *
     * @return array
     */
    public function getEntityTypes()
    {
        $entityTypes = array(
            self::PRODUCT => 'product',
            self::PRODUCT_ATTRIBUTE => 'product_attribute',
            self::PRODUCT_IMAGE => 'product_image',
            self::INVENTORY => 'inventory',
            self::ORDER => 'order',
            self::ORDER_COMMENT => 'order_comment',
            self::ORDER_ITEM => 'order_item',
            self::ORDER_ADDRESS => 'order_address',
            self::CUSTOMER => 'customer',
            self::CUSTOMER_ADDRESS => 'customer_address',
            self::SHIPMENT => 'shipment',
            self::SHIPMENT_ITEM => 'shipment_item',
            self::SHIPMENT_TRACKING => 'shipment_tracking',
            self::SHIPMENT_COMMENT => 'shipment_comment',
        );
        return array_merge($entityTypes, $this->getExtensionEntityTypes());
    }

    /**
     * Get array of job ids from the entity types
     *
     * @return array
     */
    public function getJobIds()
    {
        $jobIdArray = array();
        foreach (array_keys($this->getEntityTypes()) as $key) {
            $jobIdArray[$key] = $this->getHelper()->getStoreConfig('job_id/' . $key);
        }
        return $jobIdArray;
    }

    /**
     * Get a job id from an entity type
     *
     * @param string $entityType
     * @return string
     */
    public function getJobIdByEntityType($entityType)
    {
        $jobIdArray = $this->getJobIds();
        if (array_key_exists($entityType, $jobIdArray)) {
            return $jobIdArray[$entityType];
        }
        return null;
    }

    /**
     * Get array of entity tokens from the entity types
     *
     * @return array
     */
    public function getEntityTokens()
    {
        $entityTokenArray = array();
        foreach (array_keys($this->getEntityTypes()) as $key) {
            $entityTokenArray[$key] = $this->getHelper()
                ->getStoreConfig('entity_token/' . $key);
        }
        return $entityTokenArray;
    }

    /**
     * Get the entity type by the provided token
     *
     * @param string $entityToken
     * @return string
     */
    public function getEntityTypeByEntityToken($entityToken)
    {
        $entityTokenArray = $this->getEntityTokens();
        foreach ($entityTokenArray as $key => $value) {
            if ($entityToken == $value) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Get an entity token from an entity type
     *
     * @param string $entityType
     * @param bool   $create
     * @return string
     */
    public function getEntityTokenByEntityType($entityType, $create = false)
    {
        if (!$create) {
            $entityTokenArray = $this->getEntityTokens();
            if (array_key_exists($entityType, $entityTokenArray)) {
                $token = $entityTokenArray[$entityType];
                if ($token) {
                    return $token;
                }
            }
        }
        return $this->createEntityToken($entityType);
    }

    /**
     * Check if a dsync entity type is valid
     *
     * @param string $dsyncEntityType
     * @return boolean
     */
    public function isValidDsyncEntityType($dsyncEntityType)
    {
        if (in_array($dsyncEntityType, $this->getEntityTypes())) {
            return true;
        }
        return false;
    }

    /**
     * Returns a system entity type from the Dsync entity type
     *
     * @param string $dsyncEntityType
     * @return string
     */
    public function getEntityType($dsyncEntityType)
    {
        if (!$this->isValidDsyncEntityType($dsyncEntityType)) {
            return null;
        }
        foreach ($this->getEntityTypes() as $key => $value) {
            if ($value == $dsyncEntityType) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Get the Dsync entity type from the entity type
     *
     * @param string $entityType
     * @return string
     */
    public function getDsyncEntityType($entityType)
    {
        $entityTypes = $this->getEntityTypes();
        if (array_key_exists($entityType, $entityTypes)) {
            return $entityTypes[$entityType];
        }
        return null;
    }

    /**
     * Returns additional entity types.  Must be overwritten in parent.
     *
     * @return array
     */
    protected function getExtensionEntityTypes()
    {
        return array();
    }

    /**
     * Get the entity type options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        foreach (array_keys($this->getEntityTypes()) as $key) {
            $options[$key] = ucwords($key);
        }
        return $options;
    }

    /**
     * Generate and save the entity token
     *
     * @param string $entityType
     * @return string
     */
    protected function createEntityToken($entityType)
    {
        $entityToken = $this->generateEntityToken($entityType);
        $this
            ->getHelper()
            ->saveStoreConfig('entity_token/' . $entityType, $entityToken);
        return $entityToken;
    }

    /**
     * Generate an entity token from the entity type and the job id
     *
     * @param string $entityType
     * @return string
     */
    protected function generateEntityToken($entityType)
    {
        return self::SOURCE_ENTITY_TOKEN_PREFIX . '-'
            . str_replace('_', '-', $entityType) . '-'
                . md5(microtime());
    }

    /**
     * Get entity type options for admin
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();

        foreach ($this->getOptions() as $key => $value) {
            $optionArray[] = array(
                'value' => $key,
                'label' =>  $value,
            );
        }
        return $optionArray;
    }

     /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
