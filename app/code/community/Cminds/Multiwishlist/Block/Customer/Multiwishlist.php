<?php

/**
 * Class Cminds_Multiwishlist_Block_Customer_Multiwishlist
 */
class Cminds_Multiwishlist_Block_Customer_Multiwishlist extends Mage_Wishlist_Block_Customer_Wishlist
{

    /**
     * Retrieve Multi Wishlist model
     *
     * @return Cminds_Multiwishlist_Model_Multiwishlist
     */
    protected function _getWishlist()
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return parent::_getWishlist();
        }

        $wishlistId = Mage::registry('wishlist_id');
        $wishlist = Mage::getModel('cminds_multiwishlist/multiwishlist')->load($wishlistId);

        return $wishlist;
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('multiwishlist/index/index');
    }

}
