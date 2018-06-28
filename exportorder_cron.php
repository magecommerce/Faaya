<?php  
ini_set('memory_limit', '512M');    
require_once('app/Mage.php'); //Path to Magento
umask(0);
echo "<pre>";
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);      
Mage::getModel('orderapi/orderapi')->exportOrders();