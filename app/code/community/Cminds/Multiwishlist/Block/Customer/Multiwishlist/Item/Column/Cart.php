<?php

class Cminds_Multiwishlist_Block_Customer_Multiwishlist_Item_Column_Cart
    extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Cart
{

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|Mage_Catalog_Model_Product|Cminds_Multiwishlist_Model_Item $item
     * @return  string
     */
    public function getItemAddToCartUrl($item)
    {
        return Mage::helper('cminds_multiwishlist')->getAddToCartUrl($item);
    }

    /**
     * Returns item configure url in wishlist
     *
     * @param Mage_Catalog_Model_Product|Cminds_Multiwishlist_Model_Item $product
     *
     * @return string
     */
    public function getItemConfigureUrl($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $id = $product->getWishlistItemId();
        } else {
            $id = $product->getId();
        }
        $params = array('id' => $id);

        return $this->getUrl('multiwishlist/index/configure/', $params);
    }

}