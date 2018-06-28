<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Helper_Product_Image extends Mage_Catalog_Helper_Image
{
	/**
	 * Initialize Helper to work with Image
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $attributeName
	 * @param mixed $imageFile
	 * @return Mage_Catalog_Helper_Image
	 */
	public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile=null)
	{


		$this->_reset();
		$this->_setModel(Mage::getModel('amgiftcard/product_image'));
		$this->_getModel()->setDestinationSubdir($attributeName);
		$this->setProduct($product);

		$this->setWatermark(
			Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image")
		);
		$this->setWatermarkImageOpacity(
			Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity")
		);
		$this->setWatermarkPosition(
			Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position")
		);
		$this->setWatermarkSize(
			Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size")
		);

		if ($imageFile) {
			$this->setImageFile($imageFile);
		} else {
			// add for work original size
			$this->_getModel()->setBaseFile($this->getProduct()->getData($this->_getModel()->getDestinationSubdir()));
		}
		return $this;
	}
}