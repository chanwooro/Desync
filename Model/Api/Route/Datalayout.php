<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Model\Api\Route;

/**
 * Datalayout route class
 */
class Datalayout extends \Dsync\Dsync\Model\Api\Route\AbstractRoute
{
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
        $data = array(
            'data_layout' => array (
            )
        );

        $entities = $this->entityTypeModel->getEntityTypes();

        foreach ($entities as $key => $value) {
            // load the entity model
            $entityModel = $this->getHelper()->createEntity($key);
            if (!$entityModel) {
                continue;
            }
            if (!$entityModel->isEntityPrimary()) {
                continue;
            }
            // try to get the schema for the entity model
            try {
                $schema = $entityModel->generateSchema();
            } catch (\Exception $e) {
                $this->getHelper()->log($e->getMessage());
                continue;
            }

            // generate the endpoint url for the entity
            $url = '/dsync/api/entity/' . $key;
            $entityArray = array(
                'entity_name' => $value,
                'treekey' => $value,
                'entity_token' => $entityModel->getEntityToken(true),
                'endpoint_url' => $url,
                'fields' => $schema
            );
            $data['data_layout'][] = $entityArray;
        }
        // clean the configuration cache so that the updated
        // values can be used right away
        $this->getHelper()->cleanCache('config');

        $this->getResponse()
            ->setResponseCode(\Dsync\Dsync\Model\Api\Response\Code::HTTP_OK)
            ->setResponseData($data);
        return $this->getResponse();
    }
}
