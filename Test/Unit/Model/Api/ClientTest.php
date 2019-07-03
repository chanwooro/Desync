<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Client
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Client';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $updatedArguments = array(
            'helper' => $helperMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testSend()
    {
        $requestData = array(
            'a' => 'b'
        );

        $clientMock = $this->getMockBuilder(
            '\Zend\Http\Client'
        )->disableOriginalConstructor()->getMock();

        $responseMock = $this->getMockBuilder(
            '\Zend\Http\Response'
        )->disableOriginalConstructor()->getMock();

        $responseMock->expects($this->any())->method('getBody')->will($this->returnValue(json_encode($requestData)));

        $clientMock->expects($this->any())->method('setHeaders')->will($this->returnValue($clientMock));
        $clientMock->expects($this->any())->method('setUri')->will($this->returnValue($clientMock));
        $clientMock->expects($this->any())->method('setMethod')->will($this->returnValue($clientMock));
        $clientMock->expects($this->any())->method('send')->will($this->returnValue($responseMock));

        $this->model->setClient($clientMock);
        $this->model->setSystemType(1);
        $this->model->setEntityToken('fndskfs4353');
        $this->model->setProcessId('lkiuhgf');

        $this->assertSame(json_encode($requestData), $this->model->send($requestData, 'post', '/test'));
    }
}
