<?php

class Cminds_Multiwishlist_Model_Multiwishlist extends Mage_Wishlist_Model_Wishlist
{

    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/multiwishlist');
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            $this->_init('wishlist/wishlist');
        }
    }

    public function createNew($customerId, $newWishlistNam)
    {
        $this->setCustomerId($customerId);
        $this->setSharingCode($this->_getSharingRandomCode());
        $this->setName($newWishlistNam);
        $this->save();

        return $this;
    }

    public function getItemCollection()
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return parent::getItemCollection();
        }

        if ($this->_itemCollection) {
            return $this->_itemCollection;
        }

        /** @var $currentWebsiteOnly boolean */
        $currentWebsiteOnly = !Mage::app()->getStore()->isAdmin();
        $this
            ->_itemCollection = Mage::getResourceModel('cminds_multiwishlist/item_collection')
            ->addWishlistFilter($this)
            ->addStoreFilter($this->getSharedStoreIds($currentWebsiteOnly))
            ->setVisibilityFilter();

        if (Mage::app()->getStore()->isAdmin()) {
            $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            $this->_itemCollection->setWebsiteId($customer->getWebsiteId());
            $this->_itemCollection->setCustomerGroupId($customer->getGroupId());
        }

        return $this->_itemCollection;
    }

    protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1, $forciblySetQty = false)
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return parent::_addCatalogProduct($product, $qty = 1, $forciblySetQty = false);
        }

        $item = null;
        foreach ($this->getItemCollection() as $_item) {
            if ($_item->representProduct($product)) {
                $item = $_item;
                break;
            }
        }

        if ($item === null) {
            $storeId = $product->hasWishlistStoreId() ? $product->getWishlistStoreId() : $this->getStore()->getId();
            $item = Mage::getModel('cminds_multiwishlist/item');
            $item->setProductId($product->getId())
                ->setWishlistId($this->getId())
                ->setAddedAt(now())
                ->setStoreId($storeId)
                ->setOptions($product->getCustomOptions())
                ->setProduct($product)
                ->setQty($qty)
                ->save();
            Mage::dispatchEvent('cminds_multiwishlist_item_add_after', array('wishlist' => $this));
            if ($item->getId()) {
                $this->getItemCollection()->addItem($item);
            }
        } else {
            $qty = $forciblySetQty ? $qty : $item->getQty() + $qty;
            $item->setQty($qty)
                ->save();
        }
        $this->addItem($item);

        return $item;
    }
}