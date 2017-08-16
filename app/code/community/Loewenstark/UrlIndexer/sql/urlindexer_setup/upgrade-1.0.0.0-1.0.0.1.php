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

//$installer->setConfigData();

$conn = $installer->getConnection();
/* @var $conn Varien_Db_Adapter_Interface */

// copy dnd patch config
$select = $conn->select()
	->from($installer->getTable('core/config_data'), array('path', 'scope', 'value', 'scope_id'))
	->where('path = ?', '/dev/index/disable')
	->orWhere('path = ?', '/dev/index/notvisible');
foreach ($conn->fetchAll($select) as $config) {
	$path = null;
	if ($config['path'] == '/dev/index/disable') {
		$path = '/dev/index/disable_products';
	} elseif ($config['path'] == '/dev/index/notvisible') {
		$path = '/dev/index/notvisible_products';
	} else {
		continue;
	}
	$installer->setConfigData($path, $config['value'], $config['scope'], $config['scopeId']);
	unset($path);
}
// remove dnd patch config
$conn->delete($installer->getTable('core/config_data'), "path IN('/dev/index/disable','/dev/index/notvisible')");

$installer->endSetup();
