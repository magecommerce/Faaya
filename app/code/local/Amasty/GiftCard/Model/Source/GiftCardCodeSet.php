<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Source_GiftCardCodeSet extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
	{
		$empty = array(
			array('value'=>'', 'label'=>'')
		);
		return array_merge($empty, Mage::getModel('amgiftcard/codeSet')->getCollection()->toOptionArray());
	}
}