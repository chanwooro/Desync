<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Dsync\Dsync\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dsync\Dsync\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = 'Dsync\Dsync\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $logger = $arguments['logger'];
        $objectManager = $arguments['objectManager'];

        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $logger;

        $this->objectManager = $objectManager;

        // add scope config values
        $this->scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'scopeConfiggetValue'])
        );

        $this->objectManager->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    return $data;
                }
            )
        );

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetStoreConfig()
    {
        $this->assertSame('dsync/something', $this->helper->getStoreConfig('something'));
    }

    public function testIsModuleActive()
    {
        $this->assertTrue($this->helper->isModuleActive());
    }

    public function testGetAuthToken()
    {
        $this->assertEquals('thisisatesttoken', $this->helper->getAuthToken());
    }

    public function testGetSystemType()
    {
        $this->assertEquals('1', $this->helper->getSystemType());
    }

    public function testGetProcessGridFilter()
    {
        $this->assertEquals(array(1,2,3), $this->helper->getProcessGridFilter());
    }

    public function testIsEntityActive()
    {
        $this->assertTrue($this->helper->isEntityActive('order'));
    }

    public function testIsCleaningEnabled()
    {
        $this->assertTrue($this->helper->isCleaningEnabled());
    }

    public function testGetCleaningMinutes()
    {
        $this->assertEquals(5, $this->helper->getCleaningMinutes());
    }

    public function testCreateEntity()
    {
        $this->assertEquals('Dsync\Dsync\Model\Entity\Product', $this->helper->createEntity('product'));
    }

    public function testIsLoggingEnabled()
    {
        $this->assertTrue($this->helper->isLoggingEnabled());
    }

    public function testGetLogger()
    {
        $this->assertNotEmpty(get_class($this->helper->getLogger()));
    }

    public function testGetMinutesBetweenDates()
    {
        $datetime1 = '2016-04-07 02:18:42';
        $datetime2 = '2016-04-07 02:19:42';
        $this->assertEquals(1.0, $this->helper->getMinutesBetweenDates($datetime1, $datetime2));
    }

    public function testGetUtcDate()
    {
        $this->assertSame(date('Y-m-d H:i:s'), $this->helper->getUtcDate());
    }

    /**
     * @param string $path
     * @return boolean|string|int
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function scopeConfiggetValue($path)
    {
        switch ($path) {
            case 'dsync/something':
                return 'dsync/something';
            case 'dsync/module_config/active':
                return true;
            case 'dsync/module_config/token':
                return 'thisisatesttoken';
            case 'dsync/module_config/system_type':
                return '1';
            case 'dsync/shared_key/order':
                return '1234';
            case 'dsync/module_config/process_grid_filter':
                return '1,2,3';
            case 'dsync/entity_config/order':
                return true;
            case 'dsync/data_config/cleaning':
                return true;
            case 'dsync/data_config/cleaning_minutes':
                return 5;
            case 'dsync/data_config/logging':
                return true;
            default:
                return false;
        }
    }
}
