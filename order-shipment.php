<?php
//ini_set('memory_limit', '-1');
ini_set('memory_limit', '512M'); 
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); //Path to Magento
umask(0);
echo "<pre>";
Mage::app('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$orderId = $_GET['orderid'];
$order = Mage::getModel('sales/order')->load($orderId); // 26 //22 // 20(6) , //14 , //19(2)
if($orderId){
    $qty=array();
    foreach($order->getAllItems() as $eachOrderItem){
     $Itemqty=0;
     $Itemqty = $eachOrderItem->getQtyOrdered()
                - $eachOrderItem->getQtyShipped()
                - $eachOrderItem->getQtyRefunded()
                - $eachOrderItem->getQtyCanceled();
     $qty[$eachOrderItem->getId()]=$Itemqty;
     
    }
     
    /*
    echo "<pre>";
    print_r($qty);
    echo "</pre>";
    */
    /* check order shipment is prossiable or not */
     
    $email=true;
    $includeComment=true;
    $comment="test Shipment";
     
    if ($order->canShip()) {
             /* @var $shipment Mage_Sales_Model_Order_Shipment */
     /* prepare to create shipment */
     $shipment = $order->prepareShipment($qty);
       if ($shipment) {
       $shipment->register();
       $shipment->addComment($comment, $email && $includeComment);
       $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                    $shipment->sendEmail($email, ($includeComment ? $comment : ''));
                } catch (Mage_Core_Exception $e) {
     var_dump($e);
                }
     
       }
     
    }
}
else{
    echo "please enter Order ID";
}
