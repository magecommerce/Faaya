<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_Account_Collection extends Amasty_GiftCard_Model_Resource_Abstract_Collection
{
	protected function _construct()
	{
		$this->_init('amgiftcard/account');
	}

	/**
	 * @return $this
	 */
	public function joinOrder()
	{
		$this->getSelect()->joinLeft(
			array('order' => $this->getTable('sales/order_grid')),
			'order.entity_id = main_table.order_id',
			array('order_number' => 'order.increment_id', 'order.store_id')
		);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function joinCode()
	{
		$this->getSelect()->join(
			array('code' => $this->getTable('amgiftcard/code')),
			'code.code_id = main_table.code_id',
			array('code' => 'code.code')
		);
		return $this;
	}


	public function savedByCustomer($customer_id)
	{
		$this->getSelect()->join(
			array('customer_card' => $this->getTable('amgiftcard/customer_card')),
			'customer_card.account_id = main_table.account_id AND customer_card.customer_id = '.$customer_id,
			false
		);
		return $this;
	}



}