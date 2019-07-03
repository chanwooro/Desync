<?php

namespace Dsync\Dsync\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade schema class
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup
                ->getConnection()
                ->addColumn($setup->getTable('sales_order'), 'external_order_id', array(
                        'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length'    => 45,
                        'comment' => 'External Order ID'
                        ));
        }

        $setup->endSetup();
    }
}
