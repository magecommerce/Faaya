<?php
require 'app/Mage.php';
Mage::app();
ini_set('memory_limit', '-1');

$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('image', array("eq"=>'no_selection'));

foreach ($products as $product) {
  $gallery_images = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages();

  if(count($gallery_images)>0){
      $path = $gallery_images->toArray()["items"][0]["file"];
      $product->setSmallImage($path);
      $product->setThumbnail($path);
      $product->setImage($path);
      try {
        $product->save();
      } catch (Exception $e) {
        throw new Exception($e);
      }
      //$product->save();
      echo $product->getId();
      echo "\r\n";
  }

}
?>