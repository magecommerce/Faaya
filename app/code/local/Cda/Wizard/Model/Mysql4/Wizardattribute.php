<?php
class Cda_Wizard_Model_Mysql4_Wizardattribute extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("wizard/wizardattribute", "id");
    }
}