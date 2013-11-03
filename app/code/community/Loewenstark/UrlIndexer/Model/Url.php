<?php

class Loewenstark_UrlIndexer_Model_Url
extends Mage_Catalog_Model_Url
{
    CONST XML_PATH_DISABLE_CATEGORIE = 'catalog/seo_product/use_categories';
    
    protected $_urlKey = false;


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
        $url = parent::getProductRequestPath($product, $category);
        $this->_urlKey = false;
        if($category->getLevel() > 1 && $product->getUrlKey() == '')
        {
            $suffix = $this->getProductUrlSuffix($category->getStoreId());
            $urlKey = basename($url, $suffix); // get current url key
            $this->_urlKey = $urlKey;
            $product->setUrlKey($urlKey);
            $this->getResource()->saveProductAttribute($product, 'url_key');
        }
        
        return $url;
    }
    
    /**
     * Refresh product rewrite
     *
     * @param Varien_Object $product
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshProductRewrite(Varien_Object $product, Varien_Object $category)
    {
        if ($category->getId() == $category->getPath()) {
            return $this;
        }
        parent::_refreshProductRewrite($product, $category);
        if($this->_urlKey && $this->_urlKey != $product->getUrlKey())
        {
            $product->setUrlKey($this->_urlKey);
            $this->getResource()->saveProductAttribute($product, 'url_key');
        }
        return $this;
    }
    
    /**
     * Refresh products for category
     *
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Url
     */
    protected function _refreshCategoryProductRewrites(Varien_Object $category)
    {
        if(Mage::getStoreConfigFlag(self::XML_PATH_DISABLE_CATEGORIE, $category->getStoreId()))
        {
            return parent::_refreshCategoryProductRewrites($category);
        }
        return $this;
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