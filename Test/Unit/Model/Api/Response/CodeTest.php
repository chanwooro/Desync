<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\Api\Response;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Model\Api\Response\Code
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\Api\Response\Code';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        $updatedArguments = array();

        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testGetDefaultStatusMessage()
    {
        $this->assertSame('OK', $this->model->getDefaultStatusMessage(200));
    }
}
