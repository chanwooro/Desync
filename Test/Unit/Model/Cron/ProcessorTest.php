<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Cron;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Cron\Processor
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Cron\Processor';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        // create a mock registry
        $registryMock = $this->getMockBuilder(
            '\Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            ['flushAll']
        )->getMock();

        $helperMock->expects($this->any())->method('getRegistry')->will($this->returnValue($registryMock));
        $helperMock->expects($this->any())->method('isCleaningEnabled')->will($this->returnValue(true));
        $helperMock->expects($this->any())->method('getCleaningMinutes')->will($this->returnValue(4));
        $helperMock->expects($this->any())->method('getMinutesBetweenDates')->will($this->returnValue(5));

        // build the process factory
        $processFactoryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ProcessFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        // create a mock process
        $processMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Process'
        )->disableOriginalConstructor()->setMethods(
            ['getCollection', 'save', 'retry', 'delete', 'sendProcessNotification']
        )->getMock();

        $processMock->expects($this->any())->method('retry')->will($this->returnValue(true));
        $processMock->expects($this->any())->method('sendProcessNotification')->will($this->returnValue(true));

        // create a mock process collection
        $processCollectionMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ResourceModel\Process\Collection'
        )->disableOriginalConstructor()->getMock();

        // add the iterator to the collection to return a mock process
        $processCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array($processMock)));

        $processCollectionMock
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue($processCollectionMock));

        $processMock->expects($this->any())->method('getCollection')->will($this->returnValue($processCollectionMock));
        $processFactoryMock->expects($this->any())->method('create')->will($this->returnValue($processMock));

        $apiRequestMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Request'
        )->disableOriginalConstructor()->setMethods(
            ['setIsRetry', 'send']
        )->getMock();

        $apiRequestMock->expects($this->any())->method('setIsRetry')->will($this->returnValue($apiRequestMock));
        $apiRequestMock->expects($this->any())->method('send')->will($this->returnValue(true));

        $updatedArguments = array(
            'helper' => $helperMock,
            'processFactory' => $processFactoryMock,
            'request' => $apiRequestMock

        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testRetryProcesses()
    {
        $this->assertNull($this->model->retryProcesses());
    }

    public function testSendNotifications()
    {
        $this->assertNull($this->model->sendNotifications());
    }

    public function testCleanData()
    {
        $this->assertNull($this->model->cleanData());
    }
}
