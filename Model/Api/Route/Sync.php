<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api\Route;

/**
 * Sync route class
 */
class Sync extends \Dsync\Dsync\Model\Api\Route\AbstractRoute
{
    const ROUTE_TOTAL = 'total';
    const ROUTE_ITEMS = 'items';

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     */
    protected $entityTypeModel;

    /**
     * @param \Dsync\Dsync\Helper\Data $helper
     * @param \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
     */
    public function __construct(
        \Dsync\Dsync\Helper\Data $helper,
        \Dsync\Dsync\Model\System\Config\Source\Entity\Type $entityTypeModel
    ) {
        $this->entityTypeModel = $entityTypeModel;
        parent::__construct($helper);
    }

    /**
     * Dispatch the request
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return \Dsync\Dsync\Model\Api\Response
     */
    public function dispatch($request)
    {
        $data = [];
        $entityToken = $request->getHeader('Entity-Token');
        if (!$entityToken) {
            throw new \Dsync\Dsync\Exception('Entity Token is missing from the request.');
        }

        $entityType = $this->entityTypeModel->getEntityTypeByEntityToken($entityToken);

        if (!$entityType) {
            throw new \Dsync\Dsync\Exception('Invalid Entity Token supplied in the request.');
        }
        $entityModel = $this->getHelper()->createEntity($entityType);
        if (!$entityModel) {
            throw new \Dsync\Dsync\Exception('Invalid entity supplied.');
        }
        $route = null;
        foreach ($request->getParams() as $key => $value) {
            if ($key) {
                $route = $key;
                $page = $value;
                break;
            }
        }
        switch ($route) {
            case self::ROUTE_TOTAL:
                $data = array(
                    'total' => $entityModel->countEntities(),
                    'per_page' => $this->getHelper()->getPageSize()
                );
                break;
            case self::ROUTE_ITEMS:
                $total = $entityModel->countEntities();
                $perPage = $this->getHelper()->getPageSize();
                
                $items = array();
                $collection = $entityModel->getEntityCollection();
                $collection
                    ->setPageSize($this->getHelper()->getPageSize())
                    ->setCurPage($page);
                foreach ($collection as $itemEntity) {
                    $model = $entityModel->setEntity($itemEntity);
                    $items[] = $model->read();
                }
                $data['items'] = $items;
                // if it gets here disabled the logger for this request
                // as writing this amount of data can take up all the memory
                $this
                    ->getHelper()
                    ->getRegistry()
                    ->register('logging_disabled', true);
                // add the id headers
                $this
                    ->getResponse()
                    ->addHeader('Entity-Id-Field', $entityModel->getEntityIdField())
                    ->addHeader('Total', $total)
                    ->addHeader('Per-Page', $perPage);
                break;
        }
        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK)
            ->setResponseData($data);
        return $this->getResponse();
    }
}
