<?php
/**
  * Loewenstark_UrlIndexer
  *
  * @category  Loewenstark
  * @package   Loewenstark_UrlIndexer
  * @author    Mathis Klooss <m.klooss@loewenstark.com>
  * @copyright 2013 Loewenstark Web-Solution GmbH (http://www.mage-profis.de/). All rights served.
  * @license   https://github.com/mklooss/Loewenstark_UrlIndexer/blob/master/README.md
  */
/**
 * only changed core/url_rewirte to urlindexer/url_rewrite
 */
class Loewenstark_UrlIndexer_Helper_Category_Url_Rewrite
extends Mage_Catalog_Helper_Category_Url_Rewrite
{
    /**
     * Join url rewrite table to eav collection
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param int $storeId
     * @return Mage_Catalog_Helper_Category_Url_Rewrite
     */
    public function joinTableToEavCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection, $storeId)
    {
        if($this->_helper()->OptimizeCategoriesLeftJoin($storeId))
        {
            $collection->joinTable(
                'urlindexer/url_rewrite',
                'category_id=entity_id',
                array('request_path'),
                "{{table}}.is_system=1 AND " .
                    "{{table}}.store_id='{$storeId}' AND " .
                    "{{table}}.id_path LIKE 'category/%'",
                'left'
            );
            return $this;
        }
        return joinTableToEavCollection($collection, $storeId);
    }

    /**
     * Join url rewrite table to collection
     *
     * @param Mage_Catalog_Model_Resource_Category_Flat_Collection $collection
     * @param int $storeId
     * @return Mage_Catalog_Helper_Category_Url_Rewrite|Mage_Catalog_Helper_Category_Url_Rewrite_Interface
     */
    public function joinTableToCollection(Mage_Catalog_Model_Resource_Category_Flat_Collection $collection, $storeId)
    {
        if($this->_helper()->OptimizeCategoriesLeftJoin($storeId))
        {
            $collection->getSelect()->joinLeft(
                array('url_rewrite' => $collection->getTable('urlindexer/url_rewrite')),
                'url_rewrite.category_id = main_table.entity_id AND url_rewrite.is_system = 1 '.
                    ' AND ' . $collection->getConnection()->quoteInto('url_rewrite.store_id = ?', $storeId).
                    ' AND ' . $collection->getConnection()->quoteInto('url_rewrite.id_path LIKE ?', 'category/%'),
                array('request_path')
            );
            return $this;
        }
        return parent::joinTableToCollection($collection, $storeId);
    }

    /**
     * Join url rewrite to select
     *
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Mage_Catalog_Helper_Category_Url_Rewrite
     */
    public function joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        if($this->_helper()->OptimizeCategoriesLeftJoin($storeId))
        {
            $select->joinLeft(
                array('url_rewrite' => $this->_resource->getTableName('urlindexer/url_rewrite')),
                'url_rewrite.category_id=main_table.entity_id AND url_rewrite.is_system=1 AND ' .
                    $this->_connection->quoteInto('url_rewrite.store_id = ? AND ',
                        (int)$storeId) .
                    $this->_connection->prepareSqlCondition('url_rewrite.id_path', array('like' => 'category/%')),
                array('request_path' => 'url_rewrite.request_path'));
            return $this;
        }
        return parent::joinTableToSelect($select, $storeId);
    }
    
    /**
     * 
     * @return Loewenstark_UrlIndexer_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('urlindexer');
    }
}
