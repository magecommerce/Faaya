<?php
class Faaya_Deleteapi_Model_Deleteapi_Api extends Mage_Api_Model_Resource_Abstract
{
    public function deleteapi($sku)
    {
        Mage::getModel('wizard/productupdate')->updateProduct($sku);
        return json_encode($sku);

    }
}
