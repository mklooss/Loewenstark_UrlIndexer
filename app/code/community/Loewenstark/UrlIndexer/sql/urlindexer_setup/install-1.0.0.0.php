<?php
/**
 * Loewenstark_UrlIndexer
 *
 * @category  Loewenstark
 * @package   Loewenstark_UrlIndexer
 * @author    Mathis Klooss <m.klooss@loewenstark.com>
 * @copyright 2013 Loewenstark Web-Solution GmbH (http://www.loewenstark.de). All rights served.
 * @license   https://github.com/mklooss/Loewenstark_UrlIndexer/blob/master/README.md
 */
$installer = $this;
/* @var $installer Loewenstark_UrlIndexer_Model_Resource_Setup */
$installer->startSetup();

// set index/key in core_url_rewrite to perform left join
$installer->getConnection()
	->addIndex(
		$installer->getTable('core/url_rewrite'),
		$installer->getIdxName('core/url_rewrite', array('category_id', 'is_system', 'product_id', 'store_id', 'id_path')),
		array('category_id', 'is_system', 'product_id', 'store_id', 'id_path'),
		Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
	);

// clone original core_url_rewrite
$table = $installer->getConnection()
	->newTable($installer->getTable('urlindexer/url_rewrite'))
	->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), 'Rewrite Id')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned' => true,
		'nullable' => false,
		'default' => '0',
	), 'Store Id')
	->addColumn('id_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Id Path')
	->addColumn('request_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Request Path')
	->addColumn('target_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Target Path')
	->addColumn('is_system', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned' => true,
		'default' => '1',
	), 'Defines is Rewrite System')
	->addColumn('options', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable' => true,
	), 'Options')
	->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Deascription')
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => true,
	), 'Category Id')
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => true,
	), 'Product Id')
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('request_path', 'store_id'),
		Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
		array('request_path', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('id_path', 'is_system', 'store_id'),
		Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
		array('id_path', 'is_system', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('target_path', 'store_id')),
		array('target_path', 'store_id'))
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('id_path')),
		array('id_path'))
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('store_id')),
		array('store_id'))
	->addIndex($installer->getIdxName('urlindexer/url_rewrite', array('category_id', 'is_system', 'product_id', 'store_id', 'id_path')),
		array('category_id', 'is_system', 'product_id', 'store_id', 'id_path'))
	->addForeignKey($installer->getFkName('urlindexer/url_rewrite', 'store_id', 'core/store', 'store_id'),
		'store_id', $installer->getTable('core/store'), 'store_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('urlindexer/url_rewrite', 'category_id', 'catalog/category', 'entity_id'),
		'category_id', $installer->getTable('catalog/category'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('urlindexer/url_rewrite', 'product_id', 'catalog/product', 'entity_id'),
		'product_id', $installer->getTable('catalog/product'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Url Rewrites');
$installer->getConnection()->createTable($table);

$installer->endSetup();
