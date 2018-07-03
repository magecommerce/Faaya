<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Attribute_Backend_GiftCard_Image extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

	/**
	 * @return Amasty_GiftCard_Model_Resource_Image
	 */
	protected function _getResource()
	{
		return Mage::getResourceSingleton('amgiftcard/imageToProduct');
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
		$this->_getResource()->deleteValues($product->getId(), $this->getAttribute());
		$listImages = $product->getData($this->getAttribute()->getName());

		if (!is_array($listImages)) {
			return $this;
		}
		$listValues = array();
		foreach ($listImages as $imageId) {
			$listValues[] = array(
				'image_id'		=> $imageId,
				'product_id'		=> $product->getId(),
			);
		}
		$this->_getResource()->insertAll($listValues); //insertMultiple

		return $this;
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return $this
	 */
	public function afterLoad($product)
	{
		$listImages = $this->_getResource()->loadImages($product->getId());

		foreach ($listImages as $key=>&$image) {
			$image = $image['image_id'];
		}
		unset($image);
		$product->setData($this->getAttribute()->getName(), $listImages);
		return $this;
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return $this
	 */
	public function afterDelete($product)
	{
		$this->_getResource()->deleteValues($product->getId());
		return $this;
	}
}