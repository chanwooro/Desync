<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\System\Config\Source\Request;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Request\Type
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\System\Config\Source\Request\Type';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $this->model = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetRequestTypes()
    {
        $this->assertNotEmpty($this->model->getRequestTypes());
    }

    public function testGetRequestTypeLabel()
    {
        $this->assertSame('source', $this->model->getRequestTypeLabel(1));
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
