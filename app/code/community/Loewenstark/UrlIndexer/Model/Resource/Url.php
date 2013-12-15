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
     * Limit products for select
     *
     * @var int
     */
    protected $_productLimit                = 250;

    protected function _construct() {
        parent::_construct();
        Mage::dispatchEvent('urlindexer_construct', array(
            'model' => $this,
        ));
    }


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
        return parent::_getCategories($categoryIds, $storeId, $path);
    }
    
    /**
     * get all defined Product Data from array per storeview
     * 
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    public function getProductsByIds($productIds, $storeId, &$lastEntityId)
    {
        return $this->_getProducts($productIds, $storeId, $lastEntityId, $lastEntityId);
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
    /**
     * Retrieve Product data objects
     *
     * @param int|array $productIds
     * @param int $storeId
     * @param int $entityId
     * @param int $lastEntityId
     * @return array
     */
    protected function _getProducts($productIds, $storeId, $entityId, &$lastEntityId)
    {
        $products   = array();
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        $adapter    = $this->_getReadAdapter();
        if ($productIds !== null) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            sort($productIds); // set right order
        }
        $bind = array(
            'website_id' => (int)$websiteId,
            'entity_id'  => (int)$entityId,
        );
        $select = $adapter->select()
            ->useStraightJoin(true)
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'e.entity_id = w.product_id AND w.website_id = :website_id',
                array()
            )
            ->where('e.entity_id > :entity_id')
            ->order('e.entity_id')
            ->limit($this->_productLimit);
        if ($productIds !== null) {
            $select->where('e.entity_id IN(?)', $productIds);
        }
        
        unset($productIds, $entityId);
        
        $this->_addProductAttributeToSelect($select, 'name', $storeId);
        $this->_addProductAttributeToSelect($select, 'url_key', $storeId);
        $this->_addProductAttributeToSelect($select, 'url_path', $storeId);
        $this->_addProductAttributeStatusToSelect($select, $storeId);
        $this->_addProductAttributeVisibilityToSelect($select, $storeId);
        
        Mage::dispatchEvent('urlindexer_getproducts_select', array(
            'model' => $this,
            'select' => $select,
            'store_id' => $storeId
        ));

        foreach ($adapter->fetchAll($select, $bind) as $row) {
            if(isset($row['status'])) {
                unset($row['status']);
            }
            if(isset($row['visibility'])) {
                unset($row['visibility']);
            }
            $product = new Varien_Object($row);
            $product->setIdFieldName('entity_id');
            $product->setCategoryIds(array());
            $product->setStoreId($storeId);
            $products[$product->getId()] = $product;
            $lastEntityId = $product->getId();
        }
        
        unset($bind, $select);

        if ($products && !$this->_helper()->DoNotUseCategoryPathInProduct($storeId)) {
            $select = $adapter->select()
                ->from(
                    $this->getTable('catalog/category_product'),
                    array('product_id', 'category_id')
                )
                ->where('product_id IN(?)', array_keys($products));
            foreach ($adapter->fetchAll($select) as $category) {
                $productId = $category['product_id'];
                $categoryIds = $products[$productId]->getCategoryIds();
                $categoryIds[] = $category['category_id'];
                $products[$productId]->setCategoryIds($categoryIds);
            }
        }
        return $products;
    }
    
    /**
     * Retrieve product attribute
     *
     * @param string $attributeCode
     * @param int|array $productIds
     * @param string $storeId
     * @return array
     */
    public function _addProductAttributeToSelect(Varien_Db_Select $select, $attributeCode, $storeId)
    {
        $attributeCode = trim($attributeCode);
        $storeId = (int)$storeId;
        $adapter = $this->_getReadAdapter();
        if (!isset($this->_productAttributes[$attributeCode])) {
            $attribute = $this->getProductModel()->getResource()->getAttribute($attributeCode);

            $this->_productAttributes[$attributeCode] = array(
                'entity_type_id' => (int)$attribute->getEntityTypeId(),
                'attribute_id'   => (int)$attribute->getId(),
                'table'          => $attribute->getBackend()->getTable(),
                'is_global'      => (int)$attribute->getIsGlobal()
            );
            unset($attribute);
        }
        
        $attributeTable = $this->_productAttributes[$attributeCode]['table'];
        $attributeId = $this->_productAttributes[$attributeCode]['attribute_id'];
        
        if ($this->_productAttributes[$attributeCode]['is_global'] == 1 || $storeId == 0) {
            $attributeAlias = 'attr_'.$attributeId;
            $select->joinLeft(
                    array($attributeAlias => $attributeTable),
                    'e.entity_id = '.$attributeAlias.'.entity_id AND '.$attributeAlias.'.attribute_id = '.$attributeId.' AND '.$attributeAlias.'.store_id = 0',
                    array()
                )->columns(array($attributeCode => $attributeAlias.'.value'));
        } else {
            $attributeAlias1 = 'attr_'.$attributeId;
            $attributeAlias2 = 'attr_'.$attributeId.'_2';
            $valueExpr = $adapter->getCheckSql($attributeAlias1.'.value_id > 0', $attributeAlias1.'.value', $attributeAlias2.'.value');
            $select->joinLeft(
                    array($attributeAlias1 => $attributeTable),
                    'e.entity_id = '.$attributeAlias1.'.entity_id AND '.$attributeAlias1.'.attribute_id = '.$attributeId.' AND '.$attributeAlias1.'.store_id = '.$storeId,
                    array()
                )->joinLeft(
                    array($attributeAlias2 => $attributeTable),
                    'e.entity_id = '.$attributeAlias2.'.entity_id AND '.$attributeAlias2.'.attribute_id = '.$attributeId.' AND '.$attributeAlias2.'.store_id = 0',
                    array()
                )
                ->columns(array($attributeCode => $valueExpr));
        }
        return $this;
    }
    
    /**
     * add Status Filter to Select
     * 
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function _addProductAttributeStatusToSelect(Varien_Db_Select $select, $storeId)
    {
        if($this->_helper()->HideDisabledProducts($storeId))
        {
            $this->_addProductAttributeToSelect($select, 'status', $storeId);
            $select->where('status = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $select->where('status IS NOT NULL');
        }
        return $this;
    }
    
    /**
     * add Status Filter to Select
     * 
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function _addProductAttributeVisibilityToSelect(Varien_Db_Select $select, $storeId)
    {
        if($this->_helper()->HideNotVisibileProducts($storeId))
        {
            $this->_addProductAttributeToSelect($select, 'visibility', $storeId);
            $select->where('visibility != ?', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
            $select->where('visibility IS NOT NULL');
        }
        return $this;
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
        if($this->_helper()->OptimizeCategoriesLeftJoin($rewriteData['store_id']))
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
     * @param int $limit
     * @return Loewenstark_UrlIndexer_Model_Resource_Url
     */
    public function setProductLimit($limit)
    {
        $this->_productLimit = $limit;
        return $this;
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
