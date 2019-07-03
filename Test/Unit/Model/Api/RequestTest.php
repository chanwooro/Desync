<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Request
     */
    protected $model;

    /**
     * @var string
     */
    protected $responseJson;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Request';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $responseCodeModelMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response\Code'
        )->disableOriginalConstructor()->getMock();

        $responseCodeModelMock
            ->expects($this->any())
            ->method('getDefaultStatusMessage')
            ->will($this->returnValue('default message'));

        $apiClientMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Client'
        )->disableOriginalConstructor()->getMock();

        $responseArray = array(
            'data' => array(
                'process_id' => 5
            )
        );

        $this->responseJson = json_encode($responseArray);

        $apiClientMock->expects($this->any())->method('send')->will($this->returnValue($this->responseJson));
        $apiClientMock->expects($this->any())->method('setEntityToken')->will($this->returnValue($apiClientMock));
        $apiClientMock->expects($this->any())->method('setProcessId')->will($this->returnValue($apiClientMock));
        $apiClientMock->expects($this->any())->method('setSystemType')->will($this->returnValue($apiClientMock));
        $apiClientMock
            ->expects($this->any())
            ->method('setDsyncEntityIdField')
            ->will($this->returnValue($apiClientMock));
        $apiClientMock->expects($this->any())->method('setDsyncEntityId')->will($this->returnValue($apiClientMock));
        $apiClientMock->expects($this->any())->method('setRequestEntity')->will($this->returnValue($apiClientMock));

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
            ['setStatus', 'save']
        )->getMock();

        $processMock->expects($this->any())->method('save')->will($this->returnValue(true));

        $processMock->expects($this->any())->method('setStatus')->will($this->returnValue($processMock));

        $processFactoryMock->expects($this->any())->method('create')->will($this->returnValue($processMock));

        $updatedArguments = array(
            'helper' => $helperMock,
            'responseCodeModel' => $responseCodeModelMock,
            'client' => $apiClientMock,
            'processFactory' => $processFactoryMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testSendFail()
    {
        $this->model->resetRequest();

        // create a mock entity
        $entityMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Entity\AbstractEntity'
        )->disableOriginalConstructor()->setMethods(
            [
                'isProcessable',
                'getEntityType',
                'getJobId',
                'getEntityToken',
                'getHelper',
                'getEntityId'
            ]
        )->getMockForAbstractClass();

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $registryMock = $this->getMockBuilder(
            '\Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            ['set']
        )->getMock();

        $helperMock->expects($this->any())->method('getRegistry')->will($this->returnValue($registryMock));

        $entityMock->expects($this->any())->method('getJobId')->will($this->returnValue('12345'));
        $entityMock->expects($this->any())->method('getEntityId')->will($this->returnValue('6'));
        $entityMock->expects($this->any())->method('getEntityToken')->will($this->returnValue('12345'));
        $entityMock->expects($this->any())->method('isProcessable')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('getHelper')->will($this->returnValue($helperMock));
        $entityMock->expects($this->any())->method('getEntityType')->will($this->returnValue('product'));

        $this->model->setRequestEntity($entityMock);

        $this->model->setRequestMethod('create');

        $this->model->setIsRetry(false);

        $this->model->setRequestStatus(400);
        $this->model->setRequestMessage('this is a default message');
        $this->model->setRequestDetail('some detail');
        $this->model->setRequestData(array('a' => 'b'));
        $this->model->setRequestJobId('fdsfss');
        $this->model->setSystemType(1);

        $this->assertNull($this->model->send());
    }

    public function testSendComplete()
    {
        $this->model->resetRequest();

        // create a mock entity
        $entityMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Entity\AbstractEntity'
        )->disableOriginalConstructor()->setMethods(
            [
                'isProcessable',
                'getEntityType',
                'getJobId',
                'getEntityToken',
                'getHelper',
                'getEntityId',
                'processRead',
            ]
        )->getMockForAbstractClass();

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $registryMock = $this->getMockBuilder(
            '\Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            ['set']
        )->getMock();

        $helperMock->expects($this->any())->method('getRegistry')->will($this->returnValue($registryMock));

        $entityMock->expects($this->any())->method('getJobId')->will($this->returnValue('12345'));
        $entityMock->expects($this->any())->method('getEntityId')->will($this->returnValue('6'));
        $entityMock->expects($this->any())->method('getEntityToken')->will($this->returnValue('12345'));
        $entityMock->expects($this->any())->method('isProcessable')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('getHelper')->will($this->returnValue($helperMock));
        $entityMock->expects($this->any())->method('processRead')->will($this->returnValue(array('12345')));
        $entityMock->expects($this->any())->method('getEntityType')->will($this->returnValue('product'));

        $this->model->setRequestEntity($entityMock);

        $this->model->setRequestMethod('create');

        $this->model->setIsRetry(false);

        $this->model->setRequestStatus(400);
        $this->model->setRequestMessage('this is a default message');
        $this->model->setRequestDetail('some detail');
        $this->model->setRequestData(array('a' => 'b'));
        $this->model->setRequestJobId('fdsfss');
        $this->model->setSystemType(1);

        $this->model->getRequestDetail();

        $this->assertSame($this->responseJson, $this->model->send());
    }
}
