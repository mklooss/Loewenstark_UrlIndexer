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
class Loewenstark_UrlIndexer_Model_Url
extends Mage_Catalog_Model_Url
{
    CONST XML_PATH_DISABLE_CATEGORIE = 'catalog/seo_product/use_categories';
    
    protected $_urlKey = false;

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
        if($this->_helper()->isEnabled() && $product->getUrlKey() == '')
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
        if($this->_helper()->isEnabled() && $this->_urlKey && $this->_urlKey != $product->getUrlKey())
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
        if($this->_helper()->DoNotUseCategoryPathInProduct($category->getStoreId()))
        {
            return parent::_refreshCategoryProductRewrites($category);
        }
        return $this;
    }
    
    /**
     * Retrieve resource model, just for phpDoc :)
     *
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function getResource()
    {
        return parent::getResource();
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