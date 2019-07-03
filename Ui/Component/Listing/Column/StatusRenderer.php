<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Status renderer class
 */
class StatusRenderer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Dsync\Dsync\Model\System\Config\Source\Process\Status $status
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Dsync\Dsync\Model\System\Config\Source\Process\Status $status,
        array $components = [],
        array $data = []
    ) {
        $this->status = $status;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $this->buildSpanClass($item);
            }
        }
        return $dataSource;
    }

    /**
     * Build the span class based on the status type
     *
     * @param array $item
     */
    protected function buildSpanClass(&$item)
    {
        switch ($item['status']) {
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::COMPLETE:
                $class = 'grid-severity-notice';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING:
                $class = 'grid-severity-minor';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY:
                $class = 'grid-severity-major';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PROCESSING:
                $class = 'grid-severity-major';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_NOTIFICATION:
                $class = 'grid-severity-minor';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR:
                $class = 'grid-severity-critical';
                $status = $this->status->getStatusLabel($item['status']);
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::UNRECOVERABLE_ERROR:
                $class = 'grid-severity-critical';
                $status = $this->status->getStatusLabel($item['status']);
                break;
        }
        $item['status_html'] = '<span class="' . $class . '"><span>' . $status . '</span></span>';
    }
}
