<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_Code extends Amasty_GiftCard_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/code', 'code_id');
	}

	public function massSaveCodes($listCodes, $params)
	{
		if(count($listCodes) > 0) {
			$insert = array();
			foreach($listCodes as $code) {
				$insert[] = array_merge(array(
					'code' => $code,
				), $params);
			}
			$this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $insert);
		}

	}

	/**
	 * @param Mage_Core_Model_Abstract 	$object
	 * @param int						$codeSetId
	 *
	 * @return $this
	 */
	public function loadFreeCode(Mage_Core_Model_Abstract $object, $codeSetId)
	{
		$readAdapter = $this->_getReadAdapter();
		$query = $readAdapter->select()
			->from($this->getMainTable())
			->where('used=0')
			->where('enabled=1')
			->where('code_set_id=:code_set_id')
			->limit(1);
		$bindParams = array(
			'code_set_id'   => $codeSetId
		);

		$object->setData($readAdapter->fetchRow($query, $bindParams));

		return $this;
	}

	public function exists($code)
	{
		$readAdapter = $this->_getReadAdapter();
		$bindParams = array('code'   => $code);
		$query = "SELECT COUNT(*) FROM {$this->getMainTable()} WHERE code = :code";

		return (bool)$readAdapter->fetchOne($query, $bindParams);
	}

	public function countByTemplate($template)
	{
		$readAdapter = $this->_getReadAdapter();
		/*$query = $readAdapter->select()
			->from($this->getMainTable())
			->columns('COUNT(*)')
			->where('code LIKE :code');
		$bindParams = array(
			'code'   => $template
		);*/
		$template = $readAdapter->quote($template);
		$query = "SELECT COUNT(*) FROM {$this->getMainTable()} WHERE code LIKE {$template}";
		//var_dump($query);

		return $readAdapter->fetchOne($query);
	}

}