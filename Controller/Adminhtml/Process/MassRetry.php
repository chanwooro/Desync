<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Controller\Adminhtml\Process;

/**
 * Process mass retry controller
 */
class MassRetry extends \Dsync\Dsync\Controller\Adminhtml\Process\Retry
{
    /**
     * @var \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
     */
    protected $processCollectionFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter $filter
     */
    protected $filter;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\ProcessFactory $processFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\ProcessFactory $processFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory $processCollectionFactory
    ) {
        $this->processCollectionFactory = $processCollectionFactory;
        $this->filter = $filter;
        parent::__construct($context, $helper, $processFactory);
    }

    /**
     * Mass retry action
     *
     * @return
     */
    public function execute()
    {
        $retried = 0;
        $processCollection = $this->filter->getCollection($this->processCollectionFactory->create());
        foreach ($processCollection as $process) {
            // flush registry values
            $this->getHelper()->getRegistry()->flushAll();

            try {
                if ($process->getStatus() != \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR) {
                    continue;
                }
                $this->retry($process);
                $retried++;
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        if ($retried) {
            $processCollection->saveErrors();
            $this->messageManager->addSuccess(__('%1 processes have been retried.', $retried));
        } else {
            $this->messageManager->addNotice(__('There was nothing valid to retry.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
