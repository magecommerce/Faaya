<?php
class Faaya_Assist_Model_Mysql4_Assist extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("assist/assist", "id");
    }
}