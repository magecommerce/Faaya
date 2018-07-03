<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Source_PriceType extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
	{
		$_helper = Mage::helper('amgiftcard');
		return array(
			array('value' => Amasty_GiftCard_Model_GiftCard::PRICE_TYPE_EQUAL, 'label' => $_helper->__('the whole card amount')),
			array('value' => Amasty_GiftCard_Model_GiftCard::PRICE_TYPE_PERCENT, 'label' => $_helper->__('percent of card card amount')),
		);
	}
}