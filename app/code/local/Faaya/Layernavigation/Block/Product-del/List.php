<?php
class Cda_Wizard_Block_Product_List extends Mage_Catalog_Block_Product_List
{
     protected $_productCollection;
     protected function _getLoadedProductCollection(){  
        $limit = 5;  
        $currentCategory = Mage::registry('current_category');        
        $getupperLimit = (int)$this->getRequest()->getParam('limit');
        $upperLimit  = $limit * $getupperLimit;
        $lowerLimit = $upperLimit -  $limit;         
        $resource = Mage::getSingleton('core/resource'); 
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('wizardrelation');
        $query = 'SELECT DISTINCT a.pid FROM wizardrelation AS a INNER JOIN wizardrelation AS b ON a.id = b.id WHERE a.variant_id = b.base_variant_id';
        $pids = $readConnection->fetchAll($query);
        /* echo '<pre>';
        echo count($pids);
        print_r($pids);exit;         */
        $_productCollection = Mage::getModel('catalog/product')
         ->getCollection()
         ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
         ->addAttributeToSelect('*')
         ->addAttributeToFilter('category_id',$currentCategory->getId())
         ->addAttributeToSort('created_at', 'desc');
         /*echo '<pre>';
         print_r($_productCollection->getData());exit; */
        echo $total = count($_productCollection);exit;
         //$_productCollection->getSelect()->limit($limit,$upperLimit);  
         $_productCollection->getSelect()->limit($limit);  
          //echo $_productCollection->getSelect();exit;
         //$_productCollection['total'] = $total;
         return $_productCollection;      
     }
}
