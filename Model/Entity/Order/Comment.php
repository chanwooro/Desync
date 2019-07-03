<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Order;

/**
 * Entity order comment class
 */
class Comment extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Order\Comment $validatorModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $commentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Order\Comment $validatorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Status\HistoryFactory $commentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->entityFactory = $commentFactory;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER_COMMENT;
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
     * Create the order comment entity
     *
     * @throws \Exception
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();

        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($data['order_increment_id']);

        $comment = $data['comment'];

        try {
            $history = $order->addStatusHistoryComment($comment);
            $history
                ->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($history);
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $comment = $this->getEntity();

        if (!$comment->getOrder()) {
            $orderModel = $this->orderFactory->create();
            $order = $orderModel->load($comment->getParentId());
            $comment->setOrder($order);
        }
        $extraFields = array(
            'order_increment_id' => $comment->getOrder()->getIncrementId(),
            'external_order_id' => $comment->getOrder()->getData('external_order_id'),
            'store' => $this->storeManager->getStore($comment->getStoreId())->getCode()
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
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('order_comment.order_increment_id');
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
            'store' => 'string',
            'order_increment_id' => 'varchar',
            'external_order_id' => 'varchar'
        );
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'entity_id', 'external_order_id');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'order_comment.created_at' => 'Read only.',
            'order_comment.updated_at' => 'Read only.',
            'order_comment.entity_id' => 'Read only.',
            'order_comment.external_order_id' => 'Read only.'
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
        if ($this->getSharedKey() == 'order_increment_id') {
            throw new \Dsync\Dsync\Exception('Invalid shared key: order_increment_id');
        }
        return parent::loadEntityBySharedKey($id);
    }
}
