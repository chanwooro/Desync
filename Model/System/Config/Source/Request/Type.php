<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model\System\Config\Source\Request;

/**
 * System config source request type
 */
class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    const SOURCE = 1;
    const DESTINATION = 2;

    /**
     * Get request types
     *
     * @return array
     */
    public function getRequestTypes()
    {
        $types = array(
            self::SOURCE => 'source',
            self::DESTINATION => 'destination',
        );
        return $types;
    }

    /**
     * Get request type label from the request type
     *
     * @param int $requestType
     * @return string
     */
    public function getRequestTypeLabel($requestType)
    {
        $types = $this->getRequestTypes();
        if (array_key_exists($requestType, $types)) {
            return $types[$requestType];
        }
        return null;
    }

    /**
     * Get request type options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->getRequestTypes() as $key => $value) {
            $options[$key] = ucwords($value);
        }
        return $options;
    }

    /**
     * Get request type options for admin
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();

        foreach ($this->getOptions() as $key => $value) {
            $optionArray[] = array(
                'value' => $key,
                'label' =>  $value,
            );
        }
        return $optionArray;
    }
}
