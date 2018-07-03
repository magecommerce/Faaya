<?php
class Faaya_Customshipping_Model_Adminhtml_System_Config_Source_Subcategory extends Mage_Core_Model_Abstract
{
   public function toOptionArray()
   {
      $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'sub_category');
      if ($attribute->usesSource()) {
           $options = array();
           $option[] = array("value"=>'',"label"=>"Please select");
           $options = $attribute->getSource()->getAllOptions(false);
           $op = array_merge($option,$options);          
           return $op;
      }
   }
   public function toGetSubcategoryArray()
   {
      $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'sub_category');
      if ($attribute->usesSource()) {
           $options = $attribute->getSource()->getAllOptions(false);
           $subcat = array();
           foreach($options as $op){
               $subcat[$op['value']] = $op['label'];
           }
           return $subcat;
      }
   }
}