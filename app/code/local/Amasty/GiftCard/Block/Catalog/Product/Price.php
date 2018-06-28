<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{

	public function getMinAmount($product = null)
	{
		$product = $this->_prepareProduct($product);
		$minMaxAmount =  $product->getPriceModel()->getMinMaxAmount($product);
		return $minMaxAmount['min'];
	}

	public function getMinPrice($product = null)
	{
		$product = $this->_prepareProduct($product);
		$minPrice = $this->getMinAmount($product);
		$minPrice = $this->_preparePrice($minPrice, $product);
		return $minPrice;
	}

	public function isMultiAmount($product = null)
	{
		$product = $this->_prepareProduct($product);
		return $product->getPriceModel()->isMultiAmount($product);
	}

	protected function _prepareProduct($product = null)
	{
		if(is_null($product))
		{
			$product = $this->getProduct();
		}
		return $product;
	}

	protected function _preparePrice($price, $product)
	{
		if($product->getAmGiftcardPriceType() == Amasty_GiftCard_Model_GiftCard::PRICE_TYPE_PERCENT) {
			$price *= $product->getAmGiftcardPricePercent()/100;
		}

		return $price;
	}
}
