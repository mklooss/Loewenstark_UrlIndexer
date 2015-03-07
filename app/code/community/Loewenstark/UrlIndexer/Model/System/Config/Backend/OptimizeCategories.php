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
class Loewenstark_UrlIndexer_Model_System_Config_Backend_OptimizeCategories
    extends Mage_Core_Model_Config_Data
{
    protected $_stores = null;

    public function save()
    {
        $value = $this->getValue();
        $Oldvalue = $this->getOldValue();
        parent::save();
        if ($value != $Oldvalue && $value == 1 && count($this->_getStores()) > 0) {
            $readCon = $this->_getResource()->getReadConnection();
            $writeCon = Mage::getSingleton('core/resource')->getConnection('core_write');
            /* @var $readCon Varien_Db_Adapter_Interface */
            /* @var $writeCon Varien_Db_Adapter_Interface */
            $readCon->delete($this->_getResource()->getTable('urlindexer/url_rewrite'), 'store_id IN(' . implode(',', $this->_getStores()) . ')');
            $cols = explode(',', 'store_id,id_path,request_path,target_path,is_system,options,description,category_id,product_id');
            $select = $writeCon->select()
                ->from($this->_getResource()->getTable('core/url_rewrite'), $cols)
                ->where('store_id IN(' . implode(',', $this->_getStores()) . ')')
                ->where('product_id IS NULL')
                ->where('category_id IS NOT NULL')
                ->where('is_system = ?', 1)
                ->where("id_path LIKE 'category/%'")
                ->where('options IS NULL');
            $writeCon->query($select->insertFromSelect($this->_getResource()->getTable('urlindexer/url_rewrite'), explode(',', 'store_id,id_path,request_path,target_path,is_system,options,description,category_id,product_id'), true));
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    protected function _getStores()
    {
        if (is_null($this->_stores)) {
            $storeIds = array();
            // all stores
            if ($this->getScope() == 'default') {
                foreach (Mage::app()->getWebsites() as $website) {
                    foreach ($website->getGroups() as $group) {
                        foreach ($group->getStores() as $store) {
                            $storeIds[] = (int)$store->getId();
                        }
                    }
                }
                // websites+
            } elseif ($this->getScope() == 'websites') {
                foreach (Mage::app()->getWebsites() as $website) {
                    if ($website->getId() == $this->getScopeId()) {
                        foreach ($website->getGroups() as $group) {
                            foreach ($group->getStores() as $store) {
                                $storeIds[] = (int)$store->getId();
                            }
                        }
                    }
                }
            } elseif ($this->getScope() == 'stores') {
                $storeIds[] = (int)$this->getScopeId();
            }
            $this->_stores = $storeIds;
        }
        return $this->_stores;
    }
}
