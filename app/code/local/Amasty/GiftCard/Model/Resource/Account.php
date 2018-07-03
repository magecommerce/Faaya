<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_Account extends Amasty_GiftCard_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/account', 'account_id');
	}

	/**
	 * @param Mage_Core_Model_Abstract 	$object
	 * @param int						$codeSetId
	 *
	 * @return $this
	 */
	public function loadByCode(Mage_Core_Model_Abstract $object, $code)
	{
		$readAdapter = $this->_getReadAdapter();
		$query = $readAdapter->select()
			->from($this->getMainTable())
			->join(
				array('code' => $this->getTable('amgiftcard/code')),
				'code.code_id = '.$this->getMainTable().'.code_id',
				false
			)
			->where('code.code=:code')
			->limit(1);
		$bindParams = array(
			'code'   => $code
		);

		$object->setData($readAdapter->fetchRow($query, $bindParams));

		return $this;
	}
}