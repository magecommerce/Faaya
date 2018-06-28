<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Source_ProductType
{
	public function toOptionArray()
	{
		return Mage::getModel('catalog/product_type')->getAllOptions();
	}
}