<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'amgiftcard';
		$this->_controller = 'adminhtml_image';

		$this->_addButton('save_and_continue', array(
			'label'     => Mage::helper('amgiftcard')->__('Save and Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class' => 'save'
		), 10);

		$this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";
	}

	public function getHeaderText()
	{
		$header = "";
		$model = Mage::registry('amgiftcard_image');
		if ($model->getId()){
			$header = Mage::helper('amgiftcard')->__('Edit Gift Image');
		} else {
			$header = Mage::helper('amgiftcard')->__('New Gift Image');
		}
		return $header;
	}
}