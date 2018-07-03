<?php
class Faaya_Orderapi_Helper_Data extends Mage_Core_Helper_Abstract{
    public function getInvoicePdfHtml($invoice,$order){
          echo "Invoice #" .  $invoice->getIncrementId();
          echo "<br/>";
          echo "Order #" .  $order->getIncrementId();
          echo "<br>";
          echo "Order Date " .  $order->getCreatedAt();
          echo "<br/>";
          $billingAddress = $order->getBillingAddress();
          $shippingAddress = $order->getShippingAddress();
          $bilingContactName = $billingAddress['prefix'] ." " .$billingAddress['firstname'] . " " . $billingAddress['lastname']; 
          $bilingAddress = $billingAddress['street'];
          $bilingCity = $billingAddress['city'];
          $bilingCounty = $billingAddress['region'];
          $bilingPostCode = $billingAddress['postcode'];
          $bilingCountry = $billingAddress['country_id'];
          $bilingTelephone = $billingAddress['telephone'];
          echo "<br/>";
          echo "Ship to";
          echo "<br/>";
          $shippingContactName = $shippingAddress['prefix'] ." " .$shippingAddress['firstname'] . " " . $shippingAddress['lastname']; 
          $shippingAddress = $shippingAddress['street'];
          $shippingCity = $shippingAddress['city'];
          $shippingCounty = $shippingAddress['region'];
          $shippingPostCode = $shippingAddress['postcode'];
          $shippingCountry = $shippingAddress['country_id']; 
          $shippingTelephone = $shippingAddress['telephone']; 
          echo "<br/>";
          echo "Payment Method";
          echo "<br/>"; 
          echo $payment_method = $order->getPayment()->getMethodInstance()->getTitle();       
          
    }
}
    