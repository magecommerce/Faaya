<?php

class Cminds_Multiwishlist_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Customer Multi Wishlist instance
     *
     * @var Cminds_Multiwishlist_Model_Multiwishlist
     */
    protected $_wishlist = null;

    public function isEnabled()
    {
        $configuration = Mage::getStoreConfig('wishlist/cminds_multiwishilst/enabled');

        return $configuration;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param Mage_Catalog_Model_Product|Cminds_Multiwishlist_Model_Item $item
     * @return Mage_Core_Model_Store
     */
    public function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof Cminds_Multiwishlist_Model_Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else if ($product->hasUrlDataObject()) {
                $storeId = $product->getUrlDataObject()->getStoreId();
            }
        }
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retrieve wishlist by logged in customer
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    public function getWishlist()
    {
        if (is_null($this->_wishlist)) {
            if (Mage::registry('shared_multiwishlist')) {
                $this->_wishlist = Mage::registry('shared_multiwishlist');
            } elseif (Mage::registry('multiwishlist')) {
                $this->_wishlist = Mage::registry('multiwishlist');
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve URL for removing item from wishlist
     *
     * @param Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('multiwishlist/index/remove',
            array(
                'item' => $item->getWishlistItemId(),
                Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey()
            )
        );
    }

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|Mage_Catalog_Model_Product|Mage_Wishlist_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        $continueUrl = Mage::helper('core')->urlEncode(
            Mage::getUrl('*/*/*', array(
                '_current' => true,
                '_use_rewrite' => true,
                '_store_to_url' => true,
            ))
        );
        $params = array(
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $continueUrl,
            Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey()
        );

        return $this->_getUrlStore($item)->getUrl('multiwishlist/index/cart', $params);
    }

    /**
     * Retrieve url for updating product in wishlist
     *
     * @param Mage_Catalog_Model_Product|Cminds_Multiwishlist_Model_Item $item
     *
     * @return  string|bool
     */
    public function getUpdateUrl($item)
    {
        $itemId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $itemId = $item->getWishlistItemId();
        }
        if ($item instanceof Cminds_Multiwishlist_Model_Item) {
            $itemId = $item->getId();
        }

        if ($itemId) {
            return $this->_getUrl('multiwishlist/index/updateItemOptions', array('id' => $itemId));
        }

        return false;
    }

    /**
     * Determine if pre-define the Bundle Options with the product with best price.
     *
     * @return bool
     */
    public function fillUpBundleOptions()
    {
        $config = Mage::getStoreConfig('wishlist/cminds_multiwishilst/choose_lowest_price');

        return (bool) $config;
    }

    /**
     * @param $wishlistId
     * @return string
     */
    public function getListUrl($wishlistId)
    {
        $url = Mage::getUrl('multiwishlist/index/view', array('wishlist_id' => $wishlistId));

        return $url;
    }


}