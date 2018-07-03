<?php
class Cda_Wizard_Model_Productupdate extends Mage_Core_Model_Abstract
{
    public $_resource;
    public $_writeConnection;
    protected function _construct(){
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_writeConnection = $this->_resource->getConnection('core_write');
    }

    public function updateProduct($sku)
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        if($product){
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $stockItem->setQty(1);
            $stockItem->setData('use_config_manage_stock',0);
            $stockItem->setData('manage_stock',1);
            $stockItem->setData('is_in_stock',0);
            $stockItem->save();
            $updateEdit = "update wizardmaster set status=0 where sku='".$sku."' ";
            $this->_writeConnection->query($updateEdit);
        }
    }
}
