<?php
class Cda_Wizard_Block_Promise extends Mage_Core_Block_Template{


    public $_resource;
    public $_readConnection;
    public function _construct()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
    }


    public function getPromise($prid){
        return Mage::getModel('wizard/promise')->getPromise($prid);
    }


    public function getPromiseMetal(){
        return Mage::getModel('wizard/promise')->getPromiseMetal();
    }
}