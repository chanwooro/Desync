<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api\Route;

class DatalayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Route\Datalayout
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Route\Datalayout';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        $entityTypeMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\System\Config\Source\Entity\Type'
        )->disableOriginalConstructor()->setMethods(
            ['getEntityTypes']
        )->getMock();

        $entityTypeMock
            ->expects($this->any())
            ->method('getEntityTypes')
            ->will($this->returnValue(array('product' => 'product')));

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        // create a mock entity
        $entityMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Entity\AbstractEntity'
        )->disableOriginalConstructor()->setMethods(
            ['isEntityActive', 'processSchema', 'getEntityToken']
        )->getMockForAbstractClass();

        $entityMock->expects($this->any())->method('isEntityActive')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('processSchema')->will($this->returnValue(array()));
        $entityMock->expects($this->any())->method('getEntityToken')->will($this->returnValue('12345'));

        $helperMock->expects($this->any())->method('createEntity')->will($this->returnValue($entityMock));

        $updatedArguments = array(
            'helper' => $helperMock,
            'entityTypeModel' => $entityTypeMock,
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testDispatchComplete()
    {
        $request = array();

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $responseMock->expects($this->any())->method('setResponseCode')->will($this->returnValue($responseMock));

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }
}
