<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Source_Image extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
	{

		return Mage::getModel('amgiftcard/image')->getCollection()->addFieldToFilter('active', Amasty_GiftCard_Model_Image::STATUS_ACTIVE)->toOptionArray();
	}
}