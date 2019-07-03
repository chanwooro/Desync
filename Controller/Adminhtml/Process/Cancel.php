<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process cancel controller
 */
class Cancel extends \Magento\Backend\App\Action
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
     * Cancel action
     *
     * @return
     */
    public function execute()
    {

        $processId = $this->getRequest()->getParam('id');
        $processModel = $this->processFactory->create();
        $process = $processModel->load($processId);

        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$process->getId()) {
            $this->messageManager->addError(__('This process does not exist.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->cancel($process);
            $processCollection = $processModel->getCollection();
            $processCollection->saveErrors();
            $this->messageManager->addSuccess(__('Process # %1 was successfully cancelled.', $process->getId()));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Cancel a process
     *
     * @param \Dsync\Dsync\Model\Process $process
     * @throws \Exception
     */
    protected function cancel(\Dsync\Dsync\Model\Process $process)
    {
        if ($process->getIsLocked()) {
            throw new \Exception(__('Process # %1 is currently locked, please try again later.', $process->getId()));
        }
        $process->cancel();
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
