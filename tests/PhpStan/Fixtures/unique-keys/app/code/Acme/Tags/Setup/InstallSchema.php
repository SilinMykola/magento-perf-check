<?php

namespace Acme\Tags\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()
            ->newTable($setup->getTable('acme_tag_rule'))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Id')
            ->addColumn('code', Table::TYPE_TEXT, 32, [], 'Code')
            ->addIndex(
                $setup->getIdxName('acme_tag_rule', ['code'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
