<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Checkout_Cart_GiftCard extends Mage_Checkout_Block_Cart_Abstract
{

    public function getFormActionUrl()
    {
        return $this->getUrl('amgiftcard/cart/add', array('_secure' => $this->isSecure()));
    }

    public function getCheckCardAjaxUrl()
    {
        return $this->getUrl('amgiftcard/cart/ajax', array('_secure' => $this->isSecure()));
    }

    protected function isSecure()
    {
        return Mage::app()->getRequest()->isSecure();
    }
}
