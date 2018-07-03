<?php
class Cda_Customproductlist_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction() { 
	 /* $this->loadLayout();   
	  $this->renderLayout();   */
       $this->getResponse()->setBody($this->getLayout()->createBlock('customproductlist/customproductlist') ->setTemplate("catalog/product/list-response.phtml")->toHtml());
    }
}