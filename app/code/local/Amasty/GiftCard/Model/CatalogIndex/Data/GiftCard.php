<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_CatalogIndex_Data_GiftCard extends Mage_CatalogIndex_Model_Data_Simple
{
	public function getTypeCode()
	{
		return Amasty_GiftCard_Model_Catalog_Product_Type_GiftCard::TYPE_GIFTCARD_PRODUCT;
	}
}