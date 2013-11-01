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
}