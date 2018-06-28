<?php

class Cminds_Multiwishlist_Model_Resource_Item_Collection extends Mage_Wishlist_Model_Resource_Item_Collection
{

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cminds_multiwishlist/item');
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * Add options to items
     *
     * @return Cminds_Multiwishlist_Model_Resource_Item_Collection
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection Cminds_Multiwishlist_Model_Resource_Item_Option_Collection */
        $optionCollection = Mage::getModel('cminds_multiwishlist/item_option')->getCollection();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item Cminds_Multiwishlist_Model_Item */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return Cminds_Multiwishlist_Model_Resource_Item_Collection
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()
            ->join(
                array('multiwishlist' => $this->getTable('cminds_multiwishlist/multiwishlist')),
                'main_table.wishlist_id = multiwishlist.wishlist_id',
                array('name as wishlist_name')
            )
            ->where('multiwishlist.customer_id = ?', $customerId);
        return $this;
    }
}
