<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_Image_Collection extends Amasty_GiftCard_Model_Resource_Abstract_Collection
{
	protected function _construct()
	{
		$this->_init('amgiftcard/image');
	}

	public function toOptionArray()
	{
		return $this->_toOptionArray('image_id', 'title');
	}
}