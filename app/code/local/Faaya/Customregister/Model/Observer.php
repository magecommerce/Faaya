<?php
class Faaya_Customregister_Model_Observer {
   public function saveorder(Varien_Event_Observer $observer)
    {
        /*$controllerAction = $observer->getEvent()->getControllerAction();
        $response = $controllerAction->getResponse();
        $paymentResponse = Mage::helper('core')->jsonDecode($response->getBody());
        if (!isset($paymentResponse['error']) || !$paymentResponse['error']) {
            $controllerAction->getRequest()->setParam('form_key', Mage::getSingleton('core/session')->getFormKey());
            $controllerAction->getRequest()->setPost('agreement', array_flip(Mage::helper('checkout')->getRequiredAgreementIds()));
            $controllerAction->saveOrderAction();
            $orderResponse = Mage::helper('core')->jsonDecode($response->getBody());
            if ($orderResponse['error'] === false && $orderResponse['success'] === true) {
                if (!isset($orderResponse['redirect']) || !$orderResponse['redirect']) {*/
        //$orderResponse['redirect'] = Mage::getUrl('*/*/success');
        /* }
                $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($orderResponse));
            }
        }*/
        $controllerAction = $observer->getEvent()->getControllerAction();
        $response = $controllerAction->getResponse();
        $paymentResponse = Mage::helper('core')->jsonDecode($response->getBody());
        if (!isset($paymentResponse['error']) || !$paymentResponse['error']) {
            $controllerAction->getRequest()->setParam('form_key', Mage::getSingleton('core/session')->getFormKey());
            $controllerAction->getRequest()->setPost('agreement', array_flip(Mage::helper('checkout')->getRequiredAgreementIds()));
            $controllerAction->saveOrderAction();
            $orderResponse = Mage::helper('core')->jsonDecode($response->getBody());
            if ($orderResponse['error'] === false && $orderResponse['success'] === true) {
                if (!isset($orderResponse['redirect']) || !$orderResponse['redirect']) {
                    $orderResponse['redirect'] = Mage::getUrl('*/*/success');
                }
                $controllerAction->getResponse()->setBody(Mage::helper('core')->jsonEncode($orderResponse));
            }
        }
    }
    public function saveQuoteAfter(Varien_Event_Observer $observer){
       $quote = $observer->getQuote();
       Mage::log($quote->getData(), null, 'before-quote-in-data.log');
       $data = Mage::app()->getRequest()->getParams();
       $specialInstruction = Mage::app()->getRequest()->getParam('special-instruction');
       $shippingSpecialInstruction = Mage::app()->getRequest()->getParam('shipping-special-instruction');
       $deliveryCall = Mage::app()->getRequest()->getParam('delivery_call');
       $shippingDeliveryCall = Mage::app()->getRequest()->getParam('shipping_delivery_call');
       Mage::log($data, null, 'quote-in-observer.log');
       Mage::log($deliveryCall, null, 'before-deliveryCall.log');
       if($specialInstruction!= ""){
            $quote->setSpecialInstruction($specialInstruction);
       }
       if($shippingSpecialInstruction!= ""){
            $quote->setSpecialInstruction($shippingSpecialInstruction);
       }
       if($deliveryCall != ""){
           $quote->setDeliveryCall($deliveryCall);
       }
       if($shippingDeliveryCall != ""){
           $quote->setDeliveryCall($shippingDeliveryCall);
       }
       Mage::log($deliveryCall, null, 'after-deliveryCall.log');
       //$quote->save();
       Mage::log($quote->getData(), null, 'after-quote-in-data.log');
    }
}

