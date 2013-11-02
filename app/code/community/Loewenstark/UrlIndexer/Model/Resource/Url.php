<?php

class Loewenstark_UrlIndexer_Model_Resource_Url
extends Mage_Catalog_Model_Resource_Url
{
    CONST XML_PATH_DISABLE_CATEGORIE = 'catalog/seo_product/use_categories';
    
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
        if(!Mage::getStoreConfigFlag(self::XML_PATH_DISABLE_CATEGORIE, $storeId))
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
        return $this;
    }
    
}