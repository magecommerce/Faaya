<?php
class Faaya_Customregister_Block_Onepage extends Mage_Checkout_Block_Onepage{
    protected function _getStepCodes()
    {
        if ($this->getQuote()->isVirtual()) {
            return array('login', 'billing', 'payment');
        }
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment');
    }
}
