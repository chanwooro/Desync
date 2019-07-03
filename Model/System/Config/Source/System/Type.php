<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model\System\Config\Source\System;

/**
 * System config source system type
 */
class Type
{
    const STAGE = 1;
    const PRODUCTION = 2;
    const CUSTOM = 3;

    /**
     * Get system types
     *
     * @return array
     */
    public function getSystemTypes()
    {
        $types = array(
            //self::STAGE => 'stage',
            self::PRODUCTION => 'production',
            self::CUSTOM => 'custom',
        );
        return $types;
    }

    /**
     * Get the system type label from system type
     *
     * @param int $systemType
     * @return string
     */
    public function getSystemTypeLabel($systemType)
    {
        $types = $this->getSystemTypes();
        if (array_key_exists($systemType, $types)) {
            return $types[$systemType];
        }
        return null;
    }

    /**
     * Get system type options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->getSystemTypes() as $key => $value) {
            $options[$key] = ucwords($value);
        }
        return $options;
    }

    /**
     * Get system type options for admin
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
