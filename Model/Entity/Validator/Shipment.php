<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator shipment class
 */
class Shipment extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
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
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\Entity\Shipment\TrackingFactory $shipmentTrackingFactory
     * @param \Dsync\Dsync\Model\Entity\Shipment\ItemFactory $shipmentItemFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\Entity\Shipment\TrackingFactory $shipmentTrackingFactory,
        \Dsync\Dsync\Model\Entity\Shipment\ItemFactory $shipmentItemFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->shipmentTrackingFactory = $shipmentTrackingFactory;
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->orderFactory = $orderFactory;
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
        $order = $this->orderFactory->create()->loadByIncrementId($data['order_increment_id']);

        // Validate order data
        $this->validateOrderData($data, $order);

        // Validate tracking data
        $this->validateTrackingData($data);

        // Validate items data
        $this->validateItemsData($data, $order);

        return true;
    }

    /**
     * Validate order information before creating a shipment
     *
     * @param array $data
     * @param \Magento\Sales\Model\Order $order
     * @throws \Dsync\Dsync\Exception
     */
    protected function validateOrderData($data, $order)
    {
        // Check if there is a valid order available
        if (!$order->getId()) {
            $error = __('The requested order does not exist (%1).', $data['order_increment_id']);
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
        // Check shipment is available to create separate from invoice
        if ($order->getForcedShipmentWithInvoice()) {
            $error = __('Can not currently create a shipment for this order separately from invoice.');
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
        // Check shipment create availability
        if (!$order->canShip()) {
            $error = __('Can not currently create a shipment for this order.');
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate tracking data
     *
     * @param array $data
     * @throws \Dsync\Dsync\Exception
     */
    protected function validateTrackingData($data)
    {
        // Validate tracking data
        if (isset($data['tracking'])) {
            if (empty($data['tracking'][0]) || !is_array($data['tracking'][0])) {
                throw new \Dsync\Dsync\Exception('Tracking data is not a valid array');
            }
            foreach (array_values($data['tracking']) as $value) {
                if (is_array($value) && !empty($value)) {
                    $trackEntity = $this->shipmentTrackingFactory->create()
                        ->setDestinationDataArray($value);
                    $trackEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
                } else {
                    throw new \Dsync\Dsync\Exception('Tracking data is not a valid array');
                }
            }
        }
    }

    /**
     * Validate the order items request for a shipment
     *
     * @param array $data
     * @param \Magento\Sales\Model\Order $order
     * @throws \Dsync\Dsync\Exception
     */
    protected function validateItemsData($data, $order)
    {
        // Check items data
        if (empty($data['items'][0]) || !is_array($data['items'][0])) {
            $error = __(
                'The shipment items data is empty for the request entity (%1).',
                $this->getEntityObject()->getEntityType()
            );
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }

        // Validate items data
        $orderItems = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $orderItems[$item->getSku()] = $item;
        }
        foreach (array_values($data['items']) as $shipmentItem) {
            $shipmentItemEntity = $this
                ->shipmentItemFactory
                ->create()
                ->setDestinationDataArray($shipmentItem);
            $shipmentItemEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
            if (!array_key_exists($shipmentItem['sku'], $orderItems)) {
                $error = __('The shipment item SKU: %1 is not available in this order.', $shipmentItem['sku']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
            $orderItem = $orderItems[$shipmentItem['sku']];
            if ($orderItem->getQtyToShip() < $shipmentItem['qty']) {
                $error = __('There is not enough of SKU: %1 available to ship in this order.', $shipmentItem['sku']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
            if ($orderItem->getIsVirtual()) {
                $error = __('Item %1 is a virtual product and can not be shipped.', $shipmentItem['sku']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Validate an update request
     *
     * @return boolean
     * @throws \Dsync\Dsync\Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validateUpdate()
    {
        $shipment = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();

        if (isset($data['tracking'])) {
            if (empty($data['tracking'][0]) || !is_array($data['tracking'][0])) {
                throw new \Dsync\Dsync\Exception('Tracking data is not a valid array');
            }
            foreach (array_values($data['tracking']) as $value) {
                if (is_array($value) && !empty($value)) {
                    $trackEntity = $this->shipmentTrackingFactory->create()
                        ->setDestinationDataArray($value);
                    $trackEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
                } else {
                    throw new \Dsync\Dsync\Exception('Tracking data is not a valid array');
                }
            }
        }
        return true;
    }

    /**
     * Get restricted create fields
     *
     * @return array
     */
    public function getRestrictedCreateFields()
    {
        return array('increment_id', 'billing_address', 'shipping_address');
    }

    /**
     * Get restricted update fields
     *
     * @return array
     */
    public function getRestrictedUpdateFields()
    {
        return array('order_increment_id', 'increment_id', 'items');
    }
}
