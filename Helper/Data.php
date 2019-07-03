<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dsync\Dsync\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Dsync Data Helper
 */
class Data extends AbstractHelper
{

    const DSYNC_CONFIG_PATH = 'dsync';

    const MAGENTO_LOG_PATH = '/var/log/';

    const DEFAULT_DATE_FORMAT = 'YYYY-MM-DD HH:mm:ss';

    const DSYNC_LOG_FILE = 'dsync-exception.log';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    protected $resourceConfig;

    /**
     * @var \Dsync\Dsync\Logger $logger
     */
    protected $logger;

    /**
     * @var \Dsync\Dsync\Model\Registry $registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\App\CacheInterface $cache
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Dsync\Dsync\Logger $logger
     * @param \Dsync\Dsync\Model\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Dsync\Dsync\Logger $logger,
        \Dsync\Dsync\Model\Registry $registry
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->cache = $cache;
        $this->cacheTypeList = $cacheTypeList;
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * Get a value from the store config
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path)
    {
        return $this->getScopeConfig()->getValue(
            self::DSYNC_CONFIG_PATH . '/' . $path
        );
    }

    /**
     * Save a value in store config
     *
     * @param string $path
     * @param string $value
     */
    public function saveStoreConfig($path, $value)
    {
        $this->resourceConfig
            ->saveConfig(
                self::DSYNC_CONFIG_PATH . '/' . $path,
                $value,
                \Magento\Framework\App\Config::SCOPE_TYPE_DEFAULT,
                0
            );
    }

    /**
     * Get a value from the system cache
     *
     * @param string $path
     * @return string
     */
    public function getCache($path)
    {
        return $this->cache
            ->load(self::DSYNC_CONFIG_PATH . '/' . $path);
    }

    /**
     * Save a value in the system cache
     *
     * @param type $path
     * @param type $value
     */
    public function saveCache($path, $value)
    {
        $this->cache
            ->save($value, self::DSYNC_CONFIG_PATH . '/' . $path);
    }

    /**
     * Clean a system cache by type
     *
     * @param string $type
     */
    public function cleanCache($type)
    {
        $this->cacheTypeList->cleanType($type);
    }

    /**
     * Get the status of the module in system config
     *
     * @return boolean
     */
    public function isModuleActive()
    {
        return $this->getStoreConfig('module_config/active');
    }

    /**
     * Get the authorization token from system config and trims it
     *
     * @return string
     */
    public function getAuthToken()
    {
        return trim($this->getStoreConfig('module_config/token'));
    }

    /**
     * Get the system type from system config and trims it
     *
     * @return int
     */
    public function getSystemType()
    {
        return $this->getStoreConfig('module_config/system_type');
    }

    /**
     * Get the maximum amount of retries
     *
     * @return int
     */
    public function getMaxRetries()
    {
        $maxRetries = $this->getStoreConfig('module_config/max_retries');
        $retries = $maxRetries ? $maxRetries : 1;
        return $retries;
    }

    /**
     * Get the page size for mass synchronization from system config
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->getStoreConfig('mass_sync/page_size');
    }

    /**
     * Get a shared key from system config based on entity type
     *
     * @param string $entityType
     * @return string
     */
    public function getSharedKey($entityType)
    {
        return trim($this->getStoreConfig('shared_key/' . $entityType, null));
    }

    /**
     * Get the process grid filter from system config
     *
     * @return int
     */
    public function getProcessGridFilter()
    {
        return explode(',', $this->getStoreConfig('module_config/process_grid_filter'));
    }

    /**
     * Check if an entity is active based on the entity type
     *
     * @param string $entityType
     * @return boolean
     */
    public function isEntityActive($entityType)
    {
        return $this->getStoreConfig('entity_config/' . $entityType, null);
    }

    /**
     * Check to see if data cleaning is enabled in system config
     *
     * @return boolean
     */
    public function isCleaningEnabled()
    {
        return $this->getStoreConfig('data_config/cleaning');
    }

    /**
     * Get the number of minutes to keep data
     *
     * @return int
     */
    public function getCleaningMinutes()
    {
        return trim($this->getStoreConfig('data_config/cleaning_minutes'));
    }

    /**
     * Check to see if logging is enabled in system config
     *
     * @return boolean
     */
    public function isLoggingEnabled()
    {
        if ($this->getRegistry()->registry('logging_disabled')) {
            return false;
        }
        return $this->getStoreConfig('data_config/logging');
    }

    /**
     * Log a message in the log directory if enabled in system config
     *
     * @param string|array $message
     * @param int $level
     * @param string $file
     */
    public function log($message, $level = null, $file = null)
    {
        if ($this->isLoggingEnabled()) {
            $fileName = $file ? $file : self::DSYNC_LOG_FILE;
            $path = $this->getLogDirectory() . $fileName;
            $this->getLogger()->log($message, $level, $path);
        }
    }

    /**
     * Get the Dsync Logger
     *
     * @return \Dsync\Dsync\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get the scope config
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Get the Dsync Registry
     *
     * @return \Dsync\Dsync\Model\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Get a directory list for the file system
     *
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    public function getDirectoryList()
    {
        return $this->directoryList;
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route, $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * Get the log directory from the directory list
     *
     * @return string
     */
    public function getLogDirectory()
    {
        return $this->getDirectoryList()->getPath('log') . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the current UTC date
     *
     * @return string
     */
    public function getUtcDate()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get the number of minutes between two dates
     *
     * @param string $datetime1
     * @param string $datetime2
     * @return int
     */
    public function getMinutesBetweenDates($datetime1, $datetime2)
    {
        $datetime1 = strtotime($datetime1);
        $datetime2 = strtotime($datetime2);
        $interval  = abs($datetime2 - $datetime1);
        return round($interval / 60);
    }

    /**
     * Try to create an entity class dynamically
     *
     * @param string $type
     * @return \Dsync\Dsync\Model\Entity\AbstractEntity
     */
    public function createEntity($type)
    {
        $entityModelName = $this->generateEntityClass($type);
        if (class_exists($entityModelName)) {
            try {
                return $this->objectManager->create($entityModelName);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Generate an entity class name from a given type
     *
     * @param string $type
     * @return string
     */
    protected function generateEntityClass($type)
    {
        $classname = 'Dsync\Dsync\Model\Entity';
        $pieces = explode('_', $type);

        foreach ($pieces as $piece) {
            $classname = $classname . '\\' . ucwords($piece);
        }
        return $classname;
    }
}
