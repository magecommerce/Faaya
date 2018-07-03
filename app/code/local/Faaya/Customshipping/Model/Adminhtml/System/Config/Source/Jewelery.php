<?php
class Faaya_Customshipping_Model_Adminhtml_System_Config_Source_Jewelery extends Mage_Core_Model_Abstract
{
   public function toOptionArray()
   {
       $category = Mage::getModel('catalog/category')->loadByAttribute('name', 'jewelry');
       $_categories = $category->getChildrenCategories();
       $cat = array();
       $cat[] = array('value' => '','label' => 'Please Select');
        foreach($_categories as $_category):
            if($_category->getIsActive()):
                $cat[] = array('value' => $_category->getId(),'label' => $_category->getName());
            endif;
        endforeach;
       return $cat;
   }
   public function getcatArray()
   {
       $category = Mage::getModel('catalog/category')->loadByAttribute('name', 'jewelry');
       $_categories = $category->getChildrenCategories();
       $cat = array();
        foreach($_categories as $_category):
            if($_category->getIsActive()):
                $cat[$_category->getId()] = $_category->getName();
            endif;
        endforeach;
       return $cat;
   }
}