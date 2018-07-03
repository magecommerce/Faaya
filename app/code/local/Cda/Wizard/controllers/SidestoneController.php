<?php
class Cda_Wizard_SidestoneController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function liststoneAction(){
        $params = $this->getRequest()->getParams();
        $data = Mage::getModel('wizard/wizardoptionsmapping')->getSidestone($params);
        echo $data;
    }

    public function setsidestoneAction(){
        $params = $this->getRequest()->getParams();
        $data = Mage::getModel('wizard/wizardoptionsmapping')->setSidestone($params);
        echo $data;
    }
}