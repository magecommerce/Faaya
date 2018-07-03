<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Source_GiftCardType extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
	{
		$_helper = Mage::helper('amgiftcard');
		return array(
			array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_VIRTUAL, 'label' => $_helper->__('Virtual')),
			array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED, 'label' => $_helper->__('Printed')),
			array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED, 'label' => $_helper->__('Combined')),
		);
	}
}