<?php


class Faaya_Assist_Block_Adminhtml_Assist extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_assist";
	$this->_blockGroup = "assist";
	$this->_headerText = Mage::helper("assist")->__("Assist Manager");
	$this->_addButtonLabel = Mage::helper("assist")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}