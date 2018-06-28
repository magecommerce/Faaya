<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); //Path to Magento
umask(0);
echo "<pre>";
Mage::app('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$zip = new ZipArchive;

//$path = "jsonfiles/*.json";
$path = glob("jsonfiles/*WEB_JSON.{zip}", GLOB_BRACE);
foreach ($path as $ir=>$value) {
    if(strrpos($value, 'LD_MATERIAL')){
        unset($path[$ir]);
    }
}

if(count($path) > 0){
    foreach ($path as $value) {
        $pathinfoMain  = pathinfo($value);
        $getMainFile = explode("_", $pathinfoMain['filename']);
        $ldFileName = 'jsonfiles/'.$getMainFile[0].'_LD_MATERIAL_WEB_JSON.zip';
        if(file_exists($ldFileName)){
            $res = $zip->open($value);
            if ($res === TRUE) {
              $zip->extractTo('jsonfiles/'.$getMainFile[0].'_files/');
              $zip->close();
            }
            $res = $zip->open($ldFileName);
            if ($res === TRUE) {
              $zip->extractTo('jsonfiles/'.$getMainFile[0].'_files/');
              $zip->close();
            }
            $mainFile = 'jsonfiles/'.$getMainFile[0].'_files/'.$getMainFile[0].'_WEB_JSON.json';
            $ldFile = 'jsonfiles/'.$getMainFile[0].'_files/'.$getMainFile[0].'_LD_MATERIAL.json';
            if(file_exists($mainFile) && file_exists($ldFile)){
                $time_start = microtime(true);
                $productData = file_get_contents($mainFile);
                $ldData = file_get_contents($ldFile);
                $result =  Mage::getModel('faayaapi/faayaapi_api')->import($productData,$ldData);
                $resultSet = json_decode($result);
                $time_end = microtime(true);
                $execution_time = ($time_end - $time_start)/60;
                echo '<b>File name:</b> '.$value."\n";
                echo '<b>Total Execution Time:</b> '.$execution_time.' Mins'."\n";
                rename($mainFile, 'jsonfiles/completed/'.$getMainFile[0].'_WEB_JSON_'.date('d-m-Y').'.json');
                rename($ldFile, 'jsonfiles/completed/'.$getMainFile[0].'_LD_MATERIAL-'.date('d-m-Y').'.json');
                rmdir('jsonfiles/'.$getMainFile[0].'_files/');
                unlink($value);
                unlink($ldFileName);
            }
        }
    }
}
die('All data upadted');
  /*$time_start = microtime(true);
  $productData = file_get_contents("jsonfiles/JSON-StockLoadTestingReport.json");
  $ldData = file_get_contents("jsonfiles/JSON-StockLoadTestingReport-ld.json");
  $result =  Mage::getModel('faayaapi/faayaapi_api')->import($productData,$ldData);
  $resultSet = json_decode($result);
  echo "<pre/>";print_r($resultSet);
  $time_end = microtime(true);
  $execution_time = ($time_end - $time_start)/60;
  echo '<b>Total Execution Time:</b> '.$execution_time.' Mins';*/
  //print_r($resultSet);
