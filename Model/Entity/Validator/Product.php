<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity\Validator;

/**
 * Entity validator product class
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Product extends \Dsync\Dsync\Model\Entity\Validator\AbstractValidator
{
    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\GroupFactory $customerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        parent::__construct($helper);
    }

    /**
     * Validate a create request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateCreate()
    {
        $data = $this->getEntityObject()->getDestinationDataArray();

        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        // check that it is a valid store
        if (isset($data['store'])) {
            try {
                $store = $this->storeManager->getStore($data['store']);
                $storeId = $store->getId();
            } catch (\Exception $e) {
                // if there is an exception here it is most likely because
                // Mage_Core_Model_Store_Exception has been thrown for trying
                // to load a store that doesn't exist.
                $error = __('Invalid store supplied: %1.', $data['store']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }

        // check that it is a valid attribute set and set the attribute set id
        if (isset($data['attribute_set'])) {
            if ($data['attribute_set'] != 'Default') {
                $attributeSet = $this
                    ->attributeSetCollectionFactory
                    ->create()
                    ->addFieldToFilter('attribute_set_name', $data['attribute_set'])
                    ->getFirstItem();
                if (!$attributeSet->getId()) {
                    $error = __('Attribute set %1 cannot be found on this system.', $data['attribute_set']);
                    throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
                }
                $data['attribute_set_id'] = $attributeSet->getId();
            } else {
                $data['attribute_set_id'] = $this
                    ->getEntityObject()
                    ->getEntityFactory()
                    ->create()
                    ->getResource()
                    ->getEntityType()
                    ->getDefaultAttributeSetId();
            }
            unset($data['attribute_set']);
        }

        $data = $this->populateMissingFields($data);

        // check associated products if it is configurable
        if ($data['type_id'] == 'configurable') {
            $this->validateConfigurableProduct($data);
        }
        // check associated products if it is grouped
        if ($data['type_id'] == 'grouped') {
            $this->validateGroupedProduct($data);
        }
        // check selection products and options if it is bundle
        if ($data['type_id'] == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->validateBundleProduct($data);
        }

        $productData = array();

        $this->validateCreateAttributes($data, $productData);
        $this->validateProductData($data, $productData, $storeId);

        // for some reason the Magento API doesn't check to see if a product already exists
        $productId = $this->getEntityObject()->getEntityFactory()->create()->getIdBySku($productData['sku']);
        if ($productId) {
            throw new \Dsync\Dsync\Exception('This product already exists. SKU: ' . $data['sku']);
        }
        return true;
    }

    /**
     * Validate all create attributes
     * @todo: condense this into one validate attributes
     *
     * @param array $data
     * @param array $productData
     */
    protected function validateCreateAttributes($data, &$productData)
    {
        foreach ($data as $field => $value) {
            $this->validateCreateAttribute($field, $value, $productData);
        }
    }

    /**
     * Validate an attribute on create
     *
     * @param string $field
     * @param mixed $value
     * @param array $productData
     * @throws \Dsync\Dsync\Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateCreateAttribute($field, $value, &$productData)
    {
        $attribute = $this->getEntityObject()->getAttribute($field);

        if (!$attribute) {
            return;
        }
        if (!$value) {
            if ($value !== 0) {
                return;
            }
        }
        $setValue = $value;

        if ($attribute->getFrontendInput() == 'multiselect') {
            $value = explode(\Dsync\Dsync\Model\Entity\Product::MULTI_DELIMITER, $value);
            $setValue = array();
        }

        // check to see if the attribute source is valid
        if ($attribute->usesSource()) {
            $source = $attribute->getSource();
            if (is_array($value)) {
                foreach ($value as $option) {
                    $attributeOption = trim($option);
                    $optionId = $this->getEntityObject()->getSourceOptionId($source, $attributeOption);
                    if (is_null($optionId)) {
                        $error = __(
                            'Invalid attribute option specified for attribute %1 (%2).',
                            $field,
                            $attributeOption
                        );
                        throw new \Dsync\Dsync\Exception(
                            $error,
                            \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                        );
                    }
                    $setValue[] = $optionId;
                }
            } else {
                $optionId = $this->getEntityObject()->getSourceOptionId($source, $value);
                if (is_null($optionId)) {
                    $error = __('Invalid attribute option specified for attribute %1 (%2).', $field, $value);
                    throw new \Dsync\Dsync\Exception(
                        $error,
                        \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                    );
                }
                $setValue = $optionId;
            }
        }
        $productData[$field] = $setValue;
    }

    /**
     * Validate an update request
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateUpdate()
    {

        $product = $this->getEntityObject()->getEntity();
        $data = $this->getEntityObject()->getDestinationDataArray();

        // check that it is a valid store
        if (isset($data['store'])) {
            try {
                $store = $this->storeManager->getStore($data['store']);
                $product = $this
                    ->getEntityObject()
                    ->getEntityFactory()
                    ->create()
                    ->setStoreId($store->getId())
                    ->load($product->getId());
            } catch (\Exception $e) {
                // if there is an exception here it is most likely because
                // Mage_Core_Model_Store_Exception has been thrown for trying
                // to load a store that doesn't exist.
                $error = __('Invalid store supplied: %1.', $data['store']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
        }

        // check that it is a valid attribute set and set the attribute set id
        if (isset($data['attribute_set'])) {
            if ($data['attribute_set'] != 'Default') {
                $attributeSet = $this
                    ->attributeSetCollectionFactory
                    ->create()
                    ->addFieldToFilter('attribute_set_name', $data['attribute_set'])
                    ->getFirstItem();
                if (!$attributeSet->getId()) {
                    $error = __('Attribute set %1 cannot be found on this system.', $data['attribute_set']);
                    throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
                }
                $data['attribute_set_id'] = $attributeSet->getId();
            } else {
                $data['attribute_set_id'] = $this
                    ->getEntityObject()
                    ->getEntityFactory()
                    ->create()
                    ->getResource()
                    ->getEntityType()
                    ->getDefaultAttributeSetId();
            }
            unset($data['attribute_set']);
        }

        // check the current type of the product or set a new one
        $type = $product->getTypeId();
        if (isset($data['type_id'])) {
            $type = $data['type_id'];
        }
        // check associated products if it is configurable
        if ($type == 'configurable') {
            $this->validateConfigurableProduct($data);
        }
        // check associated products if it is grouped
        if ($type == 'grouped') {
            $this->validateGroupedProduct($data);
        }
        // check selection products and options if it is bundle
        if ($type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->validateBundleProduct($data);
        }

        $productDataArray = array();

        $this->validateUpdateAttributes($data, $productDataArray);
        $this->validateProductData($data, $productDataArray, $product->getStoreId());

        return true;
    }

    /**
     * Validate all update attributes
     * @todo: condense this into one validate attributes
     *
     * @param array $data
     * @param array $productData
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateUpdateAttributes($data, &$productData)
    {
        foreach ($data as $field => $value) {
            $attribute = $this->getEntityObject()->getAttribute($field);

            if (!$attribute) {
                continue;
            }
            if (!$value) {
                if ($value !== 0) {
                    continue;
                }
            }
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(\Dsync\Dsync\Model\Entity\Product::MULTI_DELIMITER, $value);
                $setValue = array();
            }

            // check to see if the attribute source is valid
            if ($attribute->usesSource()) {
                $source = $attribute->getSource();
                if (is_array($value)) {
                    foreach ($value as $option) {
                        $attributeOption = trim($option);
                        $optionId = $this->getEntityObject()->getSourceOptionId($source, $attributeOption);
                        if (is_null($optionId)) {
                            $error = __(
                                'Invalid attribute option specified for attribute %1 (%2).',
                                $field,
                                $attributeOption
                            );
                            throw new \Dsync\Dsync\Exception(
                                $error,
                                \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                            );
                        }
                        $setValue[] = $optionId;
                    }
                } else {
                    $optionId = $this->getEntityObject()->getSourceOptionId($source, $value);
                    if (is_null($optionId)) {
                        $error = __(
                            'Invalid attribute option specified for attribute %1 (%2).',
                            $field,
                            $value
                        );
                        throw new \Dsync\Dsync\Exception(
                            $error,
                            \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                        );
                    }
                    $setValue = $optionId;
                }
            }
            $productData[$field] = $setValue;
        }
    }

    /**
     * Validate extra product data on the incoming request
     *
     * @param array $data
     * @param array $productData
     * @param string $storeId
     */
    protected function validateProductData($data, &$productData, $storeId)
    {
        if (isset($data['websites'])) {
            if ($data['websites']) {
                $websiteIds = $this->getWebsiteIds($data['websites']);
                if (!empty($websiteIds)) {
                    $productData['website_ids'] = $websiteIds;
                }
            }
        }

        if (isset($data['categories'])) {
            if ($categories = $data['categories']) {
                $categoryIds = $this->getCategoryIds($categories, $storeId);
                if (!empty($categoryIds)) {
                    $productData['category_ids'] = $categoryIds;
                }
            }
        }

        if (isset($data['group_price'])) {
            $productData['group_price'] = $this->checkPrices($data['group_price'], false);
        }

        if (isset($data['tier_price'])) {
            $productData['tier_price'] = $this->checkPrices($data['tier_price'], true);
        }
    }

    /**
     * Get website ids from an array of website codes
     *
     * @param array $websites
     * @return array
     * @throws \Dsync\Dsync\Exception
     */
    protected function getWebsiteIds($websites)
    {
        if (!is_array($websites)) {
            $websites = explode(',', $websites);
        }
        $websiteIds = array();
        foreach ($websites as $website) {
            if (!$website) {
                continue;
            }
            try {
                $website = $this->storeManager->getWebsite(trim($website));
                $website->getId();
            } catch (\Exception $e) {
                // if there is an exception here it is most likely because
                // an exception has been thrown for trying
                // to load a website that doesn't exist.
                $error = __('Invalid website supplied: %1.', $website);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
            $websiteIds[] = $website->getId();
        }
        return $websiteIds;
    }

    /**
     * Check array of group or tiered prices
     *
     * @param array $prices
     * @return array
     * @throws \Dsync\Dsync\Exception
     */
    protected function checkPrices($prices, $tier = false)
    {
        if (!is_array($prices)) {
            $error = __('Prices must be supplied as an array.');
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
        $priceArray = array();
        foreach ($prices as $price) {
            $this->validatePriceData($price, $tier);

            // Check to see if the website is valid
            try {
                $price['website_id'] = $this->storeManager->getWebsite($price['website_id'])->getId();
            } catch (\Exception $e) {
                // if there is an exception here it is most likely because
                // an exception has been thrown for trying
                // to load a website that doesn't exist.
                $error = __('Invalid website code supplied: %1.', $price['website_id']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }

            // Check to see if the group is valid
            $group = $this->customerGroupFactory->create()->load($price['cust_group'], 'customer_group_code');
            // for some reason the primary key for not logged in is 0 on the customer group table
            // so checking to see if a code is loaded
            if (!$group->getCode()) {
                $error = __('Unable to find the provided customer group: %1.', $price['cust_group']);
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            } else {
                $price['cust_group'] = $group->getId();
            }
            $priceArray[] = $price;
        }
        return $priceArray;
    }

    /**
     * Validate configurable product data
     *
     * @param type $data
     * @throws \Dsync\Dsync\Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateConfigurableProduct($data)
    {
        if (isset($data['associated_products']) && isset($data['configurable_attributes'])) {
            $configurableAttributes = array();
            $productIds = array();
            if ($configurableAttributes = $data['configurable_attributes']) {
                if (!is_array($configurableAttributes)) {
                    $configurableAttributes = explode(',', $configurableAttributes);
                }
            }
            if ($associatedProducts = $data['associated_products']) {
                if (!is_array($associatedProducts)) {
                    $associatedProducts = explode(',', $associatedProducts);
                }
                foreach ($associatedProducts as $sku) {
                    if (!$sku) {
                        continue;
                    }
                    $productIds[] = $this->getEntityObject()->getEntityFactory()->create()->getIdBySku($sku);
                }
            }
            foreach ($productIds as $productId) {
                try {
                    $product = $this->getEntityObject()->getEntityFactory()->create()->load($productId);
                    foreach ($configurableAttributes as $configurableAttribute) {
                        if (!$configurableAttribute) {
                            continue;
                        }
                        if (!$product->getData($configurableAttribute)) {
                            $error = __(
                                'Required attribute %1 cannot be found on product sku: %2.',
                                $configurableAttribute,
                                $product->getSku()
                            );
                            throw new \Dsync\Dsync\Exception(
                                $error,
                                \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                            );
                        }
                    }
                } catch (\Exception $e) {
                    throw new \Dsync\Dsync\Exception(
                        $e->getMessage(),
                        \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                    );
                }
            }
        }
    }

    /**
     * Validate grouped product data
     *
     * @param array $data
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     */
    public function validateGroupedProduct($data)
    {
        if (isset($data['grouped_products'])) {
            try {
                if ($groupedProducts = $data['grouped_products']) {
                    foreach ($groupedProducts as $groupedProduct) {
                        if (!$groupedProduct) {
                            continue;
                        }
                        if (!isset($groupedProduct['sku'])) {
                            continue;
                        }
                        $productId = $this
                            ->getEntityObject()
                            ->getEntityFactory()
                            ->create()
                            ->getIdBySku($groupedProduct['sku']);
                        $product = $this
                            ->getEntityObject()
                            ->getEntityFactory()
                            ->create()
                            ->load($productId);
                        if (!$product->getId()) {
                            throw new \Exception('Invalid sku supplied for grouped product: ' . $groupedProduct['sku']);
                        }
                    }
                }
            } catch (\Exception $e) {
                throw new \Dsync\Dsync\Exception(
                    $e->getMessage(),
                    \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                );
            }
        }
    }

    /**
     * Validate bundle product data for create or update
     *
     * @param array $data
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateBundleProduct($data)
    {
        if (isset($data['bundle_options'])) {
            try {
                if ($bundleOptions = $data['bundle_options']) {
                    foreach ($bundleOptions as $bundleOption) {
                        if (!$bundleOption) {
                            continue;
                        }
                        if (!isset($bundleOption['title'])) {
                            $error = 'Missing "title" for bundle option. ';
                            $error .= 'A title is required to create/update a bundle option.';
                            throw new \Exception($error);
                        }
                        if (isset($bundleOption['selections'])) {
                            foreach ($bundleOption['selections'] as $selection) {
                                if (isset($selection['sku'])) {
                                    $productId = $this
                                        ->getEntityObject()
                                        ->getEntityFactory()
                                        ->create()
                                        ->getIdBySku($selection['sku']);
                                    $productSelection = $this
                                        ->getEntityObject()
                                        ->getEntityFactory()
                                        ->create()
                                        ->load($productId);
                                    if (!$productSelection->getId()) {
                                        throw new \Exception(
                                            'Invalid sku supplied for product selection: ' . $selection['sku']
                                        );
                                    }
                                } else {
                                    $error = 'Missing "sku" for bundle selection. ';
                                    $error .= 'A sku is required to create/update a bundle selection.';
                                    throw new \Exception($error);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                throw new \Dsync\Dsync\Exception(
                    $e->getMessage(),
                    \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST
                );
            }
        }
    }

    /**
     * Validate extra price data on incoming requests
     *
     * @param array $price
     * @param boolean $tier
     * @throws \Dsync\Dsync\Exception
     */
    protected function validatePriceData($price, $tier)
    {
        $error = false;
        if (!isset($price['website_id'])) {
            $error = __('A website needs to be supplied for pricing.');
        }
        if (!isset($price['price'])) {
            $error = __('A price needs to be supplied for pricing.');
        }
        if (!isset($price['cust_group'])) {
            $error = __('A customer group needs to be supplied for pricing.');
        }

        if ($tier) {
            if (!isset($price['price_qty'])) {
                $error = __('A price quantity needs to be supplied for tiered pricing.');
            }
        }
        if ($error) {
            throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get category ids from an array of category names
     *
     * @param array $categories
     * @return array
     * @throws \Dsync\Dsync\Exception
     */
    protected function getCategoryIds($categories, $storeId)
    {
        // set the current store to get categories
        $currentCode = $this->storeManager->getStore($storeId)->getCode();
        $this->storeManager->setCurrentStore($currentCode);

        if (!is_array($categories)) {
            $categories = explode(',', $categories);
        }
        $categoryIds = array();
        foreach ($categories as $categoryName) {
            if (!$categoryName) {
                continue;
            }
            $categoryName = trim($categoryName);
            if (is_numeric($categoryName)) {
                $category = $this->categoryFactory->create()->setStoreId($storeId)->load($categoryName);
            } else {
                $categoryCollection = $this->categoryFactory->create()->setStoreId($storeId)->getCollection()
                    ->addFieldToFilter('name', $categoryName);
                if ($categoryCollection->getSize() > 1) {
                    $error = __(
                        'More than one category with name: %1 found in Magento.  Please use category id.',
                        $categoryName
                    );
                    throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
                }
                $category = $categoryCollection->getFirstItem();
            }
            if (!$category->getId()) {
                $error = __(
                    'Category could not be found: %1',
                    $categoryName
                );
                throw new \Dsync\Dsync\Exception($error, \Dsync\Dsync\Model\Api\Response\Code::HTTP_BAD_REQUEST);
            }
            $categoryIds[] = $category->getId();
        }
        // set the current store back to admin afterwards
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        return $categoryIds;
    }

    /**
     * Populate missing fields
     *
     * @param array $data
     * @return array
     */
    public function populateMissingFields($data)
    {
        // here we are adding extra data to validate the request
        if (!array_key_exists('type_id', $data)) {
            $data['type_id'] = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
        }

        if (!array_key_exists('attribute_set_id', $data)) {
            $data['attribute_set_id'] = $this
                ->getEntityObject()
                ->getEntityFactory()
                ->create()
                ->getResource()
                ->getEntityType()
                ->getDefaultAttributeSetId();
        }

        if (!array_key_exists('description', $data)) {
            $data['description'] = $data['name'];
        }

        if (!array_key_exists('short_description', $data)) {
            $data['short_description'] = $data['name'];
        }

        $this->populateStaticStrings($data);
        $this->populateStaticNumbers($data);

        return $data;
    }

    /**
     * Populate missing static numbers on a create request
     *
     * @param array $data
     */
    protected function populateStaticNumbers(&$data)
    {
        if (!array_key_exists('weight', $data)) {
            $data['weight'] = 0;
        }

        if (!array_key_exists('price', $data)) {
            $data['price'] = 0;
        }
    }

    /**
     * Populate missing static strings on a create request
     *
     * @param array $data
     */
    protected function populateStaticStrings(&$data)
    {
        if (!array_key_exists('tax_class_id', $data)) {
            $data['tax_class_id'] = 'Taxable Goods';
        }

        if (!array_key_exists('visibility', $data)) {
            $data['visibility'] = 'Catalog, Search';
        }

        if (!array_key_exists('status', $data)) {
            $data['status'] = 'Disabled';
        }
    }
}
