<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator abstract class
 */
abstract class AbstractValidator
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Entity\AbstractEntity $entityObject
     */
    protected $entityObject;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Check if an entity object is valid against a method
     *
     * @param object $entityObject
     * @param string $method
     * @throws \Dsync\Dsync\Exception
     */
    public function validate($entityObject, $method)
    {
        $this->setEntityObject($entityObject);
        $isValid = null;
        switch ($method) {
            case \Dsync\Dsync\Model\Api\Request\Method::CREATE:
                $this->checkRestrictedCreateFields();
                // if it gets here validate the create
                $isValid = $this->validateCreate();
                break;
            case \Dsync\Dsync\Model\Api\Request\Method::UPDATE:
                $this->checkRestrictedUpdateFields();
                $isValid = $this->validateUpdate();
                break;
        }
        if (!$isValid) {
            throw new \Dsync\Dsync\Exception(
                __(
                    'Method %1 not allowed in the %2 entity.',
                    $method,
                    $entityObject->getEntityType()
                )
            );
        }
    }

    /**
     * Generate missing fields for a create request
     *
     * @param object $entityObject
     * @param array $data
     * @return array
     */
    public function generateMissingFields($entityObject, $data)
    {
        if (!$this->getEntityObject()) {
            $this->setEntityObject($entityObject);
        }
        return $this->populateMissingFields($data);
    }

    /**
     * Validate a create request
     *
     * @return boolean
     */
    public function validateCreate()
    {
        return false;
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

    /**
     * Set the entity object
     *
     * @param \Dsync\Dsync\Model\Entity\AbstractEntity $entityObject
     * @return \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
     */
    public function setEntityObject($entityObject)
    {
        $this->entityObject = $entityObject;
        return $this;
    }

    /**
     * Get the entity object
     *
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function getEntityObject()
    {
        return $this->entityObject;
    }

    /**
     * Check the restricted fields for creating an entity
     */
    public function checkRestrictedCreateFields()
    {
        $this->checkRestrictedFields($this->getRestrictedCreateFields(), 'creating');
    }

    /**
     * Check the restricted fields for updating an entity
     */
    public function checkRestrictedUpdateFields()
    {
        $this->checkRestrictedFields($this->getRestrictedUpdateFields(), 'updating');
    }

    /**
     * Check the restricted fields for updating or creating an entity
     *
     * @param array $restrictedFields
     * @param string $actionType
     * @throws \Dsync\Dsync\Exception
     */
    public function checkRestrictedFields($restrictedFields, $actionType)
    {
        $data = $this->getEntityObject()->getDestinationDataArray();
        foreach ($restrictedFields as $restrictedField) {
            if (array_key_exists($restrictedField, $data)) {
                $entityType = $this->getEntityObject()->getEntityType();
                $error = __(
                    'The field "%1" is restricted when %2 a "%3" entity.',
                    $restrictedField,
                    $actionType,
                    $entityType
                );
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * A list of restricted fields when creating an entity
     *
     * @return array
     */
    public function getRestrictedCreateFields()
    {
        return array();
    }

    /**
     * A list of restricted fields when updating an entity
     *
     * @return array
     */
    public function getRestrictedUpdateFields()
    {
        return array();
    }

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
