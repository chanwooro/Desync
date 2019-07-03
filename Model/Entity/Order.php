<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity order class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    /**
     * @var \Dsync\Dsync\Model\Entity\Order\ItemFactory $orderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory
     */
    protected $orderAddressFactory;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory $messageFactory
     */
    protected $messageFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface $cartManagement
     */
    protected $cartManagement;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Order $validatorModel
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory
     * @param \Dsync\Dsync\Model\Entity\Order\ItemFactory $orderItemFactory
     * @param \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Order $validatorModel,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Dsync\Dsync\Model\Entity\Order\ItemFactory $orderItemFactory,
        \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement
    ) {
        $this->entityFactory = $orderFactory;
        $this->messageFactory = $messageFactory;
        $this->validatorModel = $validatorModel;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->quoteFactory = $quoteFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER;
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
     * Create a new order entity
     *
     * @return array
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function processCreate()
    {
        $data = $this->getDestinationDataArray();

        // set the store id
        if (isset($data['store'])) {
            $store = $this->storeManager->getStore($data['store']);
            $storeId = $store->getId();
        }

        // set the currency code for this transaction
        $store->setCurrentCurrencyCode($data['order_currency_code']);

        // Create a new quote
        $quote = $this->quoteFactory->create();
        /* @var $quote \Magento\Quote\Model\Quote */

        $quote->setStoreId($storeId);
        $quote->setInventoryProcessed(false);

        // Look for an existing customer
        $customer = $this->customerFactory->create()
            ->setWebsiteId($store->getWebsiteId())
            ->loadByEmail($data['customer_email']);

        // Create a new customer
        if (!$customer->getId()) {
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($store->getWebsiteId())
                ->setStore($store)
                ->setFirstname($data['customer_firstname'])
                ->setLastname($data['customer_lastname'])
                ->setEmail($data['customer_email']);
            if (isset($data['customer_prefix'])) {
                $customer->setPrefix($data['customer_prefix']);
            }

            if (isset($data['customer_suffix'])) {
                $customer->setSuffix($data['customer_suffix']);
            }

            if (isset($data['customer_vat'])) {
                $customer->setVat($data['customer_vat']);
            }
            $customer->save();
        }

        $customer = $this->customerRepository->getById($customer->getId());

         // Add customer to the quote
        $quote->assignCustomer($customer);

        // Add the products to the quote
        foreach ($data['items'] as $item) {
            $productId = $this->productFactory->create()->getIdBySku($item['sku']);
            $product = $this->productFactory->create()->load($productId);
            $quoteItem = $quote->addProduct($product, $item['qty_ordered']);
            // if the price coming in is different from the current product
            // price we will set a custom price on it
            if (isset($item['price'])) {
                if ($item['price']) {
                    if ($item['price'] != $quoteItem->getPrice()) {
                        // set a custom price on the quote item
                        $quoteItem->setOriginalCustomPrice($item['price']);
                    }
                }
            }
        }

        // Add billing address to the quote
        if (isset($data['billing_address'])) {
            if (is_array($data['billing_address'])) {
                if (isset($data['billing_address']['entity_id'])) {
                    unset($data['billing_address']['entity_id']);
                }
                $quote->getBillingAddress()->addData($data['billing_address']);
            }
        }

        // Add shipping address to the quote
        if (isset($data['shipping_address'])) {
            if (is_array($data['shipping_address'])) {
                if (isset($data['shipping_address']['entity_id'])) {
                    unset($data['shipping_address']['entity_id']);
                }
                $quote->getShippingAddress()->addData($data['shipping_address']);
            }
        }

        // Add the coupon code to the quote
        if (isset($data['coupon_code'])) {
            $quote->setCouponCode($data['coupon_code']);
        }

        // add shipping method
        $quote
            ->getShippingAddress()
            ->setCollectShippingRates(true)
            ->setShippingMethod($data['shipping_method']);

        // add payment
        $quotePayment = $quote->getPayment();
        $quotePayment->setMethod($data['payment_method']);
        $quote->setPayment($quotePayment);

        try {
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();
            // check shipping method after collect totals
            if (!$quote->getShippingAddress()->getShippingMethod()) {
                throw new \Exception('Shipping method not available or active: ' . $data['shipping_method']);
            }
            // try to submit the quote
            $submitQuote = $this->cartRepository->get($quote->getId());
            $order = $this->cartManagement->submit($submitQuote);
        } catch (\Exception $e) {
            throw new \Dsync\Dsync\Exception($e->getMessage());
        }

        // add the gift message if it is set
        if (isset($data['gift_message'])) {
            $this->getRegistry()->set(
                \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER,
                \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD
            );
            $giftMessageArray = $data['gift_message'];
            $giftMessage = $this->messageFactory->create();
            if (isset($giftMessageArray['gift_message_from'])) {
                $giftMessage->setSender($giftMessageArray['gift_message_from']);
            }
            if (isset($giftMessageArray['gift_message_to'])) {
                $giftMessage->setRecipient($giftMessageArray['gift_message_to']);
            }
            if (isset($giftMessageArray['gift_message_body'])) {
                $giftMessage->setMessage($giftMessageArray['gift_message_body']);
            }
            $giftMessage->save();
            if (!$order->getGiftMessageId()) {
                $order->setGiftMessageId($giftMessage->getId())->save();
            }
        }

        // Add the external order id after the order is saved
        if (array_key_exists('external_order_id', $data) && $data['external_order_id']) {
            $this->getRegistry()->set(
                \Dsync\Dsync\Model\System\Config\Source\Entity\Type::ORDER,
                \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD
            );
            $order->setData('external_order_id', $data['external_order_id'])->save();
        }

        return $this->generateResponseArray($order);
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $order = $this->getEntity();
        $orderArray = $order->getData();

        $items = $this->filterOrderItems($order);

        // add the order items to the order array
        if (!empty($items)) {
            $orderArray['items'] = $items;
        }

        // add billing address to the order array
        if ($order->getBillingAddress()) {
            $billingAddress = $this->orderAddressFactory->create()
                ->setEntity($order->getBillingAddress());
            $orderArray['billing_address'] = $billingAddress->read();
        }

        // add shipping address to the order array
        if ($order->getShippingAddress()) {
            $shippingAddress = $this->orderAddressFactory->create()
                ->setEntity($order->getShippingAddress());
            $orderArray['shipping_address'] = $shippingAddress->read();
        }

        if ($order->getGiftMessageId()) {
            $giftMessage = $this->messageFactory->create()
                ->load($order->getGiftMessageId());
            if (!$giftMessage->isMessageEmpty()) {
                $orderArray['gift_message'] = array(
                    'gift_message_from' => $giftMessage->getSender(),
                    'gift_message_to'   => $giftMessage->getRecipient(),
                    'gift_message_body' => $giftMessage->getMessage()
                );
            }
        }

        if ($order->getPayment()) {
            $orderArray['payment_method'] = $order->getPayment()->getMethod();
        }

        $orderArray['store'] = $this->storeManager->getStore($order->getStoreId())->getCode();

        return $orderArray;
    }

    /**
     * Update the entity
     *
     * Currently only supports updating shipping and billing address
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processUpdate()
    {
        $order = $this->getEntity();
        /* @var $order \Magento\Sales\Model\Order */
        $data = $this->getDestinationDataArray();

        // update billing address if it is set
        if (isset($data['billing_address'])) {
            if (is_array($data['billing_address'])) {
                // if the email is blank in the update request, ignore trying
                // to update it as Magento throws an error
                if (isset($data['billing_address']['email'])) {
                    if (!$data['billing_address']['email']) {
                        unset($data['billing_address']['email']);
                    }
                }
                $addressEntity = $this->orderAddressFactory->create()
                    ->setEntity($order->getBillingAddress())
                    ->setDestinationDataArray($data['billing_address']);
                $addressEntity->update();
            }
        }

        // update shipping address if it is set
        if (isset($data['shipping_address'])) {
            if (is_array($data['shipping_address'])) {
                // if the email is blank in the update request, ignore trying
                // to update it as Magento throws an error
                if (isset($data['shipping_address']['email'])) {
                    if (!$data['shipping_address']['email']) {
                        unset($data['shipping_address']['email']);
                    }
                }
                $addressEntity = $this->orderAddressFactory->create()
                    ->setEntity($order->getShippingAddress())
                    ->setDestinationDataArray($data['shipping_address']);
                $addressEntity->update();
            }
        }
        // update the gift message if it is set
        if (isset($data['gift_message'])) {
            $giftMessageArray = $data['gift_message'];
            $giftMessage = $this->messageFactory->create();
            if ($order->getGiftMessageId()) {
                $giftMessage->load($order->getGiftMessageId());
            }
            if (isset($giftMessageArray['gift_message_from'])) {
                $giftMessage->setSender($giftMessageArray['gift_message_from']);
            }
            if (isset($giftMessageArray['gift_message_to'])) {
                $giftMessage->setRecipient($giftMessageArray['gift_message_to']);
            }
            if (isset($giftMessageArray['gift_message_body'])) {
                $giftMessage->setMessage($giftMessageArray['gift_message_body']);
            }
            $giftMessage->save();
            if (!$order->getGiftMessageId()) {
                $order->setGiftMessageId($giftMessage->getId())->save();
            }
        }
        return $this->generateResponseArray($order);
    }

    /**
     * Get allowed fields
     *
     * @return type
     */
    public function getAllowedFields()
    {
        return array(
//            'entity_id',
//            'increment_id',
//            'created_at',
//            'status',
//            'shipping_description',
//            'shipping_method',
//            'payment_method',
//            'base_currency_code',
//            'order_currency_code',
//            'store_currency_code',
//            'global_currency_code',
//            'store_name',
//            'remote_ip',
//            'store_to_order_rate',
//            'subtotal',
//            'subtotal_incl_tax',
//            'discount_amount',
//            'base_grand_total',
//            'grand_total',
//            'shipping_amount',
//            'shipping_tax_amount',
//            'shipping_incl_tax',
//            'tax_amount',
//            'tax_name',
//            'tax_rate',
//            'coupon_code',
//            'base_discount_amount',
//            'base_subtotal',
//            'base_shipping_amount',
//            'base_tax_amount',
//            'total_paid',
//            'base_total_paid',
//            'total_refunded',
//            'base_total_refunded',
//            'base_total_incl_tax',
//            'base_total_due',
//            'total_due',
//            'shipping_discount_amount',
//            'base_shipping_discount_amount',
//            'discount_description',
//            'customer_balance_amount',
//            'base_customer_balance_amount',
//            'gift_message',
//            'order_comments',
//            'items',
//            'shipments',
//            'gift_message_from',
//            'gift_message_to',
//            'gift_message_body',
//            'billing_address',
//            'shipping_address',
//            'weight',
//            'customer_email',
//            'customer_firstname',
//            'customer_middlename',
//            'customer_lastname',
//            'total_item_count'
        );
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'entity_id');
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array(
            'order.store',
            'order.shipping_method',
            'order.payment_method',
            'order.order_currency_code',
            'order.customer_firstname',
            'order.customer_lastname',
            'order.customer_email',
            'order.billing_address.firstname',
            'order.billing_address.lastname',
            'order.billing_address.street',
            'order.billing_address.country_id',
            'order.billing_address.city',
            'order.billing_address.postcode',
            'order.billing_address.telephone',
            'order.shipping_address.firstname',
            'order.shipping_address.lastname',
            'order.shipping_address.street',
            'order.shipping_address.country_id',
            'order.shipping_address.city',
            'order.shipping_address.postcode',
            'order.shipping_address.telephone',
            'order.items.sku',
            'order.items.qty_ordered'
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
            'order.created_at' => 'Read only.',
            'order.updated_at' => 'Read only.',
            'order.items' => 'Items are required to create an order.',
            'order.entity_id' => 'Read only.',
            'order.external_order_id' => 'External Order ID that can be set on the order object.',
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
        $schemaFields['store'] = 'string';
        $schemaFields['external_order_id'] = 'string';
        $schemaFields['payment_method'] = 'string';
        $schemaFields['gift_message'] = array(
            'gift_message_from' => 'string',
            'gift_message_to'   => 'string',
            'gift_message_body' => 'string'
        );
        // add billing/shipping address
        $addressEntity = $this->orderAddressFactory->create();
        $addressSchema = $addressEntity->schema();
        $schemaFields['billing_address'] = $addressSchema;
        $schemaFields['shipping_address'] = $addressSchema;

        $itemEntity = $this->orderItemFactory->create();
        $schemaFields['items'] = array($itemEntity->schema());

        return $schemaFields;
    }

    /**
     * Filter and hydrate a list of order items
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function filterOrderItems($order)
    {
        $items = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $item->setStoreId($order->getStoreId());
            $itemEntity = $this->orderItemFactory->create();
            $itemEntity->setEntity($item);
            $items[] = $itemEntity->read();
        }
        return $items;
    }
}
