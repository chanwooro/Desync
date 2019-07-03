<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Process action class
 */
class ProcessAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['id'])) {
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    $this->processActionUrl($item, $urlEntityParamName);
                    $this->addEditUrl($item, $urlEntityParamName);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Process the action url if applicable
     *
     * @param array $item
     * @param string $urlEntityParamName
     */
    protected function processActionUrl(&$item, $urlEntityParamName)
    {
        $retryUrlPath = $this->getData('config/retryUrlPath') ?: '#';
        $cancelUrlPath = $this->getData('config/cancelUrlPath') ?: '#';

        switch ($item['status']) {
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::ERROR:
                $urlPath = $retryUrlPath;
                $urlLabel = __('Retry');
                $type = 'retry';
                break;
            case \Dsync\Dsync\Model\System\Config\Source\Process\Status::PENDING_RETRY:
                $urlPath = $cancelUrlPath;
                $urlLabel = __('Cancel');
                $type = 'cancel';
                break;
            default:
                $urlPath = null;
                $urlLabel = null;
                $type = null;
                break;
        }
        $this->addActionUrl($item, $urlEntityParamName, $urlPath, $urlLabel, $type);
    }

    /**
     * Add an action url
     *
     * @param array $item
     * @param string $urlEntityParamName
     * @param string $urlPath
     * @param string $urlLabel
     * @param string $type
     */
    protected function addActionUrl(&$item, $urlEntityParamName, $urlPath, $urlLabel, $type)
    {
        if ($urlPath && $urlLabel) {
            $item[$this->getData('name')] = [
                $type => [
                    'href' => $this->urlBuilder->getUrl(
                        $urlPath,
                        [
                            $urlEntityParamName => $item['id']
                        ]
                    ),
                    'label' => $urlLabel
                ]
            ];
        }
    }

    /**
     * Add the edit url for each process
     *
     * @param array $item
     * @param string $urlEntityParamName
     */
    protected function addEditUrl(&$item, $urlEntityParamName)
    {
        $editUrlPath = $this->getData('config/editUrlPath') ?: '#';
        $item[$this->getData('name')]['edit'] = [
            'href' => $this->urlBuilder->getUrl(
                $editUrlPath,
                [
                    $urlEntityParamName => $item['id']
                ]
            ),
            'label' => __('View'),
            'hidden' => true,
        ];
    }
}
