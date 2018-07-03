<?php

class Cminds_Multiwishlist_Model_Resource_Multiwishlist
    extends Mage_Wishlist_Model_Resource_Wishlist
{
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/multiwishlist', 'wishlist_id');

        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            $this->_init('wishlist/wishlist', 'wishlist_id');
        }
    }
}
