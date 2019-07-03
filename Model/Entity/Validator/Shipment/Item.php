<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator\Shipment;

/**
 * Entity validator shipment item class
 */
class Item extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper
    ) {
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
        if (empty($data)) {
            $error = __(
                'The requested data is empty for the request entity (%1).',
                $this->getEntityObject()->getEntityType()
            );
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
