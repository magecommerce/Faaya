<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_image';
		$this->_blockGroup = 'amgiftcard';
		$this->_headerText = Mage::helper('amgiftcard')->__('Gift Card Images');
		parent::__construct();
	}

	public function getCreateUrl()
	{
		return $this->getUrl('*/*/edit');
	}
}