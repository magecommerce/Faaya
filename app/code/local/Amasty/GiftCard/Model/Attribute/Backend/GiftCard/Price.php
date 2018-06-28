<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Attribute_Backend_GiftCard_Price extends Mage_Catalog_Model_Product_Attribute_Backend_Price
{
	/**
	 * @return Amasty_GiftCard_Model_Resource_Attribute_Backend_GiftCard_Price
	 */
	protected function _getResource()
	{
		return Mage::getResourceSingleton('amgiftcard/attribute_backend_giftCard_price');
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return $this
	 */
	public function afterSave($product)
	{
		$attributeName = $this->getAttribute()->getName();
		if($product->getOrigData($attributeName) == $product->getData($attributeName)) {
			return $this;
		}
		$this->_getResource()->deleteAllPrices($product, $this->getAttribute());
		$listPrices = $product->getData($this->getAttribute()->getName());

		if (!is_array($listPrices)) {
			return $this;
		}
		$listValues = array();
		foreach ($listPrices as $row) {
			if(empty($row['price']) || !empty($row['delete'])) {
				continue;
			}
			$listValues[] = array(
				'website_id'		=> $row['website_id'],
				'value'				=> $row['price'],
				'attribute_id'		=> $this->getAttribute()->getId(),
				'product_id'		=> $product->getId(),
				'entity_type_id'	=> $product->getEntityTypeId(),
			);

		}
		if($listValues) {
			$this->_getResource()->insertPrices($listValues); //insertMultiple
		}

		return $this;
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return $this
	 */
	public function afterLoad($product)
	{
		$listPrices = $this->_getResource()->loadPrices($product, $this->getAttribute());

		foreach ($listPrices as $key=>&$price) {
			if ($price['website_id'] == 0) {
				$rate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getBaseCurrencyCode());
				if ($rate) {
					$price['website_value'] = $price['value']/$rate;
				} else {
					unset($listPrices[$key]);
				}
			} else {
				$price['website_value'] = $price['value'];
			}
		}
		unset($price);
		$product->setData($this->getAttribute()->getName(), $listPrices);
		return $this;
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return $this
	 */
	public function afterDelete($product)
	{
		$this->_getResource()->deleteAllPrices($product, $this->getAttribute());
		return $this;
	}
}