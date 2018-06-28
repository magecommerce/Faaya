<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_CodeSet_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);

		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amgiftcard')->__('General Information'),
		));

		$fieldset->addField('title', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Code Set Title'),
				'required'=>true,
				//'value' => $_event->getEventTitle(),
				'name' => 'title',
			)
		);

		$fieldset2 = $form->addFieldset('generate_codes', array(
			'htmlId'	=> 'generate_codes',
			'legend'	=> Mage::helper('amgiftcard')->__('Generate Codes'),
		));

		$text = "<p class='note'>
		{L} - letter, {D} - digit<br>
		e.g. PROMO_{L}{L}{D}{D}{D} results in PROMO_DF627</p>
		";

		$fieldset2->addField('template', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Code Set Template'),
				'required'=>false,
				//'value' => $_event->getEventTitle(),
				'name' => 'template',
				'after_element_html' => $text
			)
		);

		$fieldset2->addField('qty', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Gift Code Qty'),
				'required'=>false,
				//'value' => $_event->getEventTitle(),
				'name' => 'qty',
			)
		);

		$fieldset3 = $form->addFieldset('import_codes', array(
			'htmlId'	=> 'import_codes',
			'legend'	=> Mage::helper('amgiftcard')->__('Import Codes'),
		));

		$text = "<p class='note'>
		Each gift code on a new line</p>
		";
		$fieldset3->addField('csv', 'file',
			array(
				'label' => Mage::helper('amgiftcard')->__('CSV File'),
				//'value' => $_event->getAdditionalInformation(),
				'name' => 'csv',
				//'after_element_html' => $image
				'after_element_html' => $text,
			)
		);


		$form->setValues(Mage::registry('amgiftcard_codeSet')->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}
}