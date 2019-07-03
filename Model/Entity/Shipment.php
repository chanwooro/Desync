<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity shipment class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Dsync\Dsync\Model\Entity\Shipment\TrackingFactory $shipmentTrackingFactory
     */
    protected $shipmentTrackingFactory;

    /**
     * @var \Dsync\Dsync\Model\Entity\Shipment\ItemFactory $shipmentItemFactory
     */
    protected $shipmentItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\TrackFactory $trackFactory
     */
    protected $trackFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     */
    protected $trackCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory $transactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     */
    protected $shipmentSender;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Shipment $validatorModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Dsync\Dsync\Model\Entity\Shipment\TrackingFactory $shipmentTrackingFactory
     * @param \Dsync\Dsync\Model\Entity\Shipment\ItemFactory $shipmentItemFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Shipment $validatorModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Dsync\Dsync\Model\Entity\Shipment\TrackingFactory $shipmentTrackingFactory,
        \Dsync\Dsync\Model\Entity\Shipment\ItemFactory $shipmentItemFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
    ) {
        $this->entityFactory = $shipmentRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->shipmentTrackingFactory = $shipmentTrackingFactory;
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->trackFactory = $trackFactory;
        $this->orderFactory = $orderFactory;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentSender = $shipmentSender;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::SHIPMENT;
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
     * Create an entity
     *
     * @throws \Exception
     */
    public function processCreate()
    {
        $data = $this->getDestinationDataArray();
        $order = $this
            ->orderFactory
            ->create()
            ->loadByIncrementId($data['order_increment_id']);

        // shipment items are mandatory in order to create a shipment
        $shipmentItems = $this->createShipmentItemsData($order, $data);
        $shipment = $this->shipmentFactory->create($order, $shipmentItems);

        // add tracking information
        if (isset($data['tracking'])) {
            foreach (array_values($data['tracking']) as $value) {
                if (is_array($value)) {
                    $tracking = $this->trackFactory->create()
                        ->addData($value);
                    $shipment->addTrack($tracking);
                }
            }
            unset($data['tracking']);
        }
        try {
            $shipment
                ->register()
                ->setEmailSent(true);
            $order->setIsInProcess(true);
            $transaction = $this->transactionFactory->create();
            $transaction
                ->addObject($shipment)
                ->addObject($order)
                ->save();
             $this->shipmentSender->send($shipment);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($shipment);
    }

    /**
     * Create items data for the shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @return array
     */
    protected function createShipmentItemsData($order, $data)
    {
        // shipment items are mandatory in order to create a shipment
        $shipmentItems = array();
        $orderItems = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $orderItems[$item->getSku()] = $item;
        }
        foreach (array_values($data['items']) as $shipmentItem) {
            $orderItem = $orderItems[$shipmentItem['sku']];
            if ($orderItem->getQtyToShip() && !$orderItem->getIsVirtual()) {
                $shipmentItems[$orderItem->getId()] = $shipmentItem['qty'];
            }
        }
        return $shipmentItems;
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    public function processRead()
    {
        $shipment = $this->getEntity();

        $extraFields = array(
            'order_increment_id' => $shipment->getOrder()->getIncrementId(),
            'external_order_id' => $shipment->getOrder()->getData('external_order_id'),
            'store' => $this->storeManager->getStore($shipment->getStoreId())->getCode()
        );

        $shipmentArray = array_merge($shipment->getData(), $extraFields);

        // add tracking information
        $tracking = array();
        foreach ($shipment->getTracksCollection() as $track) {
            if (!$track->isDeleted()) {
                $trackEntity = $this->shipmentTrackingFactory->create()
                    ->setEntity($track);
                $tracking[] = $trackEntity->read();
            }
        }
        if (!empty($tracking)) {
            $shipmentArray['tracking'] = $tracking;
        }
        // add shipment items information
        if ($shipment->getAllItems()) {
            $items = array();
            foreach ($shipment->getAllItems() as $item) {
                $itemEntity = $this->shipmentItemFactory->create()
                    ->setEntity($item);
                $items[] = $itemEntity->read();
            }
            $shipmentArray['items'] = $items;
        }
        return $shipmentArray;
    }

    /**
     * Update an entity
     *
     * @throws \Exception
     */
    public function processUpdate()
    {
        $shipment = $this->getEntity();
        $data = $this->getDestinationDataArray();
        if (isset($data['tracking'])) {
            // delete data from the collection itself
            $trackCollection = $this->trackCollectionFactory->create()
                ->setShipmentFilter($shipment->getId());
            foreach ($trackCollection as $track) {
                $track->delete();
            }
            foreach (array_values($data['tracking']) as $value) {
                if (is_array($value)) {
                    $tracking = $this->trackFactory->create()
                        ->addData($value);
                    $shipment->addTrack($tracking);
                }
            }
            unset($data['tracking']);
        }
        foreach ($data as $field => $value) {
            $shipment->setData($field, $value);
        }

        try {
            $shipment->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($shipment);
    }

    /**
     * Get excluded fiels
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'id',
            'store_id',
            'order_id',
            'customer_id',
            'shipping_address_id',
            'billing_address_id',
            'tracks',
            'packages'
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
     * A list of boolean fields for an entity
     *
     * @return array
     */
    public function getBooleanFields()
    {
        return array(
            'email_sent',
            'send_email'
        );
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('shipment.order_increment_id', 'shipment.items.sku', 'shipment.items.qty');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        return array(
            'shipment.created_at' => 'Read only.',
            'shipment.updated_at' => 'Read only.',
            'shipment.order_increment_id' => 'Required to create a shipment.',
            'shipment.items' => 'Required to create a shipment.',
            'shipment.entity_id' => 'Read only.',
            'shipment.external_order_id' => 'Read only.'
        );
    }

    /**
     * A list of schema fields for an entity that might not be
     * available on the entity itself and need to be included
     *
     * @return array
     */
    public function getIncludedSchemaFields()
    {
        $schemaFields = array();
        $schemaFields['order_increment_id'] = 'string';
        $schemaFields['external_order_id'] = 'string';
        $trackEntity = $this->shipmentTrackingFactory->create();
        $schemaFields['tracking'] = array($trackEntity->schema());
        $itemEntity = $this->shipmentItemFactory->create();
        $schemaFields['items'] = array($itemEntity->schema());
        return $schemaFields;
    }
}
