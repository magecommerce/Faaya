<?php
class Cda_Wizard_Block_Ring extends Mage_Core_Block_Template{

    public $_product;
    public function _construct()
    {
        $id = $this->getRequest()->getParams('id');
        $this->_product = Mage::getModel('catalog/product')->load($id);
    }

    public function getRingAttrribute()
    {
        $collection = Mage::getModel('wizard/wizardattribute')->getCollection()->addFieldToFilter('type','RING');
        $newData = array();
        foreach ($collection->getData() as $value) {
            $newData[$value['code']] = $value;
        }
        return $newData;
    }

    public function getRingDetail($id)
    {
        $collection = Mage::getModel('wizard/wizardattribute')->getCollection()->addFieldToFilter('type','RING');
        $newData = array();
        foreach ($collection->getData() as $value) {
            $newData[$value['code']] = $value;
        }
        return $newData;
    }
}