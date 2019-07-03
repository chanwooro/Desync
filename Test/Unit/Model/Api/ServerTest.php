<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Server
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Server';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        // create a mock router
        $routerMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Router'
        )->disableOriginalConstructor()->getMock();

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();
        
        $responseMock->expects($this->any())->method('getHeaders')->will($this->returnValue(array()));

        $routerMock->expects($this->any())->method('route')->will($this->returnValue($responseMock));
        $routerMock->expects($this->any())->method('setRequest')->will($this->returnValue($routerMock));
        $routerMock->expects($this->any())->method('setRoute')->will($this->returnValue($routerMock));

        $updatedArguments = array(
            'helper' => $helperMock,
            'router' => $routerMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testRun()
    {
        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpResponseMock = $this->getMockBuilder(
            '\Magento\Framework\App\Response\Http'
        )->disableOriginalConstructor()->getMock();

        $httpResponseMock
            ->expects($this->any())
            ->method('setHttpResponseCode')
            ->will($this->returnValue($httpResponseMock));
        $httpResponseMock->expects($this->any())->method('setHeader')->will($this->returnValue($httpResponseMock));

        $this->model->setRequest($httpRequestMock);
        $this->model->setResponse($httpResponseMock);

        $this->assertNull($this->model->run());
    }
}
