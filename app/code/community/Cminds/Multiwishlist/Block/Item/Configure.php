<?php

class Cminds_Multiwishlist_Block_Item_Configure extends Mage_Wishlist_Block_Item_Configure
{
    /**
     * Returns wishlist item being configured
     *
     * @return Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item
     */
    protected function getWishlistItem()
    {
        return Mage::registry('multiwishlist_item');
    }

    /**
     * Configure product view blocks
     *
     * @return Mage_Wishlist_Block_Item_Configure
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return $this;
        }

        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $url = Mage::helper('cminds_multiwishlist')->getAddToCartUrl($this->getWishlistItem());
            $block->setCustomAddToCartUrl($url);
        }

        return $this;
    }
}
