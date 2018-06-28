<?php
class Cda_Weddingband_Block_Weddingband extends Mage_Catalog_Block_Product_Abstract
{
    public function viewMatchingBand(){
        $productUrlKey = $this->getRequest()->getParam('productname');
        $product = Mage::getModel('catalog/product')->loadByAttribute('url_key',$productUrlKey);
        $weddingbandCollection = Mage::getModel("wizard/wizardrelation")->getCollection()->addFieldToFilter('pid',$product->getId())->addFieldToFilter('type',"wedding");
        //print_r($weddingbandCollection->getData());
        $variantId = array();
        foreach($weddingbandCollection as $wedding){
            $variantId[] =  $wedding->getVariantId();   
        }
        $productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('variant_id', array('in'=> $variantId));
        $productCollection->load();
        //print_r($productCollection->getData());
        return $productCollection;
    }
     public function getCurrentMatchingRing(){
       $productUrlKey = $this->getRequest()->getParam('productname');  
       $product = Mage::getModel('catalog/product')->loadByAttribute('url_key',$productUrlKey);
       return $product;
   }
}