<?php
class Cda_Featuredproduct_Block_Featuredproduct extends Mage_Catalog_Block_Product_Abstract
{   
	public function getFeatureCollection(){
        $attributeCode = 'collection';
        $attributeOption = 'TRENDING COLLECTION';
        $attributeDetails = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
        $options = $attributeDetails->getSource()->getAllOptions(false); 
        $selectedOptionId = false;
        foreach ($options as $option){  
            if ($option['label'] == $attributeOption) {
                $selectedOptionId = $option['value'];   
            }
        }
        if ($selectedOptionId) {
            $collection = Mage::getResourceModel('catalog/product_collection')
                           ->addAttributeToSelect('*')
                           ->addAttributeToFilter($attributeCode, array('eq' => $selectedOptionId))
                           ->load();
            $collection->getSelect()->limit(10);
            return $collection;
       }                 
	}
    public function getcustomCreation(){
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);        
        $collection->addFieldToFilter('custom_creation',1);
        $collection->setPageSize(24);    
        return $collection;
    }
}