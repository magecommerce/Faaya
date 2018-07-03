<?php
class Cda_Wizard_Block_Index extends Mage_Core_Block_Template{
    public function _construct()
    {

    }

    public function getDiamondAttrribute()
    {
        $collection = Mage::getModel('wizard/wizardattribute')->getCollection()->addFieldToFilter('type','OTHER');
        $newData = array();
        foreach ($collection->getData() as $value) {
            $newData[$value['code']] = $value;
        }
        return $newData;
    }

}