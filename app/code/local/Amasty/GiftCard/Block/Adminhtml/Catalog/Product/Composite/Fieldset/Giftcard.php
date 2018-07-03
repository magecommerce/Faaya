<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Giftcard extends Amasty_GiftCard_Block_Catalog_Product_View_Type_GiftCard
{
	public function getIsLastFieldset()
	{
		if ($this->hasData('is_last_fieldset')) {
			return $this->getData('is_last_fieldset');
		} else {
			return !$this->getProduct()->getOptions();
		}
	}
}