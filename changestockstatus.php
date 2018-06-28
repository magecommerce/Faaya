<?php
require 'app/Mage.php';
Mage::app();
$file = 'stockout/Book4.csv';
$csv = new Varien_File_Csv();
$data = $csv->getData($file);

for($i=1; $i<count($data); $i++)
{
    $product = Mage::getModel('wizard/productupdate')->updateProduct($data[$i][0]);
}
echo "All data updated";exit;


?>