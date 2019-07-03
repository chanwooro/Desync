<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\ResourceModel\Notification;

/**
 * ResourceModel Nofitication Collection class
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     *
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'id';
    // @codingStandardsIgnoreEnd

    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init('Dsync\Dsync\Model\Notification', 'Dsync\Dsync\Model\ResourceModel\Notification');
    }
    // @codingStandardsIgnoreEnd
}
