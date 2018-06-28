<?php

class Cminds_Multiwishlist_Block_Share_Email_Items extends Mage_Wishlist_Block_Share_Email_Items
{
    /**
     * @return Cminds_Multiwishlist_Model_Resource_Item_Collection $collection
     */
    public function getWishlistItems()
    {
        $collection = Mage::registry('multiwishlist')->getItemCollection();
        return $collection;
    }

}
