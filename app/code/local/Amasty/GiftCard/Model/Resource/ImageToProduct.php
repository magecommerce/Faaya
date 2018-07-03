<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_ImageToProduct extends Amasty_GiftCard_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/image_to_product', 'image_id');
	}

	/**
	 * @param int $product_id
	 *
	 * @return $this
	 */
	public function deleteValues($product_id)
	{
		$condition = array(
			'product_id=?' => $product_id,
		);
		$this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
		return $this;
	}

	/**
	 * @param array $listValues
	 *
	 * @return $this
	 */
	public function insertAll(array $listValues)
	{
		$this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $listValues);
		return $this;
	}

	/**
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function loadImages($product_id)
	{
		$readAdapter = $this->_getReadAdapter();
		$query = $readAdapter->select()
			->from($this->getMainTable(), array(
				'image_id'
			))
			->where('product_id=:product_id');
		$bindParams = array(
			'product_id'   => $product_id,
		);
		return $readAdapter->fetchAll($query, $bindParams);
	}
}