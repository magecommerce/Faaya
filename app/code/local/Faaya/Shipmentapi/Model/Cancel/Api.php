<?php
class Faaya_Shipmentapi_Model_Cancel_Api extends Mage_Api_Model_Resource_Abstract
{
  public function cancelorder($orderId)
  {
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    if($order->canCancel()) {
      $order->cancel()->save();
      die('your order cancelled');
    }else{
      die('something went wrong');
    }

  }
}