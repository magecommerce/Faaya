<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_AccountOrder extends Amasty_GiftCard_Model_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/accountOrder');
	}

	public function loadByOrder($order_id, $account_id)
	{
		$this->load(array('order_id'=>$order_id, 'account_id'=>$account_id));
		return $this;
	}
}