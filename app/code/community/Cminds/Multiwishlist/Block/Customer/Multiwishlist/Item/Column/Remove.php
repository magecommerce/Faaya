<?php

/**
 * Class Cminds_Multiwishlist_Block_Customer_Multiwishlist_Item_Column_Remove
 */
class Cminds_Multiwishlist_Block_Customer_Multiwishlist_Item_Column_Remove
    extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Remove
{


    /**
     * @param Mage_Catalog_Model_Product|Cminds_Multiwishlist_Model_Item $item
     * @return mixed
     */
    public function getItemRemoveUrl($item)
    {
        return Mage::helper('cminds_multiwishlist')->getRemoveUrl($item);
    }

}