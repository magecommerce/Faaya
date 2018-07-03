<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Edit_Tab_Resend extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);

		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amgiftcard')->__('Send Gift Card'),
		));

		$fieldset->addField('recipient_email', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Recipient Email'),
				'name'	=> 'recipient_email',
			)
		);

		$fieldset->addField('recipient_name', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Recipient Name'),
				'name' 	=> 'recipient_name',
			)
		);

		$storeModel = Mage::getSingleton('adminhtml/system_store');
		/* @var $storeModel Mage_Adminhtml_Model_System_Store */
		//$websiteCollection = $storeModel->getWebsiteCollection();
		//$groupCollection = $storeModel->getGroupCollection();
		$storeCollection = $storeModel->getStoreCollection();
		$listStore = array();
		foreach ($storeCollection as $store) {
			$listStore[$store->getId()] = $store->getName();
		}

		$fieldset->addField('store_id', 'select',
			array(
				'label' 	=> Mage::helper('amgiftcard')->__('Send Email from the Following Store View'),
				'options'	=> $listStore,
				'name' 		=> 'store_id',
				'value'		=> Mage::registry('amgiftcard_account')->getOrder()->getStoreId(),
			)
		);

		Mage::registry('amgiftcard_account')->getImageWithCode();


		$form->setValues(Mage::registry('amgiftcard_account')->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}
}