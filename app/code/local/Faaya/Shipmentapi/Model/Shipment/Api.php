<?php
class Faaya_Shipmentapi_Model_Shipment_Api extends Mage_Api_Model_Resource_Abstract
{
  public function createshipment($orderData)
  {
    $orderData = json_decode($orderData);
      if($orderData->incrementId){
          $order = Mage::getModel('sales/order')->loadByIncrementId($orderData->incrementId);
          $store = Mage::getModel('core/store')->load(1);
          $itemsarray = array();
          $shipmentId = 0;
          if($orderData->items && count($orderData->items) > 0)
          {
              $orderItems = $order->getAllItems();
              foreach ($orderItems as $orderItem) {
                $options = Mage::getResourceModel('sales/quote_item_option_collection');
                $options->addItemFilter($orderItem->getData('quote_item_id'));
                foreach ($options as $option) {
                    if ($option->getCode() == 'setting') {
                        $values = unserialize($option->getValue()); // to array object
                        if(in_array($values['group']['sid'], $orderData->items)){
                            $itemsarray[$orderItem->getId()] =  1;
                        }else{
                            $itemsarray[$orderItem->getId()] =  0;
                        }
                    }
                }
              }
              $shipmentId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsarray ,'Partially create shipment programatically' ,0,0);
              if($orderData->tracking && count($orderData->tracking)>1){
                $trackmodel = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId,'fedex',$orderData->tracking[0],$orderData->tracking[1]);
              }
              if($shipmentId > 0){
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $shipmentTemplate =  Mage::getStoreConfig('sales_email/shipment/template');
                $emailTemplate = Mage::getModel('core/email_template')->load($shipmentTemplate);
                $vars = array('order' => $order,'shipment'=>$shipment,'store'=>$store);
                $emailTemplate->getProcessedTemplate($vars);
                $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', 1));
                $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', 1));
                $emailTemplate->send($order->getCustomerEmail(),$order->getCustomerFirstname(), $vars);
              }
              return json_encode('Partially Shipment for this order created');

          }elseif($order->canShip()){
            $itemQty =  $order->getItemsCollection()->count();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
            $shipment = new Mage_Sales_Model_Order_Shipment_Api();
            $shipmentId = $shipment->create($order->getIncrementId());
            if($orderData->tracking && count($orderData->tracking)>1){
              $trackmodel = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId,'fedex',$orderData->tracking[0],$orderData->tracking[1]);
            }
            if($shipmentId > 0){
              $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
              $shipmentTemplate =  Mage::getStoreConfig('sales_email/shipment/template');
              $emailTemplate = Mage::getModel('core/email_template')->load($shipmentTemplate);
              $vars = array('order' => $order,'shipment'=>$shipment,'store'=>$store);
              $emailTemplate->getProcessedTemplate($vars);
              $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', 1));
              $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', 1));
              $emailTemplate->send($order->getCustomerEmail(),$order->getCustomerFirstname(), $vars);
            }
            return json_encode('Shipment for this order created');
          }
      }
  }
}