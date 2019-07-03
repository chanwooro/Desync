<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\ResourceModel\Stock\Item;

/**
 * ResourceModel stock item collection class used for mass synchronization.
 * This is setup because Mage 2 currently does not have a regular collection
 * for stock items.  It is set in Dsync\Dsync\Model\Entity\Inventory and used
 * in the getCollection method.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     *
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'item_id';
    // @codingStandardsIgnoreEnd

    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\CatalogInventory\Model\Stock\Item',
            'Magento\CatalogInventory\Model\ResourceModel\Stock\Item'
        );
    }
    // @codingStandardsIgnoreEnd
}
