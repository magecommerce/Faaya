<?php

/**
 * Wishlist item option resource model
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cminds_Multiwishlist_Model_Resource_Item_Option extends Mage_Wishlist_Model_Resource_Item_Option
{
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/item_option', 'option_id');
    }
}
