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
		$suffix = $this->getProductUrlSuffix($category->getStoreId());
		$urlKey = basename($url, $suffix); // get current url key
		if ($this->_helper()->isEnabled() && $category->getLevel() == 1 && ($product->getUrlKey() == '' || $urlKey != $product->getUrlKey())) {
			$this->_urlKey = $urlKey;
			$product->setUrlKey($urlKey);
			$this->getResource()->saveProductAttribute($product, 'url_key');
		}
		return $url;
	}

	/**
	 * refresh url rewrites by product ids
	 *
	 * @param array $productIds
	 * @param null|int $store_id
	 * @return Loewenstark_UrlIndexer_Model_Url
	 */
	public function refreshProductRewriteByIds($productIds, $store_id = null)
	{
		$stores = array();
		if (is_null($store_id)) {
			$stores = $this->getStores();
		} else {
			$stores = array((int)$store_id => $this->getStores($store_id));
		}
		foreach ($stores as $storeId => $store) {
			$storeRootCategoryId = $store->getRootCategoryId();
			$storeRootCategory = $this->getResource()->getCategory($storeRootCategoryId, $storeId);
			$process = true;
			$lastEntityId = 0;
			while ($process == true) {
				$products = $this->getResource()->getProductsByIds($productIds, $storeId, $lastEntityId);
				if (!$products) {
					$process = false;
					break;
				}
				foreach ($products as $product) {
					$categories = $this->getResource()->getCategories($product->getCategoryIds(), $storeId);
					if (!isset($categories[$storeRootCategoryId])) {
						$categories[$storeRootCategoryId] = $storeRootCategory;
					}
					foreach ($categories as $category) {
						$this->_refreshProductRewrite($product, $category);
					}
				}
			}
		}
		return $this;
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
		if ($this->_helper()->isEnabled() && $this->_urlKey && $this->_urlKey != $product->getUrlKey()) {
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
		if (! $this->_helper()->DoNotUseCategoryPathInProduct($category->getStoreId())) {
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
