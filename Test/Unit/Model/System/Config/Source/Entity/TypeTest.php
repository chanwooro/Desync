<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Model\System\Config\Source\Entity;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Dsync\Dsync\Model\System\Config\Source\Entity\Type
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Model\System\Config\Source\Entity\Type';
        $arguments = $objectManagerHelper->getConstructArguments($className);

        // create a mock helper
        $helperMock = $this->getMockBuilder(
            '\Dsync\Dsync\Helper\Data'
        )->disableOriginalConstructor()->getMock();

        $helperMock->expects($this->any())->method('getStoreConfig')->will($this->returnValue('testtoken123'));

        $updatedArguments = array(
            'helper' => $helperMock,
        );

        $this->model = $objectManagerHelper->getObject($className, array_merge($arguments, $updatedArguments));
    }

    public function testGetEntityTypes()
    {
        $this->assertNotEmpty($this->model->getEntityTypes());
    }

    public function testGetEntityType()
    {
        $this->assertSame('product', $this->model->getEntityType('product'));
    }

    public function testGetDsyncEntityType()
    {
        $this->assertSame('product', $this->model->getDsyncEntityType('product'));
    }

    public function testGetJobIds()
    {
        $this->assertNotEmpty($this->model->getJobIds());
    }

    public function testGetJobIdByEntityType()
    {
        $this->assertSame('testtoken123', $this->model->getJobIdByEntityType('product'));
    }

    public function testGetEntityTokens()
    {
        $this->assertNotEmpty($this->model->getEntityTokens());
    }

    public function testGetEntityTokenByEntityType()
    {
        $this->assertSame('testtoken123', $this->model->getEntityTokenByEntityType('product'));
    }

    public function testIsValidDsyncEntityType()
    {
        $this->assertTrue($this->model->isValidDsyncEntityType('product'));
    }

    public function testCreateEntityToken()
    {
        $this->assertNotEmpty($this->model->getEntityTokenByEntityType('fake-product'));
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
