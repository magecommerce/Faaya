<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * @param string $code
     * @return mixed
     */
    protected function _prepareCustomOption($code)
    {
        return Mage::helper('amgiftcard/catalog_product_configuration')->prepareCustomOption($this->getItem(), $code);
    }

    /**
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        return Mage::helper('amgiftcard/catalog_product_configuration')->getGiftcardOptions($this->getItem());
    }

    /**
     * @return array
     */
    public function getOptionList()
    {
        return Mage::helper('amgiftcard/catalog_product_configuration')->getOptions($this->getItem());
    }


	/**
	 * Get product thumbnail image
	 *
	 * @return Mage_Catalog_Model_Product_Image
	 */
	public function getProductThumbnail()
	{
		$option = $this->getItem()->getOptionByCode('am_giftcard_image');
		if ($option) {
			$value = $option->getValue();
			if ($value) {
				$image = Mage::getModel('amgiftcard/image')->load($value);

				if($image->getId())
				{
					return $this->helper('amgiftcard/product_image')->init($this->getProduct(), 'thumbnail', $image->getImage());
				}
			}
		}

		if (!is_null($this->_productThumbnail)) {
			return $this->_productThumbnail;
		}
		return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail');
	}
}
