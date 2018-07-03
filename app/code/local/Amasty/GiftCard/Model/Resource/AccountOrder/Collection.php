<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_AccountOrder_Collection extends Amasty_GiftCard_Model_Resource_Abstract_Collection
{
	public function _construct()
	{
		$this->_init('amgiftcard/accountOrder');
	}


	/**
	 * @return $this
	 */
	public function joinOrder()
	{
		$this->getSelect()
			->join(
				array('order' => $this->getTable('sales/order_grid')),
				'order.entity_id = main_table.order_id',
				$orderItemFields = array('*')
			);

		return $this;
	}
}