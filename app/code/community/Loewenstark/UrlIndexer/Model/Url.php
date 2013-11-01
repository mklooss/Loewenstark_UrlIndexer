<?php

class Loewenstark_UrlIndexer_Model_Url
extends Mage_Catalog_Model_Url
{
    
    /**
     * Get requestPath that was not used yet.
     *
     * Will try to get unique path by adding -1 -2 etc. between url_key and optional url_suffix
     * rewrite to define exists
     *
     * @param int $storeId
     * @param string $requestPath
     * @param string $idPath
     * @return string
     */
    public function getUnusedPath($storeId, $requestPath, $idPath)
    {
        return parent::getUnusedPath($storeId, $requestPath, $idPath);
    }
    
    /**
     * Get unique product request path
     *
     * @param   Varien_Object $product
     * @param   Varien_Object $category
     * @return  string
     */
    public function getProductRequestPath($product, $category)
    {
        return parent::getProductRequestPath($product, $category);
    }
    
    /**
     * Retrieve resource model
     *
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function getResource()
    {
        return parent::getResource();
    }
}