<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_CodeSet_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id'     => 'edit_form',
			'action' => $this->getUrl('*/*/saveCodeSet', array('code_set_id' => $this->getRequest()->getParam('code_set_id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));

		$form->setUseContainer(true);
		$this->setForm($form);

		return parent::_prepareForm();
	}
}