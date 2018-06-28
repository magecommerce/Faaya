<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('amgiftcard')->__('Template Information'));
	}


	protected function _beforeToHtml()
	{
		$tabs = array(
			'general'		=> 'General Information',
			//'codesList'		=> 'Codes List',
		);

		foreach ($tabs as $code => $label){
			$label = Mage::helper('amgiftcard')->__($label);
			$content = $this->getLayout()->createBlock('amgiftcard/adminhtml_image_edit_tab_' . $code)
				->setTitle($label)
				->toHtml();

			$this->addTab($code, array(
				'label'     => $label,
				'content'   => $content,
			));
		}

		return parent::_beforeToHtml();
	}
}