<?php

class Faaya_Customshipping_Model_Mysql4_Customshipping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the banner_id refers to the key field in your database table.
        $this->_init('customshipping/customshipping', 'id');
    }
}