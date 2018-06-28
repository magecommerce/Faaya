<?php
require 'app/Mage.php';
Mage::app();
$order = Mage::getModel('sales/order')->loadByIncrementId('100000112');
$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId('100000023');

$emailTemplate = Mage::getModel('core/email_template')->loadByCode('Custom New Shipment');

// These variables can be used in the template file by doing {{ var some_custom_variable }}
//$emailTemplateVariables = array('order' => $order,'invoice'=>$invoive);
$emailTemplateVariables = array('order' => $order,'shipment'=>$shipment);

$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
echo "<pre/>";print_r($processedTemplate);exit;
$emailTemplate->setSenderName('Joe Bloggs');
$emailTemplate->setSenderEmail('test@test.com');
$emailTemplate->setTemplateSubject("Here is your subject");

$emailTemplate->send('recipient@test.com', 'Joanna Bloggs', $emailTemplateVariables);
