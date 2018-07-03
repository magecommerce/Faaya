<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Edit_Tab_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_account_edit_tab_order';
		$this->_blockGroup = 'amgiftcard';
		$this->_headerText = '';
		parent::__construct();
	}

	public function getButtonsHtml($area = null)
	{
		$this->removeButton('add');
		parent::getButtonsHtml($area);
	}
}