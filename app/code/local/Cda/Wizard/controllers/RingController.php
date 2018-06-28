<?php
class Cda_Wizard_RingController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}