<?php

/**
 * Class Cminds_Multiwishlist_Block_Customer_Multiwishlist_List
 */
class Cminds_Multiwishlist_Block_Customer_Multiwishlist_List extends Mage_Core_Block_Template
{

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this
            ->getLayout()
            ->createBlock('page/html_pager', 'multiwishlist.customer.wishlist.pager')
            ->setCollection($this->getItems());

        $this->setChild('pager', $pager);
        return $this;
    }

    /**
     * @return Cminds_Multiwishlist_Model_Resource_Multiwishlist_Collection
     */
    public function getItems()
    {
        $customerId = Mage::getSingleton('customer/session')->getId();

        $items = Mage::getModel('cminds_multiwishlist/multiwishlist')->getCollection();
        $items->addFieldToFilter('customer_id', array('eq' => $customerId));

        return $items;
    }

    /**
     * @param $wishlistId
     * @return string
     */
    public function getRemoveUrl($wishlistId)
    {
        $url = Mage::getUrl(
            'cminds_multiwishlist/index/removeWishlist',
            array('wishlist_id' => $wishlistId)
        );

        return $url;
    }

    /**
     * @param $wishlistId
     * @return string
     */
    public function getViewUrl($wishlistId)
    {
        $url = Mage::getUrl(
            'cminds_multiwishlist/index/view',
            array('wishlist_id' => $wishlistId)
        );

        return $url;
    }

}