<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_CodeSet_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'code_set_id';
		$this->_blockGroup = 'amgiftcard';
		$this->_controller = 'adminhtml_codeSet';

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
		$model = Mage::registry('amgiftcard_codeSet');
		if ($model->getId()){
			$header = Mage::helper('amgiftcard')->__('Edit Gift Code Set');
		} else {
			$header = Mage::helper('amgiftcard')->__('New Gift Code Set');
		}
		return $header;
	}

	public function getBackUrl()
	{
		return $this->getUrl('*/*/codes');
	}
}