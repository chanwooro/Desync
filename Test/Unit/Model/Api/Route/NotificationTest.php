<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api\Route;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Route\Notification
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Route\Notification';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $processCollectionMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ResourceModel\Process\Collection'
        )->disableOriginalConstructor()->setMethods(
            ['addFieldToFilter', 'getFirstItem']
        )->getMock();

        $processMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Process'
        )->disableOriginalConstructor()->getMock();

        $processMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $processMock->expects($this->any())->method('save')->will($this->returnValue(true));
        $processMock->expects($this->any())->method('addProcessNotification')->will($this->returnValue($processMock));

        $processCollectionMock->expects($this->any())->method('getFirstItem')->will($this->returnValue($processMock));
        $processCollectionMock
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($processCollectionMock));

        $processCollectionFactoryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ResourceModel\Process\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $processCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($processCollectionMock));

        $notificationMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Notification'
        )->disableOriginalConstructor()->setMethods(
            ['setProcessId', 'setRelationId', 'setStatus', 'setMessage']
        )->getMock();

        $notificationMock->expects($this->any())->method('setMessage')->will($this->returnValue($notificationMock));
        $notificationMock->expects($this->any())->method('setStatus')->will($this->returnValue($notificationMock));
        $notificationMock->expects($this->any())->method('setRelationId')->will($this->returnValue($notificationMock));
        $notificationMock->expects($this->any())->method('setProcessId')->will($this->returnValue($notificationMock));

        $notificationFactoryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\NotificationFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $notificationFactoryMock->expects($this->any())->method('create')->will($this->returnValue($notificationMock));

        $updatedArguments = array(
            'helper' => $helperMock,
            'processCollectionFactory' => $processCollectionFactoryMock,
            'notificationFactory' => $notificationFactoryMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testDispatchFail()
    {
        $request = array(
            'process_id' => 'b',
            'statusz' => 200,
            'message' => 'a message',
            'data' => array(
                'notifications' => array(
                    array (
                        'relation_id' => '1',
                        'status' => 200,
                        'message' => 'all good'
                    )
                )
            )
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock->expects($this->any())->method('getHeader')->will($this->returnValue('a'));

        $this->setExpectedException('\Dsync\Dsync\Exception');

        $this->model->dispatch($httpRequestMock);
    }

    public function testDispatchComplete()
    {
        $request = array(
            'process_id' => 'b',
            'status' => 200,
            'message' => 'a message',
            'data' => array(
                'notifications' => array(
                    array (
                        'relation_id' => '1',
                        'status' => 200,
                        'message' => 'all good'
                    )
                )
            )
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock->expects($this->any())->method('getHeader')->will($this->returnValue('a'));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }
}
