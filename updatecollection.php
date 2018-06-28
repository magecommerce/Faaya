<?php
require 'app/Mage.php';
Mage::app();


$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');


$collection = Mage::getModel('catalog/product')->getCollection();
//$collection->addAttributeToSelect('*');
$collection->addAttributeToFilter('collection', array('neq' => ''));
foreach ($collection as $product) {
    $collect = Mage::Helper('wizard')->getAttributeValue('collection',$product->getData('collection'));
    $updatecarat = "update wizardmaster set collection='".$collect."' where pid=".$product->getId();
    $writeConnection->query($updatecarat);
}
echo "All updated";exit;
?>