<?php

class Cminds_Multiwishlist_Model_Item extends Mage_Wishlist_Model_Item
    implements Mage_Catalog_Model_Product_Configuration_Item_Interface
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('cminds_multiwishlist/item');
    }

    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('cminds_multiwishlist/item_option')->setData($option)
                ->setItem($this);
        } else if ($option instanceof Cminds_Multiwishlist_Model_Item_Option) {
            $option->setItem($this);
        } else if ($option instanceof Varien_Object) {
            $option = Mage::getModel('cminds_multiwishlist/item_option')->setData($option->getData())
                ->setProduct($option->getProduct())
                ->setItem($this);
        } else {
            Mage::throwException(Mage::helper('sales')->__('Invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($option->getCode());
        if ($exOption) {
            $exOption->addData($option->getData());
        } else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @throws Mage_Core_Exception
     * @param Mage_Checkout_Model_Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     */
    public function addToCart(Mage_Checkout_Model_Cart $cart, $delete = false)
    {
        $product = $this->getProduct();
        $storeId = $this->getStoreId();

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
        }

        $buyRequest = $this->getBuyRequest();

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
            && Mage::registry('pre-define-lowest-price')
            && !$this->validateBundleRequest($buyRequest)
        ) {
            $this->prepareBestPriceRequest($buyRequest, $product);
        }

        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }

    /**
     * Determine if Bundle Item is configured to Add to Cart.
     *
     * @param $buyRequest
     * @return bool
     */
    public function validateBundleRequest($buyRequest)
    {
        if (!isset($buyRequest['bundle_option'])
            || !isset($buyRequest['bundle_option_qty'])
            || !isset($buyRequest['product'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Prepare buyRequest with the best Price items.
     *
     * @param $buyRequest
     * @param $product
     */
    public function prepareBestPriceRequest(&$buyRequest, $product)
    {
        $newRequest = array();
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );

        $optionsData = array();
        foreach ($selectionCollection as $selection) {
            $optionsData[$selection->getOptionId()][$selection->getSelectionId()] = $selection->getPrice();
        }

        $bestPrice = array();
        foreach ($optionsData as $key => $optionData) {
            $bestPrice[$key] = array_keys($optionData, min($optionData));
        }

        foreach ($bestPrice as $key => $data) {
            $newRequest['bundle_option'][$key] = $data[0];
        }

        $buyRequest['bundle_option'] = $newRequest['bundle_option'];
        $buyRequest['product'] = $product->getId();
    }
}
