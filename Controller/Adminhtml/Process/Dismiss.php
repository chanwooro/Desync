<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process dismiss controller
 */
class Dismiss extends \Magento\Backend\App\Action
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
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\ProcessFactory $processFactory
    ) {
        $this->helper = $helper;
        $this->processFactory = $processFactory;
        parent::__construct($context);
    }

    /**
     * Dismiss action
     *
     * @return
     */
    public function execute()
    {
        try {
            $this->getHelper()->saveCache('errors', false);

            $processModel = $this->processFactory->create();
            $processCollection = $processModel->getCollection();
            $processCollection
                ->addFieldToFilter('is_error', true)
                ->addFieldToFilter('is_dismissed', false);

            foreach ($processCollection as $process) {
                $process->setIsDismissed(true)->save();
            }
            $processCollection->saveErrors();
            $this->messageManager->addSuccess(__('Dsync errors have been dismissed.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        return $this
            ->getResponse()
            ->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
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
