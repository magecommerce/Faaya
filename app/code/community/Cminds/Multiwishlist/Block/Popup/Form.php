<?php

/**
 * Class Cminds_Multiwishlist_Block_Popup_Form
 */
class Cminds_Multiwishlist_Block_Popup_Form extends Cminds_Multiwishlist_Block_Popup
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cminds_multiwishlist/popup/form.phtml');
    }

    /**
     * @return Cminds_Multiwishlist_Model_Resource_Multiwishlist_Collection $collection
     */
    public function getItems()
    {
        $customerId = Mage::getSingleton('customer/session')->getId();
        $collection = Mage::getModel('cminds_multiwishlist/multiwishlist')->getCollection();
        $collection->addFieldToFilter('customer_id', array('eq' => array($customerId)));

        return $collection;
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        $url = Mage::getUrl('multiwishlist/index/add');

        if($this->getIsCartPage()) {
            $url = Mage::getUrl('multiwishlist/index/fromcart');

        }

        return $url;
    }

}
