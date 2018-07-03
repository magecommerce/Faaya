<?php

class Cminds_Multiwishlist_Block_Customer_Sidebar_Multiwishlist
    extends Mage_Wishlist_Block_Customer_Sidebar
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('cminds_multiwishlist/sidebar/multiwishlist.phtml');
    }
    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__('My Wishlists <small>(%d)</small>', count($this->getWishlistCollection()));
    }

    public function getWishlists()
    {
        $collection = $this->getWishlistCollection();

        $collection->setCurPage(1)
            ->setPageSize(2);

        return $collection;
    }

    public function hasWishlists()
    {
        return count($this->getWishlistItems()) > 0;
    }

    public function getItemsCollection($wishlist)
    {
        $collection = $wishlist->getItemCollection();
        $collection->setCurPage(1)
            ->setPageSize(2)
            ->setInStockFilter(true);

        return $collection;
    }

    public function getWishlistCollection()
    {
        $customerId = Mage::getSingleton('customer/session')->getId();

        $collection = Mage::getModel('cminds_multiwishlist/multiwishlist')->getCollection();
        $collection->addFieldToFilter('customer_id', array('eq' => $customerId));

        return $collection;
    }

    public function getIndexUrl()
    {
        $url = Mage::getUrl('multiwishlist/index/index');

        return $url;
    }

}