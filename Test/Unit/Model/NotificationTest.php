<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\Notification
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Notification';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $helper = $arguments['helper'];

        $this->helper = $helper;
        $this->model = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetIdentities()
    {
        $this->assertSame(['dsync_dsync_notification_'], $this->model->getIdentities());
    }

    public function testGetHelper()
    {
        $this->assertNotEmpty(get_class($this->model->getHelper()));
    }

    public function testBeforeSave()
    {
        $this->assertNotEmpty(get_class($this->model->beforeSave()));
    }
}
