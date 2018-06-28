<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$range = readRange();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$writeConnection->query('TRUNCATE lowestdiamond');


//$query = 'SELECT id,variant_id,item_id,total_dia_wt,price FROM wizardmaster WHERE construction = "PRESET" LIMIT 0,15';
$query = 'SELECT id,variant_id,item_id,total_dia_wt,price FROM wizardmaster WHERE IF(center_diamond =1, special_character LIKE  "%C%", 1) AND IF(matchpair=1, special_character LIKE  "%M%", 1 ) AND construction =  "PRESET"';
//$query = 'SELECT id,pid,variant_id,item_id,total_dia_wt,price FROM wizardmaster WHERE construction = "PRESET" and pid=5521';
$products = $readConnection->fetchAll($query);
foreach ($products as $product) {
      $lowestfound = lowestPriceDiamond($product['variant_id']);
      $lowestMatchfound = lowestMatchDiamond($product['variant_id']);

      $setQuery = array();
      $multiprice = $product['price'];
      if(!empty($lowestfound)){
            $multiprice = $multiprice+$lowestfound['lowest_diamond'];
            $lowestfound['lowest_diamond'] = ($lowestfound['lowest_diamond'] == '')?0:$lowestfound['lowest_diamond'];
            $lowestfound['lowest_diamond_id'] = ($lowestfound['lowest_diamond_id'] == '')?0:$lowestfound['lowest_diamond_id'];
            $setQuery[]= "lowest_diamond_id=".$lowestfound['lowest_diamond_id'].",lowest_diamond_price = ".$lowestfound['lowest_diamond'];
      }else{
          $setQuery[]= "lowest_diamond_id=0,lowest_diamond_price = 0";
      }

      if(!empty($lowestMatchfound)){
          $multiprice = $multiprice+$lowestMatchfound['price'];
          $setQuery[]= "matchpair_id='".$lowestMatchfound['lowest_diamond_id']."' ";
      }else{
          $setQuery[]= "matchpair_id=0";
      }
      $allcarat = getAllCarat($product['item_id'],$range);
      $setQuery[]= "allcarat='".serialize($allcarat)."' ";
      $diaRange = '';
      foreach ($allcarat as $kys=>$diawt) {
            if($diawt[1] == $product['total_dia_wt']){
                  $diaRange = $kys;
                  break;
            }
      }
      $metalColor = getMetalColorArr($product['item_id'],$diaRange);
      $setQuery[]= "allmetal='".serialize($metalColor)."' ";
      $setQuery[]= "multiprice='".$multiprice."' ";

      $setQueryString = implode(",", $setQuery);
      $updatecarat = "update wizardmaster set ".$setQueryString." where id=".$product['id'];

      $writeConnection->query($updatecarat);
}

echo "all data updated";exit;






function getMetalColorArr($itemid,$totalDia){
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');

        $totalDia = explode('-', $totalDia);
        $query = 'select metal_color,karat,pid from wizardmaster where IF(center_diamond =1, special_character LIKE  "%C%", 1) AND IF(matchpair=1, special_character LIKE  "%M%", 1 ) AND construction =  "PRESET" and item_id ="'.$itemid.'" and total_dia_wt >= "'.$totalDia[0].'" and total_dia_wt <= "'.$totalDia[1].'" and product_type!="DIAMOND" order by metal_color,karat';
        $collection = $readConnection->fetchAll($query);
        $metalColor = array();
        foreach ($collection as $value) {
          $keylbl = str_replace(' ', '-', $value['metal_color']).'_'.$value['karat'];
          $metalColor[$keylbl] = array('mkarat'=>$value['karat'].' '.$value['metal_color'],'pid'=>$value['pid'],'itemid'=>$itemid);
        }
        return $metalColor;
}


function getAllCarat($itemId,$range){
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');

      $query = 'SELECT DISTINCT(total_dia_wt) FROM wizardmaster where IF(center_diamond =1, special_character LIKE  "%C%", 1) AND IF(matchpair=1, special_character LIKE  "%M%", 1 ) AND construction =  "PRESET" and item_id='.$itemId;
      $collection = $readConnection->fetchCol($query);
      sort($collection);
      $totalDiaArr = array();
      foreach ($collection as $value) {
        $diaValue = getTotalDiaWtRange($value,$range);
        $diaKey = array_keys($diaValue);
        if($diaValue[$diaKey[0]] != ''){
          $totalDiaArr[$diaKey[0]] = $diaValue[$diaKey[0]];
        }
      }
      return $totalDiaArr;
}


function getTotalDiaWtRange($totalDiaWtLabel,$ranges){
  foreach($ranges as $range=>$value){
     if($totalDiaWtLabel >=$value[0] && $totalDiaWtLabel <=$value[1]){
         $totalDiaWt[$value[0]."-".$value[1]] = array($range,$totalDiaWtLabel);
         return $totalDiaWt;
     }
  }

}
function lowestPriceDiamond($variantid){
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');

      $relation = 'select variant_refsmryid from wizardrelation where variant_id = '.$variantid.' and type="material" and special_character ="C"';
      $relation = $readConnection->fetchCol($relation);
      $relation = '"'.implode('","', $relation).'"';
      //$relationmaster = 'select pid AS lowest_diamond_id,MIN(price) AS lowest_diamond from wizardmaster where sku IN ('.$relation.')';
      $relationmaster = 'select pid AS lowest_diamond_id,price AS lowest_diamond from wizardmaster where sku IN ('.$relation.') and status=1 order by price';
      $relationmaster = $readConnection->fetchRow($relationmaster);
      return $relationmaster;
}

function lowestMatchDiamond($variantid){
      $resource = Mage::getSingleton('core/resource');
      $readConnection = $resource->getConnection('core_read');

      $relation = 'select variant_refsmryid from wizardrelation where variant_id = '.$variantid.' and type="material" and special_character ="M"';
      $relation = $readConnection->fetchCol($relation);
      if(!empty($relation)){
        $relation = '"'.implode('","', $relation).'"';
        //$relationmaster = 'select pid AS lowest_diamond_id,price AS price,group_code from wizardmaster where sku IN ('.$relation.') order by price';
        $groupCode = 'select group_code from wizardmaster where sku IN ('.$relation.') order by price';
        $groupCode = $readConnection->fetchOne($groupCode);
        if($groupCode){
          $relationmaster = 'select pid,price from wizardmaster where group_code = "'.$groupCode.'" and status=1 order by price LIMIT 0,2';
          $relationmaster = $readConnection->fetchAll($relationmaster);
          if(count($relationmaster) > 1){
              $priceset = 0;
              foreach ($relationmaster as $value) {
                $priceset += $value['price'];
              }
              $relationmaster['lowest_diamond_id'] = $relationmaster[0]['pid'];
              $relationmaster['price'] = $priceset;
              return $relationmaster;
          }
        }
      }
      return array();
}



function readRange(){
        $file = Mage::getBaseDir().DS.'filterrangecsv'.DS.'filterrange.csv';
        $csvObject = new Varien_File_Csv();
        try {
            $count = 1;
            $data = $csvObject->getData($file);
            $rangeCollection = array();
            foreach($data as $row){
                if($count == 1){ $count++; continue; }
                $rangeCollection[$row[2]] = array($row[0],$row[1]);
            }
           return $rangeCollection;
        } catch (Exception $e) {
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
            return false;
        }
}