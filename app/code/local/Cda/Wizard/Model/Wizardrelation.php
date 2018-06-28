<?php

class Cda_Wizard_Model_Wizardrelation extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("wizard/wizardrelation");

    }

    public function getAvailbleSizes($product,$flag){
        $pid = $product->getId();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $availableArr = $readConnection->fetchOne('select available_size from wizardmaster where pid ='.$pid);
        $sizeArr = array();
        if($availableArr != ''){
            $availableArr = explode(",", $availableArr);
            foreach ($availableArr as $value) {
                $value = explode(":", $value);
                if($value[1] > 0){
                    $sizeArr[] = $value[1];
                }
            }
        }
        $sizeArr[] = Mage::Helper('wizard')->getAttributeValue('product_size',$product->getProductSize());
        sort($sizeArr);
        if($flag){
            $stringArr = array(reset($sizeArr),end($sizeArr));
            if(reset($sizeArr) == end($sizeArr)){
                return reset($sizeArr);
            }else{
                return implode('-', $stringArr);
            }
        }else{
            return $sizeArr;
        }

    }

    public function checkMetalOption($product){
        $pid = $product->getId();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $skuList = $readConnection->fetchCol('select DISTINCT variant_refsmryid from wizardrelation where pid ='.$pid.' and variant_refsmryid != "-"');
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToSelect('karat')->addAttributeToSelect('item_id')->addAttributeToSelect('metal_color')->addFieldToFilter('sku', array('in' => $skuList));

        $karatMetal = array();
        foreach ($collection as $item) {
            if($item->getMetalColor() != "" && $item->getKarat() != ""){
                $karatMetal[$item->getMetalColor().'-'.$item->getKarat()] = $item->getItemId();
            }
        }
        $karatMetal[$product->getMetalColor().'-'.$product->getKarat()] = $product->getItemId();

        return $karatMetal;
    }

    public function getMatchingBand($product){
        $id = $product->getId();
        $collection = $this->getCollection();
        $collection->addFieldToFilter('pid',$id);
        $collection->addFieldToFilter('type','wedding');
        $pIDatt = $collection->getColumnValues('variant_id');

        $minMax = array();
        if(!empty($pIDatt)){
            $data = Mage::Helper('wizard')->getProductId(implode(',', $pIDatt));
            if($data){
                foreach ($data as $id) {
                    $wwddingBand = Mage::getModel('catalog/product')->load($id);
                    $wwddingBand = $wwddingBand->getName();
                    return $wwddingBand;
                }
            }

        }
        return false;
    }
}
