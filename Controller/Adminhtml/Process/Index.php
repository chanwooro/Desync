<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process index controller
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /* @var $resultPage \Magento\Backend\Model\View\Result\Page */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dsync_Dsync::dsync_process');
        $resultPage->addBreadcrumb(__('Dsync Process Notifications'), __('Dsync Process Notifications'));
        $resultPage->getConfig()->getTitle()->prepend(__('Dsync Process Notifications'));

        return $resultPage;
    }

    /**
     * @return bool
     *
     * @codingStandardsIgnoreStart
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dsync_Dsync::process');
    }
    // @codingStandardsIgnoreEnd
}
