<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model\ResourceModel\Process\Grid;

/**
 * ResourceModel process grid collection model
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Dsync\Dsync\Helper\Data $helper,
        $mainTable,
        $resourceModel
    ) {
        $this->helper = $helper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * Add user defined process grid filters in system config
     * before rendering filters
     *
     * @codingStandardsIgnoreStart
     */
    protected function _renderFiltersBefore() {
        $this->addFieldToFilter('status', array(
            'in' => $this->helper->getProcessGridFilter()
        ));
        parent::_renderFiltersBefore();
    }
    // @codingStandardsIgnoreEnd
}
