<?php

/**
 * Order create model
 */
class Cminds_Multiwishlist_Model_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Handle data sent from sidebar.
     *
     * @param array $data
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function applySidebarData($data)
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return parent::applySidebarData($data);
        }

        if (isset($data['add_order_item'])) {
            foreach ($data['add_order_item'] as $orderItemId => $value) {
                /* @var $orderItem Mage_Sales_Model_Order_Item */
                $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
                $item = $this->initFromOrderItem($orderItem);
                if (is_string($item)) {
                    Mage::throwException($item);
                }
            }
        }
        if (isset($data['add_cart_item'])) {
            foreach ($data['add_cart_item'] as $itemId => $qty) {
                $item = $this->getCustomerCart()->getItemById($itemId);
                if ($item) {
                    $this->moveQuoteItem($item, 'order', $qty);
                    $this->removeItem($itemId, 'cart');
                }
            }
        }
        if (isset($data['add_wishlist_item'])) {
            foreach ($data['add_wishlist_item'] as $itemId => $qty) {
                $item = Mage::getModel('wishlist/item')
                    ->loadWithOptions($itemId, 'info_buyRequest');
                if ($item->getId()) {
                    $this->addProduct($item->getProduct(), $item->getBuyRequest()->toArray());
                }
            }
        }
        if (isset($data['add'])) {
            foreach ($data['add'] as $productId => $qty) {
                $this->addProduct($productId, array('qty' => $qty));
            }
        }
        if (isset($data['remove'])) {
            foreach ($data['remove'] as $itemId => $from) {
                $collection = Mage::getResourceModel('cminds_multiwishlist/item_collection')
                    ->addFieldToFilter('wishlist_item_id', $itemId);
                foreach ($collection as $item) {
                    $item->delete();
                }
            }
        }
        if (isset($data['empty_customer_cart']) && (int)$data['empty_customer_cart'] == 1) {
            $this->getCustomerCart()->removeAllItems()->collectTotals()->save();
        }

        return $this;
    }
}
