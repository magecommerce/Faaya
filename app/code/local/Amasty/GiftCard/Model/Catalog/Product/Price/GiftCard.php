<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Catalog_Product_Price_GiftCard extends Mage_Catalog_Model_Product_Type_Price
{
	protected $_minMaxAmount = array();

	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return float
	 */
	public function getPrice($product)
	{
		if ($product->getData('price')) {
			return $product->getData('price');
		} else {
			return 0;
		}
	}

	/**
	 * @param integer $qty
	 * @param Mage_Catalog_Model_Product $product
	 * @return float
	 */
	public function getFinalPrice($qty=null, $product)
	{
		$finalPrice = $product->getPrice();
		if ($product->hasCustomOptions()) {
			$customOption = $product->getCustomOption('am_giftcard_amount');
			if ($customOption) {
				$customValue = $customOption->getValue();
				$giftCardPriceType = $product->getAmGiftcardPriceType();
				if(is_null($giftCardPriceType)) {
					$giftCardPriceType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'am_giftcard_price_type', Mage::app()->getStore()->getId());
				}
				if($giftCardPriceType == Amasty_GiftCard_Model_GiftCard::PRICE_TYPE_PERCENT) {
					$pricePercent = $product->getAmGiftcardPricePercent();
					if(is_null($pricePercent)) {
						$pricePercent = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'am_giftcard_price_percent', Mage::app()->getStore()->getId());
					}
					$customValue *= $pricePercent / 100;
					$customValue = Mage::app()->getStore()->roundPrice($customValue);
				}
				$finalPrice += $customValue;
			}
		}
		$finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);

		$product->setData('final_price', $finalPrice);
		return max(0, $product->getData('final_price'));
	}

	public function getMinMaxAmount($product)
	{
		if(!isset($this->_minMaxAmount[$product->getId()])) {
			$min = $max = null;
			foreach($this->getAmounts($product) as $amount) {
				$min = is_null($min) ? $amount['value'] : min($min, $amount['value']);
				$max = is_null($max) ? $amount['value'] : max($max, $amount['value']);
			}

			if($product->getAmAllowOpenAmount())
			{
				if(is_null($min)) {
					$min = 0;
				}

				$min = min($min, (float)$product->getAmOpenAmountMin());

				$max = $product->getAmOpenAmountMax() ? max($max, $product->getAmOpenAmountMax()) : $max;
			}

			$this->_minMaxAmount[$product->getId()] = array('min'=>$min, 'max' => $max);
		}
		return $this->_minMaxAmount[$product->getId()];

	}

	public function getAmounts($product)
	{
		$prices = $product->getData('am_giftcard_prices');

		if (is_null($prices)) {
			if ($attribute = $product->getResource()->getAttribute('am_giftcard_prices')) {
				$attribute->getBackend()->afterLoad($product);
				$prices = $product->getData('am_giftcard_prices');
			}
		}

		return ($prices) ? $prices : array();
	}

	public function isMultiAmount($product)
	{
		$minMaxAmount = $this->getMinMaxAmount($product);

		return $minMaxAmount['min'] != $minMaxAmount['max'] || is_null($minMaxAmount['max']);
	}
}
