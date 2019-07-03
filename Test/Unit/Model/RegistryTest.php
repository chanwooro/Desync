<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Registry
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Registry';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $registryMock = $this->getMockBuilder(
            '\Magento\Framework\Registry'
        )->disableOriginalConstructor()->getMock();

        $registryMock->expects($this->any())->method('registry')->will($this->returnValue('test value!'));

        $entityTypeFactoryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\System\Config\Source\Entity\TypeFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $entityTypeMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\System\Config\Source\Entity\Type'
        )->disableOriginalConstructor()->setMethods(
            ['getEntityTypes']
        )->getMock();

        $entityTypeMock
            ->expects($this->any())
            ->method('getEntityTypes')
            ->will($this->returnValue(array('product' => 'product')));

        $entityTypeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($entityTypeMock));

        $requestMethodModelMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Request\Method'
        )->disableOriginalConstructor()->getMock();

        $requestMethodModelMock
            ->expects($this->any())
            ->method('getMethods')
            ->will($this->returnValue(array('create' => 'POST')));

        $updatedArguments = array(
            'coreRegistry' => $registryMock,
            'entityTypeFactory' => $entityTypeFactoryMock,
            'requestMethodModel' => $requestMethodModelMock
        );

        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testSet()
    {
        $this->assertNotEmpty(get_class($this->model->set('product', 'create', 'test value')));
    }

    public function testGet()
    {
        $this->assertSame('test value!', $this->model->get('product', 'create'));
    }

    public function testDel()
    {
        $this->assertNull($this->model->del('product', 'create'));
    }

    public function testFlushAll()
    {
        $this->assertNull($this->model->flushAll());
    }

    public function testFlush()
    {
        $this->assertNull($this->model->flush('product'));
    }
}
