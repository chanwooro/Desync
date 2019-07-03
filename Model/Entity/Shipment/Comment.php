<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Shipment;

/**
 * Entity shipment comment class
 */
class Comment extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Shipment\Comment $validatorModel
     * @param \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Shipment\Comment $validatorModel,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $commentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->entityFactory = $commentFactory;
        $this->validatorModel = $validatorModel;
        $this->shipmentRepository = $shipmentRepository;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::SHIPMENT_COMMENT;
    }

    /**
     * Check to see if this entity is a primary entity and not a sub entity
     *
     * @return boolean
     */
    public function isEntityPrimary()
    {
        return true;
    }

    /**
     * Create the shipment comment entity
     *
     * @throws \Exception
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();

        $shipmentModel = $this->shipmentRepository->create();
        $shipment = $shipmentModel->loadByIncrementId($data['shipment_increment_id']);

        $comment = $data['comment'];
        $isCustomerNotified = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
        $isVisibleOnFront = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

        try {
            $commentModel = $this->entityFactory->create();
            $commentModel
                ->setComment($comment)
                ->setIsCustomerNotified($isCustomerNotified)
                ->setIsVisibleOnFront($isVisibleOnFront)
                ->setShipment($shipment)
                ->setParentId($shipment->getId())
                ->setStoreId($shipment->getStoreId())
                ->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($commentModel);
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $comment = $this->getEntity();

        if (!$comment->getShipment()) {
            $shipmentModel = $this->shipmentRepository->create();
            $shipment = $shipmentModel->load($comment->getParentId());
            $comment->setShipment($shipment);
        }
        $extraFields = array(
            'shipment_increment_id' => $comment->getShipment()->getIncrementId(),
            'order_increment_id' => $shipment->getOrder()->getIncrementId(),
            'external_order_id' => $shipment->getOrder()->getData('external_order_id')
        );
        $commentArray = array_merge($comment->getData(), $extraFields);
        return $commentArray;
    }

    /**
     * Get the excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'parent_id',
            'store_id',
        );
    }

    /**
     * A list of boolean fields for an entity
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return array(
            'is_customer_notified',
            'is_visible_on_front',
        );
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'entity_id', 'external_order_id', 'order_increment_id');
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('shipment_comment.shipment_increment_id', 'shipment_comment.comment');
    }

    /**
     * A list of schema fields for an entity that might not be
     * available on the entity itself and need to be included
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        return array(
            'shipment_increment_id' => 'varchar',
            'external_order_id' => 'varchar',
            'order_increment_id' => 'varchar'
        );
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'shipment_comment.created_at' => 'Read only.',
            'shipment_comment.updated_at' => 'Read only.',
            'shipment_comment.entity_id' => 'Read only.',
            'external_order_id' => 'Read only.'
        );
    }

    /**
     * Load an entity on this model by the shared key
     *
     * @param mixed $id
     * @return \Dsync\Dsync\Model\Entity\Abstract
     * @throws \Dsync\Dsync\Exception
     */
    public function loadEntityBySharedKey($id)
    {
        if ($this->getSharedKey() == 'shipment_increment_id') {
            throw new \Dsync\Dsync\Exception('Invalid shared key: shipment_increment_id');
        }
        return parent::loadEntityBySharedKey($id);
    }
}
