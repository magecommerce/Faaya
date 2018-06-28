<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->setUseContainer(false);




		$fieldset = $form->addFieldset('general', array(
			'htmlId'	=> 'general_information',
			'legend'	=> Mage::helper('amgiftcard')->__('General Information'),
		));

		$fieldset->addType('block', 'Amasty_GiftCard_Block_Form_Element_Block');

		$fieldset->addField('code_pos_x', 'hidden',
			array(
				'name'	=> 'code_pos_x'
			)
		);

		$fieldset->addField('code_pos_y', 'hidden',
			array(
				'name'	=> 'code_pos_y'
			)
		);

		$fieldset->addField('title', 'text',
			array(
				'label' => Mage::helper('amgiftcard')->__('Image Title'),
				'required'=>true,
				//'value' => $_event->getEventTitle(),
				'name' => 'title',
			)
		);

		$fieldset->addField('active', 'select',
			array(
				'label' => Mage::helper('amgiftcard')->__('Status'),
				'required'=>true,
				'options'	=> array(
					0	=> 'Inactive',
					1	=> 'Active',
				),
				//'value' => $_event->getEventTitle(),
				'name' => 'active',
			)
		);

		$fieldset->addField('image', 'file',
			array(
				'label' => Mage::helper('amgiftcard')->__('Upload Image'),
				//'required'=>true,

				//'value' => $_event->getEventTitle(),
				'name' => 'image',
			)
		);

		$fieldset->addField('image_position', 'block',
			array(
				'label' => Mage::helper('amgiftcard')->__('Please, specify code position'),
				//'required'=>true,

				//'value' => $_event->getEventTitle(),
				'block' => $this->getLayout()->createBlock('amgiftcard/adminhtml_image_edit_image'),
				'image'	=> Mage::registry('amgiftcard_image'),
			)
		);

		$form->setValues(Mage::registry('amgiftcard_image')->getData());
		$this->setForm($form);

		return parent::_prepareForm();
	}
}