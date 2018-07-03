<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Catalog_Product_Helper_Form_Config_AllowMessage extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Config
{
	/**
	 * Get config value data
	 *
	 * @return mixed
	 */
	protected function _getValueFromConfig()
	{
		return Mage::getStoreConfig(Amasty_GiftCard_Helper_Data::XPATH_CONFIG_GIFT_CARD_ALLOW_MESSAGE);
	}
}