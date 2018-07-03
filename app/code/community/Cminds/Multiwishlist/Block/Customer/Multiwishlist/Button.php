<?php

class Cminds_Multiwishlist_Block_Customer_Multiwishlist_Button
    extends Mage_Wishlist_Block_Customer_Wishlist_Button
{
    /**
     * Retrieve current wishlist
     *
     * @return Cminds_Multiwishlist_Model_Multiwishlist
     */
    public function getWishlist()
    {
        return Mage::helper('cminds_multiwishlist')->getWishlist();
    }
}
