<?php

class Cminds_Multiwishlist_Model_Resource_Multiwishlist_Collection
    extends Mage_Wishlist_Model_Resource_Wishlist_Collection
{
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/multiwishlist');

        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            $this->_init('wishlist/wishlist');
        }
    }
}
