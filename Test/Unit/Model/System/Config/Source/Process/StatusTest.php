<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\System\Config\Source\Entity;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Process\Status
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\System\Config\Source\Process\Status';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $this->model = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetProcessStatuses()
    {
        $this->assertNotEmpty($this->model->getProcessStatuses());
    }

    public function testGetStatus()
    {
        $this->assertSame(1, $this->model->getStatus('pending'));
    }

    public function testGetStatusLabel()
    {
        $this->assertSame('pending', $this->model->getStatusLabel(1));
    }

    public function testGetOptions()
    {
        $this->assertNotEmpty($this->model->getOptions());
    }

    public function testToOptionArray()
    {
        $this->assertNotEmpty($this->model->toOptionArray());
    }
}
