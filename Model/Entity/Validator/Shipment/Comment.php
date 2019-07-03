<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator\Shipment;

/**
 * Entity validator shipment comment class
 */
class Comment extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->shipmentRepository = $shipmentRepository;
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
        $shipmentModel = $this->shipmentRepository->create();
        $shipment = $shipmentModel->loadByIncrementId($data['shipment_increment_id']);

        if (!$shipment->getId()) {
            $error = __('The requested shipment does not exist (%1).', $data['shipment_increment_id']);
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
        return false;
    }
}
