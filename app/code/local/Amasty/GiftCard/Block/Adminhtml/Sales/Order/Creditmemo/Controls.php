<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Sales_Order_Creditmemo_Controls extends Mage_Core_Block_Template
{
	public function hasRefundToGiftCard()
	{
		if ($this->_getCreditmemo()->getOrder()->getAmGiftCardsAmount()) {
			return true;
		}

		return false;
	}

	public function getReturnValue()
	{
		return (float) $this->_getCreditmemo()->getOrder()->getAmGiftCardsAmount();
	}

	protected function _getCreditmemo()
	{
		return Mage::registry('current_creditmemo');
	}
}