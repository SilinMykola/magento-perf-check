<?php

namespace Acme\Tags\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('acme_tag_stock'))
                ->addColumn('stock_item_id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Id')
                ->addColumn('product_id', Table::TYPE_INTEGER, null, [], 'Product')
                ->addColumn('stock_id', Table::TYPE_INTEGER, null, [], 'Stock')
                ->addIndex($setup->getIdxName('acme_tag_stock', ['product_id']), ['product_id'])
                ->addIndex(
                    $setup->getIdxName('acme_tag_stock', ['product_id', 'stock_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['product_id', 'stock_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable('acme_tag_source'),
                $setup->getIdxName('acme_tag_source', ['source_id', 'product_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['source_id', 'product_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
            $setup->getConnection()->addIndex(
                $setup->getTable('acme_tag_source'),
                $setup->getIdxName('acme_tag_source', ['product_id']),
                ['product_id']
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable('acme_tag_source'),
                $setup->getIdxName('acme_tag_source', ['source_code'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['source_code'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            // Codes repeat from now on.
            $setup->getConnection()->dropIndex(
                $setup->getTable('acme_tag_source'),
                $setup->getIdxName('acme_tag_source', ['source_code'], AdapterInterface::INDEX_TYPE_UNIQUE)
            );
        }

        $setup->endSetup();
    }
}
