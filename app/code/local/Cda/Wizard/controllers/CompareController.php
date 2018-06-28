<?php
class Cda_Wizard_CompareController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}