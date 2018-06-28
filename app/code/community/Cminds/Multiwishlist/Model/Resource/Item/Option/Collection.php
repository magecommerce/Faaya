<?php

class Cminds_Multiwishlist_Model_Resource_Item_Option_Collection
    extends Mage_Wishlist_Model_Resource_Item_Option_Collection
{

    /**
     * Define resource model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/item_option');
    }
}
