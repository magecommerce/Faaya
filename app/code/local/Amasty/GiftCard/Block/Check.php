<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Check extends Mage_Core_Block_Template
{
	/**
	 * @return Amasty_GiftCard_Model_Account
	 */
	public function getCard()
	{
		return Mage::registry('amgiftcard_code_account');
	}
}