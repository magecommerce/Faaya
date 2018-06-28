<?php
class Cda_Wizard_Model_Promise extends Mage_Core_Model_Abstract{
    public $_resource;
    public $_readConnection;
    public function _construct()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
    }


    public function getPromise($prid){
        if($prid){
            $query = 'SELECT * FROM wizardmaster where construction = "Promise Ring" and pid='.$prid;
        }else{
            $query = 'SELECT * FROM wizardmaster where construction = "Promise Ring"';
        }

        $promise = $this->_readConnection->fetchRow($query);
        return $promise;
    }


    public function getPromiseMetal(){
        $query = 'SELECT metal_color,karat FROM wizardmaster where construction = "Promise Ring"';
        $promise = $this->_readConnection->fetchAll($query);
        return $promise;
    }

    public function updatePromise($metal,$karat){
        $query = 'SELECT pid FROM wizardmaster where construction = "Promise Ring" and metal_color="'.$metal.'" and karat="'.$karat.'" ';
        $promise = $this->_readConnection->fetchOne($query);
        return $promise;
    }


}
