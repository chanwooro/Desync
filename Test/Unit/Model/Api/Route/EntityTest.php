<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api\Route;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Route\Entity
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Route\Entity';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        // create a mock entity
        $entityMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Entity\AbstractEntity'
        )->disableOriginalConstructor()->setMethods(
            ['isProcessable', 'getEntityType', 'create', 'read', 'update', 'delete']
        )->getMockForAbstractClass();

        $entityMock->expects($this->any())->method('isProcessable')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('create')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('read')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('update')->will($this->returnValue(true));
        $entityMock->expects($this->any())->method('delete')->will($this->returnValue(true));

        $helperMock->expects($this->any())->method('createEntity')->will($this->returnValue($entityMock));

        $requestMethodModelMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Request\Method'
        )->disableOriginalConstructor()->getMock();

        $requestMethodModelMock
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnCallback([$this, 'methodValue']));

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
            'requestMethodModel' => $requestMethodModelMock,
            'processFactory' => $processFactoryMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testDispatchSetJob()
    {
        $request = array(
            'process_id' => 'b',
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getMethod')->will($this->returnValue('post'));
        $httpRequestMock
            ->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue(array('product' => 'set_job')));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock
            ->expects($this->any())
            ->method('getHeader')
            ->will($this->returnCallback([$this, 'headerValue']));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }

    public function testDispatchCreateComplete()
    {
        $request = array(
            'process_id' => 'b',
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getMethod')->will($this->returnValue('post'));
        $httpRequestMock->expects($this->any())->method('getParams')->will($this->returnValue(array('product' => '')));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock
            ->expects($this->any())
            ->method('getHeader')
            ->will($this->returnCallback([$this, 'headerValue']));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }

    public function testDispatchReadComplete()
    {
        $request = array(
            'process_id' => 'b',
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getMethod')->will($this->returnValue('get'));
        $httpRequestMock->expects($this->any())->method('getParams')->will($this->returnValue(array('product' => '')));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock
            ->expects($this->any())
            ->method('getHeader')
            ->will($this->returnCallback([$this, 'headerValue']));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }

    public function testDispatchUpdateComplete()
    {
        $request = array(
            'process_id' => 'b',
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getMethod')->will($this->returnValue('put'));
        $httpRequestMock->expects($this->any())->method('getParams')->will($this->returnValue(array('product' => '')));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock
            ->expects($this->any())
            ->method('getHeader')
            ->will($this->returnCallback([$this, 'headerValue']));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }

    public function testDispatchDeleteComplete()
    {
        $request = array(
            'process_id' => 'b',
        );

        $httpRequestMock = $this->getMockBuilder(
            '\Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();

        $httpRequestMock->expects($this->any())->method('getMethod')->will($this->returnValue('delete'));
        $httpRequestMock->expects($this->any())->method('getParams')->will($this->returnValue(array('product' => '')));

        // create a mock response
        $responseMock = $this->getMockBuilder(
            '\Dsync\Dsync\Model\Api\Response'
        )->disableOriginalConstructor()->getMock();

        $this->model->setResponse($responseMock);
        $httpRequestMock->expects($this->any())->method('getContent')->will($this->returnValue(json_encode($request)));
        $httpRequestMock
            ->expects($this->any())
            ->method('getHeader')
            ->will($this->returnCallback([$this, 'headerValue']));

        $this->assertNotEmpty(get_class($this->model->dispatch($httpRequestMock)));
    }

    /**
     * Callback function, emulates getMethod function
     *
     * @param string $method
     * @return mixed
     */
    public function methodValue($method)
    {
        switch ($method) {
            case 'post':
                return 'create';
            case 'put':
                return 'update';
            case 'get':
                return 'read';
            case 'delete':
                return 'delete';
            default:
                return false;
        }
    }

    /**
     * Callback function, emulates getHeader function
     *
     * @param string $name
     * @return mixed
     */
    public function headerValue($name)
    {
        switch ($name) {
            case 'Process-Id':
                return 'b';
            case 'Entity-Id':
                return 'c';
            default:
                return false;
        }
    }
}
