<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_AccountOrder extends Amasty_GiftCard_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/account_order', 'account_order_id');
	}
}