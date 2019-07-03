<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Entity;

/**
 * Entity product class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Product extends \Dsync\Dsync\Model\Entity\AbstractEntity
{
    const MULTI_DELIMITER = ',';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Dsync\Dsync\Model\Entity\IventoryFactory $inventoryFactory
     */
    protected $inventoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManager $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\GroupFactory $customerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var array
     */
    protected $attributeSetsById;

    /**
     * @var array
     */
    protected $attributeSetsByName;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory $optionCollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory
     */
    protected $configurableFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     * @param \Dsync\Dsync\Model\Entity\Validator\Product $validatorModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Dsync\Dsync\Model\Entity\InventoryFactory $inventoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel,
        \Dsync\Dsync\Model\Entity\Validator\Product $validatorModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Dsync\Dsync\Model\Entity\InventoryFactory $inventoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory $optionCollectionFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
    ) {
        $this->entityFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->validatorModel = $validatorModel;
        $this->storeManager = $storeManager;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->inventoryFactory = $inventoryFactory;
        $this->itemFactory = $itemFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->configurableFactory = $configurableFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        parent::__construct($helper, $entityTypeModel);
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Dsync\Dsync\Model\System\Config\Source\Entity\Type::PRODUCT;
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
     * Create a new entity
     *
     * @throws \Dsync\Dsync\Exception
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function processCreate()
    {

        $data = $this->getDestinationDataArray();

        $productData = array();

        // set the store id
        if (isset($data['store'])) {
            $store = $this->storeManager->getStore($data['store']);
            $storeId = $store->getId();
            unset($data['store']);
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        // set the attribute set id
        if (isset($data['attribute_set'])) {
            if ($data['attribute_set'] != 'Default') {
                $attributeSet = $this
                    ->attributeSetCollectionFactory
                    ->create()
                    ->addFieldToFilter('attribute_set_name', $data['attribute_set'])
                    ->getFirstItem();
                $data['attribute_set_id'] = $attributeSet->getId();
            } else {
                $data['attribute_set_id'] = $this
                    ->getEntityFactory()
                    ->create()
                    ->getResource()
                    ->getEntityType()
                    ->getDefaultAttributeSetId();
            }
            unset($data['attribute_set']);
        }

        // remove the url_key if it is set and empty
        if (isset($data['url_key']) && !$data['url_key']) {
            unset($data['url_key']);
        }

        // remove the url_path if it is set and empty
        if (isset($data['url_path']) && !$data['url_path']) {
            unset($data['url_path']);
        }

        $validator = $this->getValidatorModel();

        $data = $validator->generateMissingFields($this, $data);

        $this->createAttributeData($data, $productData);

        $this->createProductData($data, $productData, $storeId);

        $type = $productData['type_id'];
        $set = $productData['attribute_set_id'];
        $sku = $productData['sku'];

        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->entityFactory->create()
            ->setStoreId($storeId)
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        // add associated products if it is configurable
        if ($type == 'configurable') {
            $this->generateConfigurableProductData($data, $product);
        }

        // add associated products if it is grouped
        if ($type == 'grouped') {
            $this->generateGroupedProductData($data, $product);
        }

        // add bundle options and products if it is bundle
        if ($type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->generateBundleProductData($data, $product);
        }

        foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $product->setData($mediaAttrCode, 'no_selection');
        }

        foreach ($productData as $field => $value) {
            //set all the filtered data
            $product->setData($field, $value);
        }

        $this->createAttributesByStore($product, $productData);

        $this->getRegistry()->set(
            \Dsync\Dsync\Model\System\Config\Source\Entity\Type::INVENTORY,
            \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD
        );

        try {
            $product->setStockData([]);
            $product->validate();
            $product->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($product);
    }

    /**
     * Filter attributes only by store when creating
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productData
     */
    protected function createAttributesByStore(&$product, $productData)
    {
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            //Unset data if object attribute has no value in current store
            if (\Magento\Store\Model\Store::DEFAULT_STORE_ID !== (int)$product->getStoreId()
                && !$product->getExistsStoreValueFlag($attribute->getAttributeCode())
                && !$attribute->isScopeGlobal()
            ) {
                if ($product->getData($attribute->getAttributeCode())) {
                    $product->setData($attribute->getAttributeCode(), false);
                }
            }
            if (isset($productData[$attribute->getAttributeCode()])) {
                $product->setData(
                    $attribute->getAttributeCode(),
                    $productData[$attribute->getAttributeCode()]
                );
            }
        }
    }


    /**
     * Filter attributes data for setting on the product when creating
     * Converts source type to actual value needed
     *
     * @param array $data
     * @param array $productData
     */
    protected function createAttributeData($data, &$productData)
    {
        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);

            if (!$attribute) {
                continue;
            }
            if (!$value) {
                if ($value !== 0) {
                    $productData[$field] = $value;
                    continue;
                }
            }
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $setValue = array();
            }

            // check to see if the attribute source is valid
            if ($attribute->usesSource()) {
                $source = $attribute->getSource();
                if (is_array($value)) {
                    foreach ($value as $option) {
                        $attributeOption = trim($option);
                        $optionId = $this->getSourceOptionId($source, $attributeOption);
                        $setValue[] = $optionId;
                    }
                } else {
                    $optionId = $this->getSourceOptionId($source, $value);
                    $setValue = $optionId;
                }
            }
            $productData[$field] = $setValue;
        }
    }

    /**
     * Hydrate extra product data when creating
     *
     * @param array $data
     * @param array $productData
     */
    protected function createProductData($data, &$productData, $storeId)
    {
        // add websites if they are set
        if (isset($data['websites'])) {
            if ($websites = $data['websites']) {
                $websiteIds = $this->getWebsiteIds($websites);
                if (!empty($websiteIds)) {
                    $productData['website_ids'] = $websiteIds;
                }
            }
        }
        // add categories if they are set
        if (isset($data['categories'])) {
            if ($categories = $data['categories']) {
                $categoryIds = $this->getCategoryIds($categories, $storeId);
                if (!empty($categoryIds)) {
                    $productData['category_ids'] = $categoryIds;
                }
            }
        }

        if (isset($data['group_price'])) {
            $productData['group_price'] = $this->convertPriceKeys($data['group_price']);
        }

        if (isset($data['tier_price'])) {
            $productData['tier_price'] = $this->convertPriceKeys($data['tier_price']);
        }
    }

    /**
     * Process an entity read
     *
     * @return array
     */
    protected function processRead()
    {
        $productData = $this->getEntity();

        // reload the product to get all of the data
        $product = $this
            ->entityFactory
            ->create()
            ->setStoreId($productData->getStoreId())
            ->load($productData->getId());

        $groupPrice = $this->filterPriceKeys($product->getData('group_price'));
        if (!empty($groupPrice)) {
            $product->setData('group_price', $groupPrice);
        }
        $tierPrice = $this->filterPriceKeys($product->getData('tier_price'));
        if (!empty($tierPrice)) {
            $product->setData('tier_price', $tierPrice);
        }

        $product->setData('product_type_name', $product->getTypeId());

        $extraFields = array(
            'attribute_set' => $this->getAttributeSetName($product),
            'store' => $this->storeManager->getStore($product->getStoreId())->getCode()
        );

        $productArray = array_merge($product->getData(), $extraFields);

        // create a blank stock item model and apply the stock data to it
        // if it is available
        if ($product->getStockData()) {
            $stockItemModel = $this->itemFactory->create();
            $stockData = $product->getStockData();
            $stockData['product_id'] = $product->getId();
            $inventoryItem = $stockItemModel->setData($stockData);
            $inventoryEntity = $this->inventoryFactory->create();
            $inventoryEntity->setEntity($inventoryItem);
            $productArray['inventory'] = $inventoryEntity->read();
        }

        // read extra product data
        $this->readProductData($product, $productArray);

        // product attributes
        $this->readAttributeData($product, $productArray);
        return $productArray;
    }

    /**
     * Filter attributes data when reading an entity
     * Will output the source option as text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productArray
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function readAttributeData($product, &$productArray)
    {
        $productAttributes = array();

        // product attributes
        foreach ($product->getData() as $field => $value) {
            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }
            if (is_array($value)) {
                continue;
            } elseif ($attribute->usesSource()) {
                if (!$value) {
                    if (!$value === 0) {
                        continue;
                    }
                }
                $option = $attribute->getSource()->getOptionText($value);
                if ($value && empty($option) && $option != '0') {
                    continue;
                }
                if (is_array($option)) {
                    $value = join(self::MULTI_DELIMITER, $option);
                } else {
                    $value = $option;
                }
                unset($option);
            }
            $productArray[$field] = $value;
            $productAttributes[] = array(
                'type' => $field,
                'value' => $value
            );
        }
        $productArray['product_attributes'] = $productAttributes;
    }

    /**
     * Read extra product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productArray
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function readProductData($product, &$productArray)
    {
        // categories
        $categories = array();
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = $this->categoryFactory->create()->setStoreId($product->getStoreId())->load($categoryId);
            if ($category->getName()) {
                $categories[] = $category->getName();
            }
        }
        if (!empty($categories)) {
            $productArray['categories'] = $categories;
        }

        // websites
        $websites = array();
        foreach ($product->getWebsiteIds() as $websiteId) {
            $websites[] = $this->storeManager->getWebsite($websiteId)->getCode();
        }
        if (!empty($websites)) {
            $productArray['websites'] = $websites;
        }
        // add associated products if it is configurable
        if ($product->getTypeId() == 'configurable') {
            $associatedProducts = [];

            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($usedProducts as $usedProduct) {
                $associatedProducts[] = $usedProduct->getSku();
            }
            // add configurable attribute codes
            $configurableAttributeCodes = [];

            $configurableAttributes = $product->getTypeInstance()->getConfigurableAttributes($product);
            foreach ($configurableAttributes as $configurableAttribute) {
                $configurableAttributeCodes[] = $configurableAttribute->getProductAttribute()->getAttributeCode();
            }
            $productArray['associated_products'] = $associatedProducts;
            $productArray['configurable_attributes'] = $configurableAttributeCodes;
        }

        if ($product->getTypeId() == 'grouped') {
            $groupedProducts = array();
            $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach ($associatedProducts as $associatedProduct) {
                $groupedProducts[] = $associatedProduct->getData();
            }
            $productArray['grouped_products'] = $groupedProducts;
        }
        // add bundle options and selections if it is a bundle
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $productArray['bundle_options'] = $this->readBundleProductData($product);
        }

        // add parent skus if it is simple
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $parentSkus = array();
            $parentIds = $this
                ->configurableFactory
                ->create()
                ->getParentIdsByChild($product->getId());
            foreach ($parentIds as $parentId) {
                $parentProduct = $this->entityFactory->create()->load($parentId);
                $parentSkus[] = $parentProduct->getSku();
            }
            $productArray['parent_skus'] = $parentSkus;
        }
    }

    /**
     * Read bundle product data from the product entity
     *
     * @param  \Magento\Catalog\Model\Product $product
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function readBundleProductData($product)
    {
        $bundleOptions = array();
        $typeInstance = $product->getTypeInstance();
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );
        foreach ($optionCollection as $option) {
            $optionArray = $option->getData();
            $optionArray['title'] = $option->getTitle() ? $option->getTitle() : $option->getDefaultTitle();
            $selectionArray = array();
            foreach ($selectionCollection as $selection) {
                if ($selection->getOptionId() == $option->getOptionId()) {
                    $selectionData = $selection->getData();
                    if (isset($selectionData['stock_item'])) {
                        unset($selectionData['stock_item']);
                    }
                    $selectionArray[] = $selectionData;
                }
            }
            $optionArray['selections'] = $selectionArray;
            $bundleOptions[] = $optionArray;
        }
        return $bundleOptions;
    }

    /**
     * Update an entity
     *
     * @throws \Exception
     */
    protected function processUpdate()
    {
        $product = $this->getEntity();
        $data = $this->getDestinationDataArray();

        // reload the product to have all entity information available
        $product = $this->entityFactory->create()->load($product->getId());

        // if the store is set load the product by the store
        if (isset($data['store'])) {
            $store = $this->storeManager->getStore($data['store']);
            $product = $this->entityFactory->create()->setStoreId($store->getId())->load($product->getId());
        }

        // set the attribute set id
        if (isset($data['attribute_set'])) {
            if ($data['attribute_set'] != 'Default') {
                $attributeSet = $this
                    ->attributeSetCollectionFactory
                    ->create()
                    ->addFieldToFilter('attribute_set_name', $data['attribute_set'])
                    ->getFirstItem();
                $data['attribute_set_id'] = $attributeSet->getId();
            } else {
                $data['attribute_set_id'] = $this
                    ->getEntityFactory()
                    ->create()
                    ->getResource()
                    ->getEntityType()
                    ->getDefaultAttributeSetId();
            }
            unset($data['attribute_set']);
        }

        // remove the url_key if it is set and empty
        if (isset($data['url_key']) && !$data['url_key']) {
            unset($data['url_key']);
        }

        // remove the url_path if it is set and empty
        if (isset($data['url_path']) && !$data['url_path']) {
            unset($data['url_path']);
        }

        // check the current type of the product or set a new one
        $type = $product->getTypeId();
        if (isset($data['type_id'])) {
            $type = $data['type_id'];
        }
        // add associated products if it is configurable
        if ($type == 'configurable') {
            $this->generateConfigurableProductData($data, $product);
        }
        // add associated products if it is grouped
        if ($type == 'grouped') {
            $this->generateGroupedProductData($data, $product);
        }

        // add bundle options and products if it is bundle
        if ($type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $this->generateBundleProductData($data, $product);
        }

        // update product data
        $this->updateProductData($data, $product->getStoreId());

        // update attribute data
        $this->updateAttributeData($data, $product);

        $this->getRegistry()->set(
            \Dsync\Dsync\Model\System\Config\Source\Entity\Type::INVENTORY,
            \Dsync\Dsync\Model\Registry::BLOCK_REQUEST_METHOD
        );

        try {
            $product->validate();
            $product->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($product);
    }

    /**
     * Update attribute data on an entity
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function updateAttributeData($data, &$product)
    {
        foreach ($data as $field => $value) {
            $attribute = $this->getAttribute($field);

            if (!$attribute) {
                $product->setData($field, $value);
                continue;
            }
            if (!$value) {
                if ($value !== 0) {
                    $product->setData($field, $value);
                    continue;
                }
            }
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $setValue = array();
            }

            // check to see if the attribute source is valid
            if ($attribute->usesSource()) {
                $source = $attribute->getSource();
                if (is_array($value)) {
                    foreach ($value as $option) {
                        $attributeOption = trim($option);
                        $optionId = $this->getSourceOptionId($source, $attributeOption);
                        $setValue[] = $optionId;
                    }
                } else {
                    $optionId = $this->getSourceOptionId($source, $value);
                    $setValue = $optionId;
                }
            }
            $product->setData($field, $setValue);
        }
    }

    /**
     * Update extra product data
     *
     * @param array $data
     * @param string $storeId
     */
    protected function updateProductData(&$data, $storeId)
    {
        // add websites if they are set
        if (isset($data['websites'])) {
            if ($websites = $data['websites']) {
                $websiteIds = $this->getWebsiteIds($websites);
                if (!empty($websiteIds)) {
                    $data['website_ids'] = $websiteIds;
                }
            }
        }
        // add categories if they are set
        if (isset($data['categories'])) {
            if ($categories = $data['categories']) {
                $categoryIds = $this->getCategoryIds($categories, $storeId);
                if (!empty($categoryIds)) {
                    $data['category_ids'] = $categoryIds;
                }
            }
        }

        // add group prices if it is set
        if (isset($data['group_price'])) {
            $data['group_price'] = $this->convertPriceKeys($data['group_price']);
        }

        // add tier prices if it is set
        if (isset($data['tier_price'])) {
            $data['tier_price'] = $this->convertPriceKeys($data['tier_price']);
        }
    }

    /**
     * Generate configurable product data
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateConfigurableProductData(&$data, &$product)
    {
        if (isset($data['associated_products'])) {
            if ($associatedProducts = $data['associated_products']) {
                $productIds = [];
                if (!is_array($associatedProducts)) {
                    $associatedProducts = explode(',', $associatedProducts);
                }
                foreach ($associatedProducts as $sku) {
                    if (!$sku) {
                        continue;
                    }
                    $productIds[] = $this->getEntityFactory()->create()->getIdBySku($sku);
                }
                if (!empty($productIds)) {
                    $product->setAssociatedProductIds($productIds);
                }
            }
        }
        if (isset($data['configurable_attributes'])) {
            if ($configurableAttributes = $data['configurable_attributes']) {
                $configurableAttributeData = [];
                if (!is_array($configurableAttributes)) {
                    $configurableAttributes = explode(',', $configurableAttributes);
                }
                foreach ($configurableAttributes as $configurableAttribute) {
                    if (!$configurableAttribute) {
                        continue;
                    }
                    $attribute = $this->getAttribute($configurableAttribute);

                    if (!$attribute) {
                        continue;
                    }
                    $configurableAttributeData[$attribute->getId()] = array(
                        'attribute_id' => $attribute->getId(),
                        'attribute_code' => $configurableAttribute
                    );
                }
                if (!empty($configurableAttributeData)) {
                    $product->setConfigurableAttributesData($configurableAttributeData);
                }
            }
        }
    }

    /**
     * Generate grouped product data to be saved on the entity
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function generateGroupedProductData(&$data, &$product)
    {
        if (isset($data['grouped_products'])) {
            $productIds = array();
            if ($groupedProducts = $data['grouped_products']) {
                foreach ($groupedProducts as $groupedProduct) {
                    if (!$groupedProduct) {
                        continue;
                    }
                    if (!isset($groupedProduct['sku'])) {
                        continue;
                    }
                    $productIds[$this->getEntityFactory()->create()->getIdBySku($groupedProduct['sku'])] = array(
                        'qty' => isset($groupedProduct['qty']) ? $groupedProduct['qty'] : 0,
                        'position' => isset($groupedProduct['position']) ? $groupedProduct['position'] : 0
                    );
                }
            }
            $product->setGroupedLinkData($productIds);
        }
    }

    /**
     * Generate bundle product data to be saved on the entity
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function generateBundleProductData(&$data, &$product)
    {
        // limit bundle product options/selections updating to the admin store
        // as creating bundle data in another type of store will remove it
        // from the admin store and not be properly accessable when editing
        // a product
        $store = $this->storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE);
        if ($store->getId() != $product->getStoreId()) {
            return;
        }
        if (isset($data['bundle_options'])) {
            $bundleOptionsArray = array();
            $bundleSelectionsArray = array();
            if ($bundleOptions = $data['bundle_options']) {
                foreach ($bundleOptions as $bundleKey => $bundleOption) {
                    if (!$bundleOption) {
                        continue;
                    }
                    if (isset($bundleOption['selections'])) {
                        $selectionsArray = array();
                        if (!empty($bundleOption['selections'])) {
                            foreach ($bundleOption['selections'] as $key => $value) {
                                if (isset($value['sku'])) {
                                    $productId = $this->getEntityFactory()->create()->getIdBySku($value['sku']);
                                    $value['product_id'] = $productId;
                                    unset($value['sku']);
                                }
                                $selectionsArray[$key] = $value;
                            }
                            $bundleSelectionsArray[$bundleKey] = $selectionsArray;
                        }
                        unset($bundleOption['selections']);
                    }
                    $bundleOptionsArray[$bundleKey] = $bundleOption;
                }
            }
            $product->setBundleOptionsData($bundleOptionsArray);
            $product->setBundleSelectionsData($bundleSelectionsArray);
            if ($product->getId()) {
                $optionCollection = $this
                    ->optionCollectionFactory
                    ->create()
                    ->addFieldToFilter('parent_id', $product->getId());
                foreach ($optionCollection as $option) {
                    $option->delete();
                }
            }
        }
    }

    /**
     * Delete an entity
     *
     * @throws \Exception
     */
    protected function processDelete()
    {
        $product = $this->getEntity();
        try {
            $product->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->generateResponseArray($product);
    }

    /**
     * Creates schema based on attributes for a product
     *
     * @return array
     */
    protected function processSchema()
    {
        $schemaArray = array();
        $entityModel = $this->entityFactory->create();
        $attributes = $entityModel->getAttributes();

        foreach ($attributes as $attribute) {
            $type = $attribute->getBackendType();
            if ($attribute->usesSource()) {
                $type = 'text';
            }
            $schemaArray[$attribute->getName()] = $type;
        }
        $productAttributes  = $this->attributeCollectionFactory->create()->load();
        foreach ($productAttributes as $productAttribute) {
            if (!array_key_exists($productAttribute->getName(), $schemaArray)) {
                $type = $productAttribute->getBackendType();
                if ($productAttribute->usesSource()) {
                    $type = 'text';
                }
                $schemaArray[$productAttribute->getName()] = $type;
            }
        }
        return $schemaArray;
    }

    /**
     * Get excluded fields
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return array(
            'allow_open_amount',
            'allow_open_amount',
            'open_amount_min',
            'image',
            'is_recurring',
            'gallery',
            'media_gallery',
            'small_image',
            'image_url',
            'is_saleable',
            'total_reviews_count',
            'url',
            'buy_now_url',
            'has_custom_options',
            'is_in_stock',
            'regular_price_with_tax',
            'regular_price_without_tax',
            'final_price_with_tax',
            'final_price_without_tax',
            'recurring_profile',
            'thumbnail',
            'product_id',
            'entity_type_id',
            'store_id',
            'store_ids',
            'website_ids',
            '_cache_editable_attributes',
            '_cache_instance_product_set_attributes',
            'attribute_set_id',
            'parent_id',
            'stock_item',
            'matched_rules',
            'media_attributes',
            '_edit_mode',
            'grouped_link_data',
            'cross_sell_link_data',
            'up_sell_link_data',
            'related_link_data',
            'original_id',
            'category_ids',
            'affected_category_ids',
            'stock_data',
            'old_id',
            '_cache_instance_products',
            '_cache_instance_configurable_attributes',
            '_cache_instance_used_attributes',
            '_cache_instance_used_product_attributes',
            '_cache_instance_used_product_attribute_ids',
            'configurable_attributes_data',
            'configurable_products_data',
            'bundle_selections_data',
            'bundle_options_data',
            'downloadable_data'
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
            'has_options',
            'required_options',
            'group_price_changed',
            'tier_price_changed',
            'use_config_gift_message_available',
            'is_salable',
            'product_has_weight'
        );
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return array('product.sku', 'product.name', 'product.type_id', 'product.attribute_set');
    }

    /**
     * Get ignored fields
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        return array('created_at', 'updated_at', 'inventory', 'entity_id');
    }

    /**
     * Descriptions for fields when schema is generated
     *
     * @return array
     */
    public function getFieldDescriptions()
    {
        $configurableAttributes = 'Required for configurable product support.  ';
        $configurableAttributes .= 'Attributes need to be mapped for the simple product as well.';

        $bundleOptions = 'Required for bundle product support. ';
        $bundleOptions .= 'Title/sku are required for options/selections respectively. ';
        $bundleOptions .= 'Limited to the admin store (global only).';

        return array(
            'product.weight' => 'Weight is required for certain product types.',
            'product.price' => 'Price is required for simple products.',
            'product.tax_class_id' => 'Tax class is is required for certain product types.',
            'product.inventory' => 'Inventory is read-only on the product entity.',
            'product.associated_products' => 'Required for configurable product support.',
            'product.configurable_attributes' => $configurableAttributes,
            'product.grouped_products' => 'Required for grouped product support.',
            'product.bundle_options' => $bundleOptions,
            'product.price_view' => 'Required for bundled products.',
            'product.price_type' => 'Required for bundled products.',
            'product.created_at' => 'Read only.',
            'product.updated_at' => 'Read only.',
            'product.entity_id' => 'Read only.',
            'product.product_attributes' => 'Read only.',
            'product.parent_skus' => 'Read only.'
        );
    }

    /**
     * Get a list of text fields that are multiple
     * when generating the schema i.e a simple array
     *
     * @return array
     */
    public function getMultipleFields()
    {
        return array(
            'product.categories',
            'product.websites',
            'product.associated_products',
            'product.configurable_attributes',
            'product.parent_skus'
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
        $schemaFields = array(
            'created_at' => 'date',
            'updated_at' => 'date',
            'store' => 'string',
            'attribute_set' => 'string',
            'websites' => 'string',
            'categories' => 'string',
            'associated_products' => 'string',
            'configurable_attributes' => 'string',
            'parent_skus' => 'string',
            'grouped_products' => array(
                array(
                    'sku' => 'string',
                    'qty' => 'integer',
                    'position' => 'string'
                )
            ),
            'bundle_options' => array(
                array(
                    'title' => 'string',
                    'delete' => 'string',
                    'type' => 'string',
                    'required' => 'string',
                    'position' => 'string',
                    'selections' => array(
                        array(
                            'delete' => 'string',
                            'selection_price_value' => 'string',
                            'selection_price_type' => 'string',
                            'selection_qty' => 'string',
                            'selection_can_change_qty' => 'string',
                            'position' => 'string',
                            'is_default' => 'string',
                            'sku' => 'string',
                        )
                    )
                )
            ),
            'group_price' => array(
                array(
                    'website_id' => 'string',
                    'cust_group' => 'string',
                    'price' => 'decimal'
                )
            ),
            'tier_price' => array(
                array(
                    'website_id' => 'string',
                    'cust_group' => 'string',
                    'price_qty' => 'integer',
                    'price' => 'decimal'
                )
            ),
            'product_attributes' => array(
                array(
                    'type' => 'string',
                    'value' => 'string'
                )
            ),
        );

        // add inventory item
        $inventoryEntity = $this->inventoryFactory->create();
        $schemaFields['inventory'] = $inventoryEntity->schema();
        return $schemaFields;
    }

    /**
     * Get website ids from an array of website codes
     *
     * @param array $websites
     * @return array
     */
    protected function getWebsiteIds($websites)
    {
        $websiteIds = array();
        if (!is_array($websites)) {
            $websites = explode(',', $websites);
        }
        foreach ($websites as $website) {
            if (!$website) {
                continue;
            }
            $website = $this->storeManager->getWebsite(trim($website));
            $websiteIds[] = $website->getId();
        }
        return $websiteIds;
    }

    /**
     * Get category ids from an array of category names
     *
     * @param array $categories
     * @param string $storeId
     * @return array
     */
    protected function getCategoryIds($categories, $storeId)
    {
        // set the current store to get categories
        $currentCode = $this->storeManager->getStore($storeId)->getCode();
        $this->storeManager->setCurrentStore($currentCode);

        $categoryIds = array();
        if (!is_array($categories)) {
            $categories = explode(',', $categories);
        }
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
                $category = $categoryCollection->getFirstItem();
            }
            $categoryIds[] = $category->getId();
        }
        // set the current store back to admin afterwards
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        return $categoryIds;
    }

    /**
     * Converts values of certain price keys to be in a format
     * to save the price
     *
     * @param array $prices
     * @return array
     */
    protected function convertPriceKeys($prices)
    {
        $convertedArray = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['website_id'] = $this->storeManager->getWebsite($price['website_id'])->getId();
                $price['cust_group'] = $this->customerGroupFactory->create()
                    ->load($price['cust_group'], 'customer_group_code')->getId();
                $convertedArray[] = $price;
            }
        }
        return $convertedArray;
    }

    /**
     * Filters the values of certain keys to be in a text format when
     * reading the entity and removes certain keys from being displayed
     *
     * @param array $array
     * @return array
     */
    protected function filterPriceKeys($array)
    {
        $priceFilterKeys = array('price_id', 'all_groups', 'website_price');
        $filteredArray = array();
        if (is_array($array)) {
            foreach (array_values($array) as $value) {
                foreach ($priceFilterKeys as $priceFilterKey) {
                    if (array_key_exists($priceFilterKey, $value)) {
                        unset($value[$priceFilterKey]);
                    }
                }
                if (isset($value['website_id'])) {
                    $value['website_id'] = $this->storeManager->getWebsite($value['website_id'])->getCode();
                }
                if (isset($value['cust_group'])) {
                    $group = $this->customerGroupFactory->create()->load($value['cust_group']);
                    $value['cust_group'] = $group->getCode();
                }
                $filteredArray[] = $value;
            }
        }
        return $filteredArray;
    }

    /**
     * Get the attribute set name
     *
     * @param int $entityTypeId
     * @param int $id
     * @return string
     */
    public function getAttributeSetName($product)
    {
        if ($setId = $product->getAttributeSetId()) {
            $set = $this->attributeSetFactory->create()->load($setId);
            return $set->getAttributeSetName();
        }
        return null;
    }

    /**
     * Get the entire collection of the current entity
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getEntityCollection()
    {
        $entityModel = $this->entityFactory->create();
        // include the flag that the stock filter is already set so it
        // does not get set on the before load.
        $collection = $entityModel
            ->getCollection()
            ->setFlag('has_stock_status_filter');
        return $collection;
    }

    /**
     * Load an entity on this model by the entity id
     * but check for a SKU first
     *
     * @param mixed $id
     * @return \Dsync_Dsync_Model_Entity_Abstract
     * @throws Dsync_Dsync_Exception
     */
    public function loadEntity($id)
    {
        $productId = $this->getEntityFactory()->create()->getIdBySku($id);
        if ($productId) {
            $entity = $this->getEntityFactory()->create()->load($productId);
            if ($entity->getId()) {
                $id = $entity->getId();
            }
        }
        return parent::loadEntity($id);
    }

    public function loadEntityBySharedKey($id)
    {
        // The product entity uses attributes
        $collection = $this->getEntityCollection();
        $collection->addAttributeToFilter($this->getSharedKey(), $id);
        $entity = $collection->getFirstItem();
        if ($entity->getId()) {
            $this->setEntity($entity);
            return $this;
        } else {
            throw new \Dsync\Dsync\Exception('This entity could not be loaded with the selected shared key.');
        }
    }
}
