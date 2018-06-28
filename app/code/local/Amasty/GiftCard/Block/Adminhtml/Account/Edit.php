<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'amgiftcard';
		$this->_controller = 'adminhtml_account';

		/*$this->_addButton('save_and_continue', array(
			'label'     => Mage::helper('amgiftcard')->__('Save and Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class' => 'save'
		), 10);*/

		$this->_addButton('save_and_send', array(
			'label'     => Mage::helper('amgiftcard')->__('Save & Send Email'),
			'onclick'   => 'saveAndSend()',
			'class' => 'save'
		), 0);

		$this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";
		$this->_formScripts[] = "function saveAndSend(){ editForm.submit($('edit_form').action + 'send/email') }";
	}

	public function getHeaderText()
	{
		$header = "";
		$model = Mage::registry('amgiftcard_account');
		if ($model->getId()){
			$header = Mage::helper('amgiftcard')->__('Edit Gift Code Account');
		} else {
			$header = Mage::helper('amgiftcard')->__('New Gift Code Account');
		}
		return $header;
	}
}