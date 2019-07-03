<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\RequestFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactory;

    /**
     * @var \Dsync\Dsync\Model\Process
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Process';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $helper = $arguments['helper'];

        // build the request factory
        $requestFactory = $this->getMockBuilder(
            '\Dsync\Dsync\Model\RequestFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        // create a mock request
        $requestMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Request'
        )->disableOriginalConstructor()->getMock();

        // set the request mock to "load" iteself
        $requestMock->expects($this->any())->method('load')->will($this->returnValue($requestMock));

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        // create a mock registry
        $registryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Registry'
        )->disableOriginalConstructor()->getMock();

        // create a mock entity
        $entityMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Entity\AbstractEntity'
        )->disableOriginalConstructor()->setMethods(
            ['isProcessable', 'getEntityType']
        )->getMockForAbstractClass();

        $entityMock->expects($this->any())->method('isProcessable')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('getEntityType')->will($this->returnValue('product'));

        $helperMock->expects($this->any())->method('createEntity')->will($this->returnValue($entityMock));
        $helperMock->expects($this->any())->method('getRegistry')->will($this->returnValue($registryMock));

        $apiRequestMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Request'
        )->disableOriginalConstructor()->setMethods(
            ['setIsRetry', 'send']
        )->getMock();

        $apiRequestMock->expects($this->any())->method('setIsRetry')->will($this->returnValue($apiRequestMock));
        $apiRequestMock->expects($this->any())->method('send')->will($this->returnValue(true));

        $this->requestFactory = $requestFactory;
        $this->requestFactory->expects($this->any())->method('create')->will($this->returnValue($requestMock));

        $notificationCollectionMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ResourceModel\Notification\Collection'
        )->disableOriginalConstructor()->setMethods(
            ['addFieldToFilter']
        )->getMock();

        $notificationCollectionMock
            ->expects($this->any())
            ->method('addFieldToFilter')
            ->will($this->returnValue(array()));

        $notificationCollectionFactoryMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\ResourceModel\Notification\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $notificationCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($notificationCollectionMock));

        $resourceMock = $this
            ->getMockBuilder('\Magento\Framework\Model\ResourceModel\AbstractResource')
            ->disableOriginalConstructor()
            ->setMethods(
                ['getConnection', 'getIdFieldName', 'save']
            )->getMockForAbstractClass();

        $updatedArguments = array(
            'requestFactory' => $this->requestFactory,
            'helper' => $helperMock,
            'notificationCollectionFactory' => $notificationCollectionFactoryMock,
            'resource' => $resourceMock,
            'apiRequest' => $apiRequestMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testGetIdentities()
    {
        $this->assertSame(['dsync_dsync_process_'], $this->model->getIdentities());
    }

    public function testGetHelper()
    {
        $this->assertNotEmpty(get_class($this->model->getHelper()));
    }

    public function testBeforeSave()
    {
        $this->assertNotEmpty(get_class($this->model->beforeSave()));
    }

    public function testAfterSave()
    {
        $this->assertNotEmpty(get_class($this->model->afterSave()));
    }

    public function testAfterDelete()
    {
        $this->assertNotEmpty(get_class($this->model->afterDelete()));
    }

    public function testSetRequest()
    {
        $requestMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Request'
        )->disableOriginalConstructor()->getMock();

        $this->assertNotEmpty(get_class($this->model->setRequest($requestMock)));
    }

    public function testGetRequest()
    {
        $this->assertNotEmpty(get_class($this->model->getRequest()));
    }

    public function testSetRequestData()
    {
        $data = array('a' => 'b');
        $this->assertNotEmpty(get_class($this->model->setRequestData($data)));
    }

    public function testGetRequestData()
    {
        $requestData = array('a' => 'b');
        $requestMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Request'
        )->disableOriginalConstructor()->getMock();
        $this->model->setRequest($requestMock);
        $this->model->setRequestData($requestData);
        $this->assertSame($requestData, $this->model->getRequestData());
    }

    public function testCancel()
    {
        $this->model->setRequestType(1);
        $this->assertNull($this->model->cancel());
    }

    public function testRetrySource()
    {
        $this->model->setRequestType(1);
        $this->assertNotEmpty(get_class($this->model->retry()));
    }

    public function testRetryDestination()
    {
        $this->model->setRequestType(2);
        $this->assertNotEmpty(get_class($this->model->retry()));
    }

    public function testSendProcessNotification()
    {
        $this->model->setRequestType(1);
        $this->assertNull($this->model->sendProcessNotification());
    }

    public function testAddProcessNotification()
    {
        $notificationMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Notification'
        )->disableOriginalConstructor()->getMock();
        $this->assertNotEmpty(get_class($this->model->addProcessNotification($notificationMock)));
    }

    public function testGetProcessNotifications()
    {
        $notificationMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Notification'
        )->disableOriginalConstructor()->getMock();
        $this->model->addProcessNotification($notificationMock);
        $this->assertNotEmpty($this->model->getProcessNotifications());
    }

    public function testGetRegistry()
    {
        $this->assertNotEmpty(get_class($this->model->getRegistry()));
    }
}
