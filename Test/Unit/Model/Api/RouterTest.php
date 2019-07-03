<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Router
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Router';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $helperMock->expects($this->any())->method('getAuthToken')->will($this->returnValue(123));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $updatedArguments = array(
            'helper' => $helperMock,
            'response' => $responseMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testRun()
    {
        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getHeader')->will($this->returnValue(123));

        $routeMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Route\AbstractRoute'
        )->disableOriginalConstructor()->getMockForAbstractClass();
        
        $routeMock->expects($this->any())->method('setResponse')->will($this->returnValue($routeMock));

        $this->model->setRequest($httpRequestMock);
        $this->model->setRoute($routeMock);

        $this->assertNotEmpty(get_class($this->model->route()));
    }
}
