<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Customer_Cards extends Mage_Core_Block_Template
{
	public function getCards()
	{
		return Mage::registry('customer_am_gift_cards');
	}
}