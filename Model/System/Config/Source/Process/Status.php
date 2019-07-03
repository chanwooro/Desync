<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Model\System\Config\Source\Process;

/**
 * System config source process status
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    const PENDING = 1;
    const PROCESSING = 2;
    const PENDING_RETRY = 3;
    const PENDING_NOTIFICATION = 4;
    const COMPLETE = 5;
    const ERROR = 6;
    const UNRECOVERABLE_ERROR = 7;

    /**
     * Get process statuses
     *
     * @return array
     */
    public function getProcessStatuses()
    {
        $statuses = array(
            self::PENDING => 'pending',
            self::PROCESSING => 'processing',
            self::PENDING_RETRY => 'pending retry',
            self::PENDING_NOTIFICATION => 'pending notification',
            self::COMPLETE => 'complete',
            self::ERROR => 'error',
            self::UNRECOVERABLE_ERROR => 'unrecoverable error'
        );
        return $statuses;
    }

    /**
     * Get a status from a status label
     *
     * @param string $statusLabel
     * @return int
     */
    public function getStatus($statusLabel)
    {
        foreach ($this->getProcessStatuses() as $key => $value) {
            if ($value == strtolower($statusLabel)) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Get a status label from a status
     *
     * @param int $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $statuses = $this->getProcessStatuses();
        if (array_key_exists($status, $statuses)) {
            return $statuses[$status];
        }
        return null;
    }

    /**
     * Get process status options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->getProcessStatuses() as $key => $value) {
            $options[$key] = ucwords($value);
        }
        return $options;
    }

    /**
     * Get process status options for admin
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
