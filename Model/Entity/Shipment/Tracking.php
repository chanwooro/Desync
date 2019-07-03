<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Shipment;

/**
 * Entity shipment tracking class
 */
class Tracking extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Shipment\Tracking $validatorModel
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackingFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Shipment\Tracking $validatorModel,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackingFactory
    ) {
        $this->entityFactory = $trackingFactory;
        $this->validatorModel = $validatorModel;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::SHIPMENT_TRACKING;
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $tracking = $this->getEntity();
        $trackingArray = $tracking->getData();
        return $trackingArray;
    }

    /**
     * Get allowed fields
     *
     * @return array
     */
    public function getAllowedFields()
    {
        return array(
            'track_number',
            'number',
            'description',
            'title',
            'carrier_code',
            'weight',
            'qty'
        );
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
            'shipment_tracking.created_at' => 'Read only.',
            'shipment_tracking.updated_at' => 'Read only.'
        );
    }
}
