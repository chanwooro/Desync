<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Api\Response
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Response';
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

        $updatedArguments = array(
            'helper' => $helperMock,
            'responseCodeModel' => $responseCodeModelMock
        );

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testGetBody()
    {
        $this->model->setResponseCode(400);
        $this->model->setResponseMessage('this is a default message');
        $this->model->setResponseDetail('some detail');
        $this->model->setResponseData(array('a' => 'b'));

        $this->assertNotEmpty($this->model->getBody());
    }
}
