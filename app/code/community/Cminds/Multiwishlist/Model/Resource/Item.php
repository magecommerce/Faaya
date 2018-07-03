<?php

class Cminds_Multiwishlist_Model_Resource_Item extends Mage_Wishlist_Model_Resource_Item
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/item', 'wishlist_item_id');
    }
}
