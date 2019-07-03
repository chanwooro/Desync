<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\ResourceModel\Process;

/**
 * ResourceModel Process collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var string
     *
     * @codingStandardsIgnoreStart
     */
    protected $_idFieldName = 'id';
    // @codingStandardsIgnoreEnd

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->helper = $helper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init('Dsync\Dsync\Model\Process', 'Dsync\Dsync\Model\ResourceModel\Process');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Check for errors in the process table and save the
     * result to be visible by the Magento admin
     */
    public function saveErrors()
    {
        $this
            ->addFieldToFilter('is_error', true)
            ->addFieldToFilter('is_dismissed', false);
        if ($size = $this->getSize()) {
            $this->getHelper()->saveCache('errors', $size);
        } else {
            $this->getHelper()->saveCache('errors', false);
        }
    }

    /**
     * Get the helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
