<?php
class Faaya_Orderapi_Model_Mysql4_Customerguestuser extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("orderapi/customerguestuser", "id");
    }
}