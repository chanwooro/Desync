<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process edit controller
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Dsync\Dsync\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    protected $processFactory;

    /**
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\ProcessFactory $processFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->helper = $helper;
        $this->processFactory = $processFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $processId = $this->getRequest()->getParam('id');
        $processModel = $this->processFactory->create();
        $process = $processModel->load($processId);

        if ($process->getId()) {
            $this->coreRegistry->register('dsync_process', $process);
        } else {
            $this->messageManager->addError(__('This process does not exist.'));
            $this->_redirect('*/*/');
            return;
        }

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
        return $this->_authorization->isAllowed('Dsync_Dsync::config_dsync');
    }
    // @codingStandardsIgnoreEnd

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
