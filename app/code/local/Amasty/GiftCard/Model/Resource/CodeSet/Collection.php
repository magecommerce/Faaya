<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_CodeSet_Collection extends Amasty_GiftCard_Model_Resource_Abstract_Collection
{
	protected function _construct()
	{
		$this->_init('amgiftcard/codeSet');
	}

	public function toOptionArray()
	{
		return $this->_toOptionArray('code_set_id', 'title');
	}

	public function joinCodes($fields = '*')
	{
		$this->getSelect()
			->joinLeft(
				array('amgiftcard_code'=>$this->getTable('amgiftcard/code')),
				'amgiftcard_code.code_set_id = main_table.code_set_id',
				$fields
			);
		return $this;
	}

	public function joinCodeQtyAndUnused()
	{
		$fields = array(
			'qty'	=> new Zend_Db_Expr('COUNT(amgiftcard_code.code_id)'),
			'qty_unused'	=> new Zend_Db_Expr('SUM(IF(amgiftcard_code.used='.Amasty_GiftCard_Model_Code::STATE_UNUSED.',1,0))')
		);
		$this->joinCodes($fields)->getSelect()->group('main_table.code_set_id');
		return $this;
	}

	public function addFilterQty($qty)
	{
		$field = new Zend_Db_Expr("
		(SELECT COUNT(*) FROM `{$this->getTable('amgiftcard/code')}` AS where_amgiftcard_code WHERE where_amgiftcard_code.code_set_id = main_table.code_set_id)");
		$this->addFieldToFilter($field, $qty);
		return $this;
	}

	public function addFilterQtyUnused($qty)
	{
		$field = new Zend_Db_Expr("
		(SELECT SUM(IF(where_amgiftcard_code.used='.Amasty_GiftCard_Model_Code::STATE_UNUSED.',1,0)) FROM `{$this->getTable('amgiftcard/code')}` AS where_amgiftcard_code WHERE where_amgiftcard_code.code_set_id = main_table.code_set_id)");
		$this->addFieldToFilter($field, $qty);
		return $this;
	}
}