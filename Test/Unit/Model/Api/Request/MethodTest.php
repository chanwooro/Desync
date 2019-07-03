<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api\Request;

class MethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Model\Api\Request\Method
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Request\Method';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $updatedArguments = array();

        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testGetMethodType()
    {
        $this->assertSame('POST', $this->model->getMethodType('create'));
    }

    public function testGetMethod()
    {
        $this->assertSame('create', $this->model->getMethod('POST'));
    }

    public function testGetMethods()
    {
        $this->assertNotEmpty($this->model->getMethods());
    }

    public function testIsMethodAllowed()
    {
        $this->assertTrue($this->model->isMethodAllowed('create'));
    }

    public function testGetOptions()
    {
        $this->assertNotEmpty($this->model->getOptions());
    }

    public function testToOptionArray()
    {
        $this->assertNotEmpty($this->model->toOptionArray());
    }
}
