<?php

class Cminds_Multiwishlist_Model_Item_Option extends Mage_Wishlist_Model_Item_Option
    implements Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/item_option');
    }

}
