<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity abstract class
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
abstract class AbstractEntity
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var mixed
     */
    protected $dsyncEntityId;

    /**
     * @var object
     */
    protected $destinationData;

    /**
     * @var array
     */
    protected $destinationDataArray;

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     */
    protected $entityTypeModel;

    /**
     * @var \Dsync\Dsync\Model\Entity\Validator\AbstractValidator $validatorModel
     */
    protected $validatorModel;

    /**
     * @var mixed $entityFactory
     */
    protected $entityFactory;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $method;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
    ) {
        $this->helper = $helper;
        $this->entityTypeModel = $entityTypeModel;
    }

    /**
     * Set an entity
     *
     * @param mixed $entity
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function setEntity($entity)
    {
        $this->resetEntity();
        $this->entity = $entity;
        return $this;
    }

    /**
     * Set the method
     *
     * @param string $method
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set destination data
     *
     * @param object $destinationData
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function setDestinationData($destinationData)
    {
        $this->destinationData = $destinationData;
        return $this;
    }

    /**
     * Set the destination data array
     *
     * @param array $destinationDataArray
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function setDestinationDataArray(array $destinationDataArray)
    {
        $this->destinationDataArray = $this->filter($destinationDataArray);
        return $this;
    }

    /**
     * Set the dsync entity id
     *
     * @param mixed $dsyncEntityId
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function setDsyncEntityId($dsyncEntityId)
    {
        $this->dsyncEntityId = $dsyncEntityId;
        return $this;
    }

    /**
     * Get the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get destination data
     *
     * @return object
     */
    public function getDestinationData()
    {
        return $this->destinationData;
    }

    /**
     * Return pre filtered destination data as an array
     *
     * @return array
     */
    public function getDestinationDataArray()
    {
        if (!$this->destinationDataArray) {
            $destinationData = $this->getDestinationData();
            $destinationDataArray = json_decode(json_encode($destinationData), true);
            $this->setDestinationDataArray($destinationDataArray);
        }
        return $this->destinationDataArray;
    }

    /**
     * Get an entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        if (!$this->entity) {
            switch ($this->getMethod()) {
                case \Dsync\Dsync\Model\Api\Request\Method::CREATE:
                    // do nothing, you are not supposed to have an entity yet
                    break;
                default:
                    // load the entity by the Dsync entity id
                    $this->loadEntity($this->getDsyncEntityId());
                    break;
            }
        }
        return $this->entity;
    }

    /**
     * Get a set entity factory
     *
     * @return mixed
     */
    public function getEntityFactory()
    {
        return $this->entityFactory;
    }

    /**
     * Get the id of a loaded entity
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getEntity()->getId();
    }

    /**
     * @return \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
     */
    public function getValidatorModel()
    {
        return $this->validatorModel;
    }

    /**
     * Reset entity data when a new entity is set on this object
     */
    public function resetEntity()
    {
        // currently not doing anything
    }

    /**
     * Validate and process a create request
     */
    public function create()
    {
        $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
        $this
            ->getRegistry()
            ->set(
                $this->getEntityType(),
                $this->getMethod()
            );
        $this->validate($this->getMethod());
        return $this->processCreate();
    }

    /**
     * Process and return a read request
     */
    public function read()
    {
        $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::READ);
        $this
            ->getRegistry()
            ->set(
                $this->getEntityType(),
                $this->getMethod()
            );
        $readResponse = $this->processRead();
        return $this->filter($readResponse);
    }

    /**
     * Validate and process an update request
     */
    public function update()
    {
        $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::UPDATE);
        $this
            ->getRegistry()
            ->set(
                $this->getEntityType(),
                $this->getMethod()
            );
        $this->validate($this->getMethod());
        return $this->processUpdate();
    }

    /**
     * Validate and process a delete request
     */
    public function delete()
    {
        $this->setMethod(\Dsync\Dsync\Model\Api\Request\Method::DELETE);
        $this
            ->getRegistry()
            ->set(
                $this->getEntityType(),
                $this->getMethod()
            );
        return $this->processDelete();
    }

    /**
     * Create request - overwrite in extending class
     */
    protected function processCreate()
    {
        throw new \Dsync\Dsync\Exception('Method not allowed.');
    }

    /**
     * Read request - overwrite in extending class
     */
    protected function processRead()
    {
        throw new \Dsync\Dsync\Exception('Method not allowed.');
    }

    /**
     * Update request - overwrite in extending class
     */
    protected function processUpdate()
    {
        throw new \Dsync\Dsync\Exception('Method not allowed.');
    }

    /**
     * Delete request - overwrite in extending class
     */
    protected function processDelete()
    {
        throw new \Dsync\Dsync\Exception('Method not allowed.');
    }

    /**
     * Generate a schema for use with the data layout
     *
     * @return array
     */
    public function generateSchema()
    {
        $schemaArray = array();
        $fields = $this->schema();
        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                $schemaArray[] = $this
                    ->generateSchemaObject($key, $value, $this->getEntityType());
            } else {
                $schemaArray[] = $this
                    ->generateSchemaField($key, $value, $this->getEntityType());
            }
        }
        return $schemaArray;
    }

    /**
     * Generate a response array with either the entity or shared field
     * and value
     *
     * @param object $entity
     * @return array
     */
    public function generateResponseArray($entity)
    {
        $responseArray = [];
        if ($this->getSharedKey()) {
            $field = $this->getSharedKey();
        } else {
            $field = $this->getEntityIdField();
        }
        $responseArray[$field] = $entity->getData($field);
        return $responseArray;
    }

    /**
     * Validate an entity based on its method
     *
     * @param string $method
     * @throws \Dsync\Dsync\Exception
     */
    public function validate($method)
    {
        if (!$this->getValidatorModel()) {
            throw new \Dsync\Dsync\Exception(
                __('A validator is required to %1 a %2 entity.', $method, $this->getEntityType())
            );
        }
        $this->getValidatorModel()->validate($this, $method);
    }

    /*
     * Process and return this schema
     *
     * @return array
     */
    public function schema()
    {
        $schemaResponse = $this->filter($this->processSchema(), true);
        $schemaArray = array_merge($schemaResponse, $this->getIncludedSchemaFields());
        return $this->filterSchemaValues($schemaArray);
    }

    /**
     * Return a default schema for this entity model.  This will
     * use the returned main table name - extracted from "module/table" style.
     * This method can't be used for EAV models
     *
     * @return array
     */
    protected function processSchema()
    {
        $schemaArray = array();
        $entityModel = $this->entityFactory->create();
        // retrieve the main table for this entity model
        $mainTable = $entityModel
            ->getResource()
            ->getMainTable();
        // retrieve a description of the main table for this entity model
        $description = $entityModel
            ->getResource()
            ->getConnection()
            ->describeTable($mainTable);

        foreach ($description as $key => $value) {
            if (is_array($value)) {
                if (isset($value['DATA_TYPE'])) {
                    $schemaArray[$key] = $value['DATA_TYPE'];
                }
            }
        }
        return $schemaArray;
    }

    /**
     * Filter a schema array values to Dsync field types
     *
     * @param array $schemaArray
     * @return array
     */
    protected function filterSchemaValues($schemaArray)
    {
        $filteredSchemaArray = array();
        foreach ($schemaArray as $key => $value) {
            if (is_array($value)) {
                $value = $this->filterSchemaValues($value);
            } else {
                $value = $this->filterSchemaValue($value);
            }
            $filteredSchemaArray[$key] = $value;
        }
        return $filteredSchemaArray;
    }

    /**
     * Filter a value to be a valid field type or return
     * the original value
     *
     * @param string $value
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function filterSchemaValue($value)
    {
        switch ($value) {
            case 'bool':
            case 'boolean':
                $value = \Dsync\Dsync\Model\System\Config\Source\Field\Type::BOOLEAN;
                break;
            case 'decimal':
            case 'numeric':
            case 'float':
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'integer':
                $value = \Dsync\Dsync\Model\System\Config\Source\Field\Type::NUMBER;
                break;
            case 'date':
            case 'datetime':
            case 'timestamp':
                $value = \Dsync\Dsync\Model\System\Config\Source\Field\Type::DATE;
                break;
            case 'text':
            case 'tinytext':
            case 'char':
            case 'mediumtext':
            case 'longtext':
            case 'string':
            case 'static':
            case 'varchar':
            case 'blob':
            case 'mediumblob':
            case 'longblob':
            default:
                $value = \Dsync\Dsync\Model\System\Config\Source\Field\Type::TEXT;
                break;
        }
        return $value;
    }

    /**
     * Generate an object for the datalayout
     *
     * @param string $name
     * @param array $fields
     * @param string $treekey
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function generateSchemaObject($name, $fields, $treekey, $multiple = false)
    {
        $required = false;
        $description = null;

        $requiredFields = array();
        $fieldDescriptions = array();

        if ($treekey == $this->getEntityType()) {
            $requiredFields = $this->getRequiredFields();
            $fieldDescriptions = $this->getFieldDescriptions();
        }

        $treekey .= '.' . $name;

        $fieldsArray = array();
        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                $fieldsArray[] = $this
                    ->generateSchemaField($key, $value, $treekey);
            } else {
                if (is_int($key)) {
                    foreach ($value as $innerKey => $innerValue) {
                        if (is_array($innerValue)) {
                            $fieldsArray[] = $this
                                ->generateSchemaObject($innerKey, $innerValue, $treekey);
                        } else {
                            $fieldsArray[] = $this
                                ->generateSchemaField($innerKey, $innerValue, $treekey);
                        }
                    }
                    $multiple = true;
                } else {
                    $isMultiple = false;
                    foreach ($value as $innerValue) {
                        if (is_array($innerValue)) {
                            $fieldsArray[] = $this
                                ->generateSchemaObject($key, $innerValue, $treekey, true);
                            $isMultiple = true;
                            break;
                        }
                    }
                    if (!$isMultiple) {
                        $fieldsArray[] = $this
                            ->generateSchemaObject($key, $value, $treekey);
                    }
                }
            }
        }
        if (!empty($requiredFields)) {
            if (in_array($name, $requiredFields)) {
                $required = true;
            }
        }
        if (!empty($fieldDescriptions)) {
            if (array_key_exists($treekey, $fieldDescriptions)) {
                $fieldDescription = $fieldDescriptions[$treekey];
                $description = __($fieldDescription);
            }
        }
        $objectArray = array(
            'treekey' => $treekey,
            'name' => $name,
            'description' => $description,
            'required' => $required,
            'multiple' => $multiple,
            'type' => 'object',
            'fields' => $fieldsArray
        );

        return $objectArray;
    }

    /**
     * Generate a field for the datalayout
     *
     * @param string $name
     * @param string $type
     * @param string $treekey
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateSchemaField($name, $type, $treekey)
    {
        $required = false;
        $description = null;
        $multiple = false;

        $requiredFields = $this->getRequiredFields();
        $fieldDescriptions = $this->getFieldDescriptions();
        $multipleFields = $this->getMultipleFields();
        $dateFields = $this->getDateFormatFields();
        $foreignKeyFields = $this->getForeignKeyFields();
        $primaryKey = false;
        $foreignKey = null;

        $currentTreekey = $treekey . '.' . $name;

        if ($treekey == $this->getEntityType()) {
            if ($name == $this->getEntityIdField()) {
                $primaryKey = true;
            }
        }
        $dateFormat = \Dsync\Dsync\Helper\Data::DEFAULT_DATE_FORMAT;
        if (!empty($requiredFields)) {
            if (in_array($currentTreekey, $requiredFields)) {
                $required = true;
            }
        }
        if (!empty($fieldDescriptions)) {
            if (array_key_exists($currentTreekey, $fieldDescriptions)) {
                $fieldDescription = $fieldDescriptions[$currentTreekey];
                $description = __($fieldDescription);
            }
        }
        if (!empty($dateFields)) {
            if (array_key_exists($currentTreekey, $dateFields)) {
                $dateFormat = $dateFields[$currentTreekey];
                $type = \Dsync\Dsync\Model\System\Config\Source\Field\Type::DATE;
            }
        }
        if (!empty($foreignKeyFields)) {
            if (array_key_exists($currentTreekey, $foreignKeyFields)) {
                $foreignKey = $foreignKeyFields[$currentTreekey];
            }
        }
        if (!empty($multipleFields)) {
            if (in_array($currentTreekey, $multipleFields)) {
                $multiple = true;
            }
        }
        $fieldArray = array(
            'treekey' => $currentTreekey,
            'name' => $name,
            'description' => $description,
            'multiple' => $multiple,
            'required' => $required,
            'primary_key' => $primaryKey,
            'type' => $type
        );
        if ($foreignKey) {
            $fieldArray['foreign_key'] = $foreignKey;
        }
        if ($type == \Dsync\Dsync\Model\System\Config\Source\Field\Type::DATE) {
            $fieldArray['date_format'] = $dateFormat;
        }
        if ($type == \Dsync\Dsync\Model\System\Config\Source\Field\Type::BOOLEAN) {
            $fieldArray['bool_settings'] = ['representation' => 'standard'];
        }
        return $fieldArray;
    }

    /**
     * Get the shared key from system config for this entity
     *
     * @return string
     */
    public function getSharedKey()
    {
        return $this->getHelper()->getSharedKey($this->getEntityType());
    }

    /**
     * Get the entity id field
     *
     * @return string
     */
    public function getEntityIdField()
    {
        return 'entity_id';
    }

    /**
     * Get the current entity id or the shared key value if it is set
     *
     * @return mixed
     */
    public function getDsyncEntityId()
    {
        if ($this->dsyncEntityId) {
            return $this->dsyncEntityId;
        }
        if ($this->getSharedKey()) {
            $this->dsyncEntityId = $this->getEntity()->getData($this->getSharedKey());
        } else {
            $this->dsyncEntityId = $this->getEntityId();
        }
        return $this->dsyncEntityId;
    }

    /**
     * Return the entity id or the shared key if it is set
     *
     * @return string
     */
    public function getDsyncEntityIdField()
    {
        if ($this->getSharedKey()) {
            return $this->getSharedKey();
        }
        return $this->getEntityIdField();
    }

    /**
     * Check to see if this entity is enabled in system config
     *
     * @return boolean
     */
    public function isEntityActive()
    {
        return $this->getHelper()->isEntityActive($this->getEntityType());
    }

    /**
     * Check to see if this entity is a primary entity and not a sub entity
     *
     * @return boolean
     */
    public function isEntityPrimary()
    {
        return false;
    }

    /**
     * Get the job id for the currently loaded entity
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->entityTypeModel->getJobIdByEntityType($this->getEntityType());
    }

    /**
     * Get the entity token for the currently loaded entity
     *
     * @return string
     */
    public function getEntityToken($create = false)
    {
        return $this->entityTypeModel->getEntityTokenByEntityType($this->getEntityType(), $create);
    }

    /**
     * Get the Dsync entity type from the Magento entity type
     *
     * @return string
     */
    public function getDsyncEntityType()
    {
        return $this->entityTypeModel->getDsyncEntityType($this->getEntityType());
    }

    /**
     * Check a number of settings to see if this entity can be used
     *
     * @return boolean
     */
    public function isProcessable()
    {
        if ($this->getJobId() &&
            $this->getEntityToken() &&
            $this->getHelper()->isModuleActive() &&
            $this->getHelper()->getAuthToken() &&
            $this->isEntityActive()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Load an entity on this model by the entity id
     *
     * @param int $id
     * @return \Dsync\Dsync\Model\Entity\EntityAbstract
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityByEntityId($id)
    {
        $entityModel = $this->entityFactory->create();
        $entity = $entityModel->load($id);
        if ($entity->getId()) {
            $this->setEntity($entity);
            return $this;
        } else {
            throw new \Dsync\Dsync\Exception('This entity could not be loaded with the selected entity id.');
        }
    }

    /**
     * Load an entity on this model by the shared key
     *
     * @param int $id
     * @return \Dsync\Dsync\Model\Entity\EntityAbstract
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityBySharedKey($id)
    {
        $entityModel = $this->entityFactory->create();
        $entity = $entityModel->load($id, $this->getSharedKey());
        if ($entity->getId()) {
            $this->setEntity($entity);
            return $this;
        } else {
            throw new \Dsync\Dsync\Exception('This entity could not be loaded with the selected shared key.');
        }
    }

    /**
     * Load an entity on this model by the entity id
     *
     * @param mixed $id
     * @return \Dsync_Dsync_Model_Entity_Abstract
     * @throws Dsync_Dsync_Exception
     */
    public function loadEntity($id)
    {
        if ($this->getSharedKey()) {
            return $this->loadEntityBySharedKey($id);
        } else {
            return $this->loadEntityByEntityId($id);
        }
    }



    /**
     * Count the number of entities in the entire collection
     *
     * @return int
     */
    public function countEntities()
    {
        $collection = $this->getEntityCollection();
        return $collection->getSize();
    }

    /**
     * Get the entire collection of the current entity
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getEntityCollection()
    {
        $entityModel = $this->entityFactory->create();
        return $entityModel->getCollection();
    }

    /**
     * Return only the data which keys are allowed.  If data is set all
     * other field will be ignored.
     *
     * @param array $allowedFields List of attributes only available to use
     * @param array $data Associative array attribute to value
     * @return array
     */
    public function filterAllowed(array $allowedFields, array $data)
    {
        foreach (array_keys($data) as $attribute) {
            if (!in_array($attribute, $allowedFields)) {
                unset($data[$attribute]);
            }
        }
        return $data;
    }

    /**
     * Return only the data which keys are allowed.  If data is set those
     * fields will be removed.
     *
     * @param array $excludedFields List of attributes not available to use
     * @param array $data Associative array attribute to value
     * @return array
     */
    public function filterExcluded(array $excludedFields, array $data)
    {
        foreach (array_keys($data) as $attribute) {
            if (in_array($attribute, $excludedFields)) {
                unset($data[$attribute]);
            }
        }
        return $data;
    }

    /**
     * Remove ignored fields from a create or update request
     *
     * @param array $ignoredFields List of attributes not available to use
     * @param array $data Associative array attribute to value
     * @return array
     */
    public function filterIgnored(array $ignoredFields, array $data)
    {
        foreach (array_keys($data) as $attribute) {
            if (in_array($attribute, $ignoredFields)) {
                unset($data[$attribute]);
            }
        }
        return $data;
    }

    /**
     * Filter an array of data with allowed and excluded fields
     *
     * @param array $data
     * @param string $method
     * @return array
     */
    public function filter(array $data, $isGeneratingSchema = false)
    {
        $method = $this->getMethod();
        $allowedFields = $this->getAllowedFields();
        $excludedFields = $this->getExcludedFields();
        // filter fields that are only allowed
        if (!empty($allowedFields)) {
            $data = $this->filterAllowed($allowedFields, $data);
        }
        // filter fields that need to be excluded
        if (!empty($excludedFields)) {
            $data = $this->filterExcluded($excludedFields, $data);
        }
        if ($isGeneratingSchema) {
            $data = $this->convertBooleanFieldsSchema($data);
        } else {
            if ($method == \Dsync\Dsync\Model\Api\Request\Method::READ) {
                $data = $this->convertBooleanFieldsToText($data);
            } else {
                $data = $this->convertBooleanFieldsToNumber($this->preFilterRequest($data, $method));
            }
        }
        return $data;
    }

    /**
     * Remove all empty and ignored data when processing a request
     * unless it is applicable
     *
     * @param array $data
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function preFilterRequest(array $data, $method)
    {
//        if ($method == \Dsync\Dsync\Model\Api\Request\Method::CREATE //||
//            //$method == \Dsync\Dsync\Model\Api\Request\Method::UPDATE
//        ) {
//            foreach ($data as $key => $value) {
//                if (!$value) {
//                    if (is_bool($value) || is_numeric($value)) {
//                        continue;
//                    }
//                    if (in_array($key, $this->getNullFields())) {
//                        continue;
//                    }
//                    unset($data[$key]);
//                }
//            }
//        }
        return $this->filterIgnored($this->getReadOnlyFields(), $data);
    }

    /**
     * Convert boolean field to text in an array of data
     *
     * @param array $data
     * @return array
     */
    public function convertBooleanFieldsToText(array $data)
    {
        $booleanFields = $this->getBooleanFields();
        foreach ($data as $key => $value) {
            if (in_array($key, $booleanFields)) {
                // only convert it if it is numeric
                if (is_numeric($value)) {
                    if ($value) {
                        $data[$key] = 'true';
                    } else {
                        $data[$key] = 'false';
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Convert boolean field to numbers in an array of data
     *
     * @param array $data
     * @return array
     */
    public function convertBooleanFieldsToNumber(array $data)
    {
        $booleanFields = $this->getBooleanFields();
        foreach ($data as $key => $value) {
            if (in_array($key, $booleanFields)) {
                // only convert it if it is not numeric
                if (!is_numeric($value)) {
                    if ($value == 'true') {
                        $data[$key] = 1;
                    } else {
                        $data[$key] = 0;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Automatically set boolean fields type to 'text' when generating
     * a schema
     *
     * @param array $data
     * @return array
     */
    public function convertBooleanFieldsSchema(array $data)
    {
        $booleanFields = $this->getBooleanFields();
        foreach (array_keys($data) as $key) {
            if (in_array($key, $booleanFields)) {
                $data[$key] = \Dsync\Dsync\Model\System\Config\Source\Field\Type::TEXT;
            }
        }
        return $data;
    }

    /**
     * Retrieve eav entity attribute model
     *
     * @param string $code
     * @return mixed
     */
    public function getAttribute($code)
    {
        if (!isset($this->attributes[$code])) {
            $this->attributes[$code] = $this
                ->entityFactory
                ->create()
                ->getResource()
                ->getAttribute($code);
        }
        return $this->attributes[$code];
    }

    public function getSourceOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value)==0) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * A list of schema fields for an entity that might not be
     * available on the entity itself and need to be included
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        return array();
    }

    /**
     * Get a list of allowed fields for an entity
     *
     * @return array
     */
    public function getAllowedFields()
    {
        return array();
    }

    /**
     * A list of excluded fields for an entity
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array();
    }

    /**
     * A list of ignored fields for an entity, fields will be available on
     * the schema and the read request but removed when creating or updating
     * an entity
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array();
    }

    /**
     * A list of fields for an entity that are able to be null
     *
     * @return array
     */
    public function getNullFields()
    {
        return array();
    }

    /**
     * A list of boolean fields for an entity
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return array();
    }

    /**
     * A list of required fields in order to create an entity
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array();
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array();
    }

    /**
     * Get a list of text fields that are multiple
     * when generating the schema i.e a simple array
     *
     * @return array
     */
    public function getMultipleFields()
    {
        return array();
    }

    /**
     * Get fields and formats for specific date fields
     *
     * @return array
     */
    public function getDateFormatFields()
    {
        return array();
    }

    /**
     * Get the foreign key fields
     *
     * @return array
     */
    public function getForeignKeyFields()
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

    /**
     * Return the Dsync Registry
     *
     * @return \Dsync\Dsync\Model\Registry
     */
    protected function getRegistry()
    {
        return $this->getHelper()->getRegistry();
    }
}
