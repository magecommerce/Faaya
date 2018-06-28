<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_Multiwishlist_Adminhtml_MultiwishlistController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer');
    }

    /**
     * @param $key
     * @return $this
     */
    protected function _initCustomer($key)
    {
        $customerId = (int)$this->getRequest()->getParam($key);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     *Tab customer_edit_tab_wishlists action.
     */
    public function gridAction()
    {
        $this->_initCustomer('id');

        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            if ($itemId = (int) $this->getRequest()->getParam('delete')) {
                try {
                    Mage::getModel('cminds_multiwishlist/item')->load($itemId)
                        ->delete();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'cminds_multiwishlist/adminhtml_customer_edit_tab_wishlists',
                    'customer_edit_tab_wishlists'
                )
                ->toHtml()
        );
    }
}
