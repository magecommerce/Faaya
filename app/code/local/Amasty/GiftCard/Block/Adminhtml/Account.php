<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_account';
		$this->_blockGroup = 'amgiftcard';
		$this->_headerText = Mage::helper('amgiftcard')->__('Gift Code Accounts');
		parent::__construct();
	}
}