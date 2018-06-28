<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Cda_Wizard_CartController extends Mage_Checkout_CartController{
     protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('ring-product-id');
        if(!$productId){
            $productId = (int) $this->getRequest()->getParam('product');
        }
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
}