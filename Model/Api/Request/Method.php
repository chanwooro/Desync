<?php

namespace Dsync\Dsync\Model\Api\Request;

/**
 * Request method class
 */
class Method implements \Magento\Framework\Data\OptionSourceInterface
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';

    /**
     * Get all of the request methods
     *
     * @return array
     */
    public function getMethods()
    {
        return array(
            self::CREATE => \Zend\Http\Request::METHOD_POST,
            self::READ => \Zend\Http\Request::METHOD_GET,
            self::UPDATE => \Zend\Http\Request::METHOD_PUT,
            self::DELETE => \Zend\Http\Request::METHOD_DELETE,
        );
    }

    /**
     * Get the Zend HTTP Client method type from a method
     *
     * @param string $method
     * @return string
     */
    public function getMethodType($method)
    {
        foreach ($this->getMethods() as $key => $value) {
            if ($key == $method) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Get the method from the Zend HTTP Client method type
     *
     * @param string $methodType
     * @return string
     */
    public function getMethod($methodType)
    {
        foreach ($this->getMethods() as $key => $value) {
            if ($value == $methodType) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Check to see if the method is allowed
     *
     * @param string $method
     * @return boolean
     */
    public function isMethodAllowed($method)
    {
        $methods = $this->getMethods();
        if (array_key_exists($method, $methods)) {
            return true;
        }
        return false;
    }

    /**
     * Get all the method options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        foreach (array_keys($this->getMethods()) as $key) {
            $options[$key] = ucwords($key);
        }
        return $options;
    }

    /**
     * Get method options for admin
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
