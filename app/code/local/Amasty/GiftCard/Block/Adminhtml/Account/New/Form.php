<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id'     => 'edit_form',
			'action' => $this->getUrl('*/*/create', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));
		$form->setUseContainer(true);

		$emptySelect = array(
			-1 => array ( "value"=> "", "label"=> "" )
		);

		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amgiftcard')->__('Information'),
		));
		$fieldset->addType('price', 'Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price');

		$fieldset->addField('code_set_id', 'select',
			array(
				'label' 	=> Mage::helper('amgiftcard')->__('Code Set'),
				'required'	=>true,
				'values'	=> Mage::getModel('amgiftcard/source_giftCardCodeSet')->getAllOptions(),
				'name' 		=> 'code_set_id',
			)
		);

		$fieldset->addField('image_id', 'select',
			array(
				'label' 	=> Mage::helper('amgiftcard')->__('Image'),
				'required'	=> false,
				'values'	=> array_merge($emptySelect, Mage::getModel('amgiftcard/source_image')->getAllOptions()),
				'name'		=> 'image_id',
			)
		);

		$fieldset->addField('store_id', 'select',
			array(
				'label' 	=> Mage::helper('amgiftcard')->__('Store'),
				'values'	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
				'name' 		=> 'store_id',
				'required'	=> true,
			)
		);

		$fieldset->addField('status_id', 'select',
			array(
				'label' => Mage::helper('amgiftcard')->__('Status'),
				'required'=>true,
				'options'	=> Mage::getModel('amgiftcard/account')->getListStatuses(),
				'name' => 'status_id',
			)
		);

		$fieldset->addField('value', 'price',
			array(
				'label' => Mage::helper('amgiftcard')->__('Balance'),
				'required'=>true,
				'name' => 'value',
			)
		);

		$fieldset->addField('expired_date', 'date',
			array(
				'label' => Mage::helper('amgiftcard')->__('Expiry Date'),
				'name' => 'expired_date',
				'format'	=> 'MM/dd/yy HH:mm',
				'image'     => $this->getSkinUrl('images/grid-cal.gif'),
				'time'		=> true,
				//'value'		=> Mage::getModel('core/date')->date('Y-m-d H:i:s', Mage::registry('amgiftcard_account')->getExpiredDate()),
			)
		);

		$fieldset->addField('comment', 'textarea',
			array(
				'label' => Mage::helper('amgiftcard')->__('Comment'),
				'name' => 'comment',
			)
		);

		$fieldset = $form->addFieldset('send_information', array(
			'htmlId'	=> 'send_information',
			'legend'	=> Mage::helper('amgiftcard')->__('Send Information'),
		));

		$fieldset->addField('sender_name', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Sender Name'),
				'required'=>true,
				'name' => 'sender_name',
			)
		);

		$fieldset->addField('sender_email', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Sender Email'),
				'required'=>true,
				'name' => 'sender_email',
				'class' => 'validate-email',
			)
		);

		$fieldset->addField('recipient_name', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Recipient Name'),
				'required'=>true,
				'name' => 'recipient_name',
			)
		);

		$fieldset->addField('recipient_email', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Recipient Email'),
				'required'=>true,
				'name' => 'recipient_email',
				'class' => 'validate-email',
			)
		);

		$fieldset->addField('sender_message', 'textarea',
			array(
				'label' => Mage::helper('amgiftcard')->__('Sender Message'),
				'name' => 'sender_message',
			)
		);

		$fieldset->addField('date_delivery', 'date',
			array(
				'label' 	=> Mage::helper('amgiftcard')->__('Date Delivery'),
				'name' 		=> 'date_delivery',
				'format'	=> 'MM/dd/yy HH:mm',
				'image'     => $this->getSkinUrl('images/grid-cal.gif'),
				'time'		=> true,
				//'value'		=> Mage::getModel('core/date')->date('Y-m-d H:i:s', Mage::registry('amgiftcard_account')->getExpiredDate()),
			)
		);



		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);

		$form->setValues($data);
		$this->setForm($form);


		return parent::_prepareForm();
	}
}