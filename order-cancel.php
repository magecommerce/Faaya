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
$order = Mage::getModel('sales/order')->load($orderId);
if($orderId){
    if ($order->canCancel()) {
    try {
        $order->cancel();
        // remove status history set in _setState
        $order->getStatusHistoryCollection(true);
        $order->save();
    } catch (Exception $e) {
        Mage::logException($e);
    }
}
}
else{
    echo "please enter Order ID";
}
