<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\System\Config\Source\System;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\System\Type
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\System\Config\Source\System\Type';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $this->model = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetRequestTypes()
    {
        $this->assertNotEmpty($this->model->getSystemTypes());
    }

    public function testGetRequestTypeLabel()
    {
        $this->assertSame('production', $this->model->getSystemTypeLabel(2));
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
