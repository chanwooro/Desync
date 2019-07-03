<?php

namespace Dsync\Dsync\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema class
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'dsync_process'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('dsync_process'))) {
            $installer->getConnection()->dropTable($installer->getTable('dsync_process'));
        }

        $processTable = $installer->getConnection()
            ->newTable($installer->getTable('dsync_process'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ), 'Internal Process ID')
            ->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'nullable'  => true,
                'default'   => null,
                'unsigned'  => true,
                ), 'Entity ID')
            ->addColumn('dsync_entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Dsync Entity ID')
            ->addColumn('dsync_entity_id_field', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Dsync Entity ID Field')
            ->addColumn('process_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Process ID')
            ->addColumn('method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Method')
            ->addColumn('entity_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Entity Type')
            ->addColumn('request_type', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Data Request Type')
            ->addColumn('system_type', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Request System Type')
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                ), 'status')
            ->addColumn('message', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Message')
            ->addColumn('retry', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                ), 'Retry')
            ->addColumn('is_error', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Is this an error?')
            ->addColumn('is_dismissed', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Is this error dismissed?')
            ->addColumn('is_locked', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Is locked?')
            ->addColumn('notification_needed', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null, array(
                'nullable'  => false,
                'default'   => '0',
                ), 'Is notification needed?')
            ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Created At')
            ->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Updated At')
            ->addIndex(
                $installer->getIdxName('dsync_process', array('request_type')),
                array('request_type')
            )
            ->addIndex(
                $installer->getIdxName(
                    'dsync_process',
                    array('process_id', 'method', 'entity_type'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                array('process_id', 'method', 'entity_type'),
                array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT)
            )
            ->setComment('Dsync Process Table');
        $installer->getConnection()->createTable($processTable);

        /**
         * Create table 'dsync_request'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('dsync_request'))) {
            $installer->getConnection()->dropTable($installer->getTable('dsync_request'));
        }

        $requestTable = $installer->getConnection()
            ->newTable($installer->getTable('dsync_request'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ), 'Request ID')
            ->addColumn('process_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Internal Process ID')
            ->addColumn('request_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Request Data')
            ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Created At')
            ->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Updated At')
            ->addIndex(
                $installer->getIdxName('dsync_request', array('process_id')),
                array('process_id')
            )
            ->setComment('Dsync Request Table');
        $installer->getConnection()->createTable($requestTable);

        /**
         * Create table 'dsync_notification'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('dsync_notification'))) {
            $installer->getConnection()->dropTable($installer->getTable('dsync_notification'));
        }

        $notificationTable = $installer->getConnection()
            ->newTable($installer->getTable('dsync_notification'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ), 'Notification ID')
            ->addColumn('process_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Process ID')
            ->addColumn('relation_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Relation ID')
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 45, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Status')
            ->addColumn('message', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Message')
            ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Created At')
            ->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, array(
                'nullable'  => true,
                'default'   => null,
                ), 'Updated At')
            ->addIndex(
                $installer->getIdxName('dsync_notification', array('process_id')),
                array('process_id')
            )
            ->setComment('Dsync Notification Table');
        $installer->getConnection()->createTable($notificationTable);

        $installer->endSetup();
    }
}
