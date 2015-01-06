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
class Loewenstark_UrlIndexer_Helper_Data
extends Mage_Core_Helper_Abstract
{
    // isEnabled
    const XML_PATH_IS_ENABLED = 'dev/index/enable';
    // HideDisabledProducts
    const XML_PATH_DISABLED_PRODUCTS = 'dev/index/disable_products';
    // HideNotVisibileProducts
    const XML_PATH_HIDE_PRODUCTS = 'dev/index/notvisible_products';
    // HideNotVisibileProducts
    const XML_PATH_DISABLED_CATEGORIES = 'dev/index/disable_categories';
    // DoNotUseCategoryPathInProduct
    const XML_PATH_DISABLE_CATEGORIE = 'catalog/seo/product_use_categories';
    // OptimizeCategoriesLeftJoin
    const XML_PATH_LEFTJOIN_CATEGORIE = 'dev/index/optimize_categorie_leftjoin';
    
    /**
     * is Modul enabled
     * 
     * @param int $storeid
     * @return bool
     */
    public function isEnabled($storeid=0)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_IS_ENABLED, $storeid);
    }
    
    /**
     * disabled products
     * 
     * @param int $storeid
     * @return boolean
     */
    public function HideDisabledProducts($storeid=0)
    {
        return $this->_getConfigFlag(self::XML_PATH_DISABLED_PRODUCTS, $storeid);
    }
    /**
     * disabled categories
     * 
     * @param int $storeid
     * @return boolean
     */
    public function HideDisabledCategories($storeid=0)
    {
        return $this->_getConfigFlag(self::XML_PATH_DISABLED_CATEGORIES, $storeid);
    }
    
    /**
     * 
     * @param int $storeid
     * @return bool
     */
    public function HideNotVisibileProducts($storeid=0)
    {
        return $this->_getConfigFlag(self::XML_PATH_HIDE_PRODUCTS, $storeid);
    }
    
    /**
     * 
     * @param int $storeid
     * @return bool
     */
    public function OptimizeCategoriesLeftJoin($storeid=0)
    {
        return !$this->_getConfigFlag(self::XML_PATH_LEFTJOIN_CATEGORIE, $storeid);
    }
    
    /**
     * 
     * @param int $storeid
     * @return bool
     */
    public function DoNotUseCategoryPathInProduct($storeid=0)
    {
        return !$this->_getConfigFlag(self::XML_PATH_DISABLE_CATEGORIE, $storeid);
    }
    
    /**
     * Mage::getStoreConfigFlag
     * 
     * @param string $xmlPath
     * @param int $storeid
     */
    protected function _getConfigFlag($xmlPath, $storeid=0)
    {
        if($this->isEnabled())
        {
            return Mage::getStoreConfigFlag($xmlPath, $storeid);
        }
        return false;
    }
}