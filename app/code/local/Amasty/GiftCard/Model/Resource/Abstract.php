<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
abstract class Amasty_GiftCard_Model_Resource_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
	public function massDelete($listIds)
	{
		if(!is_array($listIds)) {
			return $this;
		}
		if(count($listIds) == 0) {
			return $this;
		}

		$condition = array(
			$this->getIdFieldName() . ' IN (?)' => $listIds,
		);

		$this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
		return $this;
	}

	/**
	 * Retrieve select object for load object data
	 *
	 * @param mixed $field
	 * @param mixed $value
	 * @param Mage_Core_Model_Abstract $object
	 * @return Zend_Db_Select
	 */
	protected function _getLoadSelect($field, $value, $object)
	{
		if(is_array($field) || is_array($value)) {
			if(is_array($field) && is_array($value)) {
				$listFieldsValues = array_combine($field, $value);
			} elseif(is_array($field)) {
				$listFieldsValues = $field;
			} else {
				$listFieldsValues = $value;
			}

			$select = $this->_getReadAdapter()->select()
				->from($this->getMainTable());
			foreach($listFieldsValues as $field=>$value) {
				$field  = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
				$select->where($field . '=?', $value);
			}
		} else {
			$select = parent::_getLoadSelect($field, $value, $object);
		}
		return $select;
	}
}