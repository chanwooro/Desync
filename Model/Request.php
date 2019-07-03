<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

use Dsync\Dsync\Api\Data\RequestInterface;

/**
 * Request class
 */
class Request extends AbstractModel implements IdentityInterface, RequestInterface
{
    const CACHE_TAG = 'dsync_dsync_request';

    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        $this->_init('Dsync\Dsync\Model\ResourceModel\Request');
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Dsync\Dsync\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get the unique identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Before save event
     *
     * @return \Dsync\Dsync\Model\Request
     */
    public function beforeSave()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($this->getHelper()->getUtcDate());
        }
        $this->setUpdatedAt($this->getHelper()->getUtcDate());
        return parent::beforeSave();
    }

    /**
     * Return the data helper
     *
     * @return \Dsync\Dsync\Helper\Data
     */
    protected function getHelper()
    {
        return $this->helper;
    }
}
