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
class Loewenstark_UrlIndexer_Model_Resource_Url
extends Mage_Catalog_Model_Resource_Url
{
    /**
     * Retrieve categories objects
     * Either $categoryIds or $path (with ending slash) must be specified
     *
     * @param int|array $categoryIds
     * @param int $storeId
     * @param string $path
     * @return array
     */
    protected function _getCategories($categoryIds, $storeId = null, $path = null)
    {
        if($this->_helper()->HideDisabledCategories($storeId))
        {
            $categories = parent::_getCategories($categoryIds, $storeId, $path);
            if($categories)
            {
                $category = end($categories);
                $attributes = $this->_getCategoryAttribute('is_active', array_keys($categories),
                    $category->getStoreId());
                unset($category);
                foreach ($attributes as $categoryId => $attributeValue) {
                    if($attributeValue == 0)
                    {
                        unset($categories[$categoryId]);
                    }
                }
               unset($attributes);
            }
            return $categories;
        }
        return parent::_getCategories($categoryIds, $storeId, $path);
    }
    
    /**
     * Retrieve Product data objects
     * LOE: remove if status(=2) is disabled or visibility(=1) false
     *
     * @param int|array $productIds
     * @param int $storeId
     * @param int $entityId
     * @param int $lastEntityId
     * @return array
     */
    protected function _getProducts($productIds, $storeId, $entityId, &$lastEntityId)
    {
        if($this->_helper()->HideDisabledProducts($storeId) || $this->_helper()->HideNotVisibileProducts($storeId))
        {
            $hasIds = false;
            if($productIds !== null)
            {
                $hasIds = true;
                $productIds = $this->_checkProducts($productIds, $storeId, true);
            }
            if(!$hasIds)
            {
                $products = parent::_getProducts($productIds, $storeId, $entityId, $lastEntityId);
                $products = $this->_checkProducts($products, $storeId, false);
                return $products;
            }
        }
        return parent::_getProducts($productIds, $storeId, $entityId, $lastEntityId);
    }
    
    /**
     * check if products can be disabled
     * 
     * @param array $productIds
     * @param int $storeId
     * @param bool $are_ids
     * @return array
     */
    protected function _checkProducts(&$productIds, $storeId, $use_id=true)
    {
        $_attributes = array();
        if($this->_helper()->HideDisabledProducts($storeId))
        {
            $_attributes[] = 'status';
        }
        if($this->_helper()->HideNotVisibileProducts($storeId))
        {
            $_attributes[] = 'visibility';
        }
        foreach ($_attributes as $attributeCode) {
            $attributes = $this->_getProductAttribute($attributeCode, ($use_id) ? $productIds : array_keys($productIds), $storeId);
            foreach ($attributes as $productId => $attributeValue) {
                if(($attributeCode == 'status' && $attributeValue == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                   ||
                   ($attributeCode == 'visibility' && $attributeValue == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                  )
                {
                    $id = $productId;
                    if($use_id)
                    {
                        $id = array_search($productId, $productIds);
                    }
                    if(isset($productIds[$id]))
                    {
                        unset($productIds[$id]);
                    }
                }
            }
        }
        return $productIds;
    }

    /**
     * Retrieve categories data objects by their ids. Return only categories that belong to specified store.
     * // LOE: Check Categories, force array output
     * @see Mage_Catalog_Model_Resource_Url::getCategories()
     *
     * @param int|array $categoryIds
     * @param int $storeId
     * @return array
     */
    public function getCategories($categoryIds, $storeId)
    {
        if($this->_helper()->DoNotUseCategoryPathInProduct($storeId))
        {
            return array();
        }
        $parent = parent::getCategories($categoryIds, $storeId);
        if(!$parent)
        {
            return array();
        }
        return $parent;
    }
    
    /**
     * Save rewrite URL
     *
     * @param array $rewriteData
     * @param int|Varien_Object $rewrite
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function saveRewrite($rewriteData, $rewrite)
    {
        parent::saveRewrite($rewriteData, $rewrite);
        if($this->_helper()->isEnabled())
        {
            $this->_saveUrlIndexerRewrite($rewriteData, $rewrite);
        }
        return $this;
    }

    /**
     * Save urlindexer rewrite URL
     *
     * @param array $rewriteData
     * @param int|Varien_Object $rewrite
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    protected function _saveUrlIndexerRewrite($rewriteData, $rewrite)
    {
        // check if is a category
        if((isset($rewriteData['category_id']) && !empty($rewriteData['category_id']))
         && isset($rewriteData['is_system']) && intval($rewriteData['is_system']) == 1
         && ((isset($rewriteData['product_id']) && is_null($rewriteData['product_id']))
             || !isset($rewriteData['product_id'])))
        {
            $adapter = $this->_getWriteAdapter();
            try {
                $adapter->insertOnDuplicate($this->getTable('urlindexer/url_rewrite'), $rewriteData);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::throwException(Mage::helper('urlindexer')->__('An error occurred while saving the URL rewrite in urlindexer'));
            }
            
            // delete old entry!
            if ($rewrite && $rewrite->getId()) {
                if ($rewriteData['request_path'] != $rewrite->getRequestPath()) {
                    // Update existing rewrites history and avoid chain redirects
                    $where = array('target_path = ?' => $rewrite->getRequestPath());
                    if ($rewrite->getStoreId()) {
                        $where['store_id = ?'] = (int)$rewrite->getStoreId();
                    }
                    $adapter->delete(
                        $this->_getWriteAdapter()->getTableName('urlindexer/url_rewrite'),
                        $where
                    );
                }
            }
        }
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