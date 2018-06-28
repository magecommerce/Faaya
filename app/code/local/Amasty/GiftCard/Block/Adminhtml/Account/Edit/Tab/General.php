<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);

		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amgiftcard')->__('Information'),
		));

		$fieldset->addType('price', 'Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price');

		$fieldset->addField('order_number', 'link',
			array(
				'label' => Mage::helper('amgiftcard')->__('Order ID'),
				'name' => 'order_number',
				'href' => $this->getUrl('*/sales_order/view', array('order_id' => Mage::registry('amgiftcard_account')->getOrderId())),
				'value'	=> Mage::registry('amgiftcard_account')->getOrderNumber(),
			)
		);

		$fieldset->addField('code', 'label',
			array(
				'label' => Mage::helper('amgiftcard')->__('Gift Card Code'),
				'name' => 'code',
				'value'	=> Mage::registry('amgiftcard_account')->getCode(),
				//'text' => 'ads',
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

		$fieldset->addField('website_id', 'select',
			array(
				'label' => Mage::helper('amgiftcard')->__('Website'),
				'required'=>true,
				'options'	=> Mage::helper('amgiftcard')->getWebsitesOptions(),
				'name' => 'website_id',
			)
		);


		$fieldset->addField('initial_value', 'label',
			array(
				'label' => Mage::helper('amgiftcard')->__('Initial code value'),
				'name' => 'initial_value',
				//'text' => 'ads',
			)
		);
		/*Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price*/
		$fieldset->addField('current_value', 'price',
			array(
				'label' => Mage::helper('amgiftcard')->__('Current Balance'),
				'required'=>true,
				'name' => 'current_value',
			)
		);

		$fieldset->addField('expired_date', 'date',
			array(
				'label' => Mage::helper('amgiftcard')->__('Expiry Date'),
				'name' => 'expired_date',
				'format'	=> 'MM/dd/yy HH:mm',
				'image'     => $this->getSkinUrl('images/grid-cal.gif'),
				'time'		=> true
			)
		);

		$fieldset->addField('comment', 'textarea',
			array(
				'label' => Mage::helper('amgiftcard')->__('Comment'),
				'name' => 'comment',
			)
		);

		$values = Mage::registry('amgiftcard_account')->getData();

		$form->setValues($values);
		$this->setForm($form);

		return parent::_prepareForm();
	}
}