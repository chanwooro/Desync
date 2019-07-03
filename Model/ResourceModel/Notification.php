<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * ResourceModel notification class
 */
class Notification extends AbstractDb
{
    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init('dsync_notification', 'id');
    }
    // @codingStandardsIgnoreEnd
}
