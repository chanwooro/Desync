<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator order class
 */
class Order extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory
     */
    protected $orderAddressFactory;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Payment\Model\Config $paymentConfig
     */
    protected $paymentConfig;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dsync\Dsync\Model\Entity\Order\AddressFactory $orderAddressFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        $this->storeManager = $storeManager;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->productFactory = $productFactory;
        $this->paymentConfig = $paymentConfig;
        parent::__construct($helper);
    }

    /**
     * Validate a create request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateCreate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();

        // check that it is a valid store
        if (isset($data['store'])) {
            try {
                $store = $this->storeManager->getStore($data['store']);
                $store->getId();
            } catch (\Exception $e) {
                // if there is an exception here it is most likely because
                // Mage_Core_Model_Store_Exception has been thrown for trying
                // to load a store that doesn't exist.
                $error = __('Invalid store supplied: %1.', $data['store']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }

        // check billing address
        if (isset($data['billing_address'])) {
            if (is_array($data['billing_address'])) {
                $addressEntity = $this->orderAddressFactory->create()
                    ->setDestinationDataArray($data['billing_address']);
                $addressEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
            } else {
                throw new \Dsync\Dsync\Exception('Billing address is not an array');
            }
        }
        
        // check shipping address
        if (isset($data['shipping_address'])) {
            if (is_array($data['shipping_address'])) {
                $addressEntity = $this->orderAddressFactory->create()
                    ->setDestinationDataArray($data['shipping_address']);
                $addressEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::CREATE);
            } else {
                throw new \Dsync\Dsync\Exception('Shipping address is not an array');
            }
        }

        // check payment methods
        $paymentMethodsInfo = $this->paymentConfig->getMethodsInfo();
        $this->getHelper()->log(json_encode($paymentMethodsInfo));

        if (!array_key_exists($data['payment_method'], $paymentMethodsInfo)) {
            throw new \Dsync\Dsync\Exception(
                'Payment method not available or active: ' . $data['payment_method']
            );
        }

        // check items
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $productId = $this->productFactory->create()->getIdBySku($item['sku']);
                if (!$productId) {
                    throw new \Dsync\Dsync\Exception(
                        'Product not found: ' . $item['sku']
                    );
                }
            }
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
        $order = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();

        if (isset($data['billing_address'])) {
            if (is_array($data['billing_address'])) {
                $addressEntity = $this->orderAddressFactory->create()
                    ->setEntity($order->getBillingAddress())
                    ->setDestinationDataArray($data['billing_address']);
                $addressEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::UPDATE);
            } else {
                throw new \Dsync\Dsync\Exception('Billing address is not an array');
            }
        }
        if (isset($data['shipping_address'])) {
            if (is_array($data['shipping_address'])) {
                $addressEntity = $this->orderAddressFactory->create()
                    ->setEntity($order->getShippingAddress())
                    ->setDestinationDataArray($data['shipping_address']);
                $addressEntity->validate(\Dsync\Dsync\Model\Api\Request\Method::UPDATE);
            } else {
                throw new \Dsync\Dsync\Exception('Shipping address is not an array');
            }
        }
        return true;
    }
}
