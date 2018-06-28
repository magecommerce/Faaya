<?php
class Cda_Custompost_IndexController extends Mage_Core_Controller_Front_Action{
    public function postAction() {
	  $this->loadLayout();   
	  $this->renderLayout(); 
    }
}