<?php
class Faaya_Cancelapi_Model_Cancelapi_Api extends Mage_Api_Model_Resource_Abstract
{
    public function cancelapi($orderData)
    {
        $orderData = json_decode($orderData);
        if($orderData->incrementId){
          $order = Mage::getModel('sales/order')->loadByIncrementId($orderData->incrementId);
          $itemsarray = array();
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
                                $itemsarray['qtys'][$orderItem->getId()] =  1;
                            }
                        }
                    }
                  }
              }else{
                    $orderItems = $order->getAllItems();
                    foreach ($orderItems as $orderItem) {
                    $options = Mage::getResourceModel('sales/quote_item_option_collection');
                    $options->addItemFilter($orderItem->getData('quote_item_id'));
                        foreach ($options as $option) {
                            if ($option->getCode() == 'setting') {
                                $values = unserialize($option->getValue()); // to array object
                                $itemsarray['qtys'][$orderItem->getId()] =  1;
                            }
                        }
                    }
              }
            $service = Mage::getModel('sales/service_order', $order);
            $data = $itemsarray;
            $message = 'Partially creaditmemo for this order created';
            try {
                $service->prepareCreditmemo($data)->register()->save();
            } catch (Exception $e) {
                $message = 'We can not refund without invoice';
                //throw new Exception($e);
            }
            return json_encode($message);
        }
    }
}
