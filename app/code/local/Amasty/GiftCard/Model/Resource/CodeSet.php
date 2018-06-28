<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_CodeSet extends Amasty_GiftCard_Model_Resource_Abstract
{
	protected function _construct()
	{
		$this->_init('amgiftcard/code_set', 'code_set_id');
	}
}