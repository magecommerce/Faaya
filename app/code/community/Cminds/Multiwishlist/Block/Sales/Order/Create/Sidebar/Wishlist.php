<?php

/**
 * Adminhtml sales order create sidebar wishlist block.
 *
 */
class Cminds_Multiwishlist_Block_Sales_Order_Create_Sidebar_Wishlist
    extends Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Wishlist
{
    public function getItemCollection()
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return parent::getItemCollection();
        }

        $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        $collection = Mage::getModel('cminds_multiwishlist/item')->getCollection();
        $multiwishlistTableName = Mage::getSingleton('core/resource')
            ->getTableName('cminds_multiwishlist/multiwishlist');
        $collection
            ->getSelect()
            ->join(
                array('multiwishlist' => $multiwishlistTableName),
                'multiwishlist.wishlist_id = main_table.wishlist_id',
                array('multiwishlist.customer_id', 'multiwishlist.name as list_name')
            )
            ->where('customer_id = ?', $customerId);
        $this->setData('item_collection', $collection);

        return $collection;
    }

    /**
     * Retrieve all items.
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getItemCollection();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $item->setName($product->getName())
                ->setPrice($product->getFinalPrice(1))
                ->setTypeId($product->getTypeId());
        }

        return $items;
    }

    /**
     * Retrieve product identifier linked with item.
     *
     * @param   Mage_Wishlist_Model_Item $item
     *
     * @return  int
     */
    public function getProductId($item)
    {
        return $item->getProductId();
    }
}

