<?php
class Faaya_Layernavigation_Block_Layernavigation extends Mage_Catalog_Block_Product_List{

    protected $_readRange;
    protected $_limit;
    protected $_resource;
    protected $_readConnection;
    protected $_lowestDiamond = array();
    protected $_totalDiawt = array();

    protected function _construct(){
      $this->_resource = Mage::getSingleton('core/resource');
      $this->_readConnection = $this->_resource->getConnection('core_read');
      $this->_readRange = $this->readRange();
      $this->_limit = 5;
      Mage::getSingleton('core/session')->setLimitData($this->_limit);
    }

    public function getLoadCollection($cid){
      $cid = Mage::Helper('layernavigation')->categoryId($cid);

      if($cid>0){
        $query = 'SELECT * FROM wizardmaster WHERE category_id='.$cid.' and is_basevariant = 1 and IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" group by variant_id order by multiprice';
      }else{
        $query = 'SELECT * FROM wizardmaster WHERE is_basevariant = 1 and IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and collection !="" group by variant_id order by multiprice';
        //$query = 'SELECT * FROM wizardmaster WHERE construction =  "PRESET" and collection !="" group by variant_id order by multiprice';
      }

      $collection = $this->_readConnection->fetchAll($query);
      $minPrice = 0;
      $maxPrice = 0;
      foreach ($collection as $value) {
        $totalprice = $value['multiprice'];
        if($totalprice == ''){
          continue;
        }
        if($totalprice < $minPrice || $minPrice == 0){
          $minPrice = $totalprice;
        }
        if($totalprice > $maxPrice){
          $maxPrice = $totalprice;
        }
      }
      $getData = Mage::getSingleton('core/session')->getPresetData();
      $getData = unserialize($getData);
      $getData[$cid] = $collection;

      Mage::getSingleton('core/session')->setPresetData(serialize($getData));
      $dataarr = array('data'=>$collection,'minprice'=>$minPrice,'maxprice'=>$maxPrice);
      return json_encode($dataarr);
    }

    public function getAllCarat($itemId){
      $query = 'SELECT DISTINCT(total_dia_wt) FROM wizardmaster where item_id='.$itemId;
      $collection = $this->_readConnection->fetchCol($query);
      sort($collection);
      $totalDiaArr = array();
      foreach ($collection as $value) {
        $diaValue = $this->getTotalDiaWtRange($value);
        $diaKey = array_keys($diaValue);
        if($diaValue[$diaKey[0]] != ''){
          $this->_totalDiawt[$itemId][$diaKey[0]] =  $diaValue[$diaKey[0]];
          $totalDiaArr[$diaKey[0]] = $diaValue[$diaKey[0]];
        }
      }
      return $totalDiaArr;
    }
    public function lowestPriceDiamond($variantid){
      $relation = 'select variant_refsmryid from wizardrelation where variant_id = '.$variantid.' and type="material" and special_character ="C"';
      $relation = $this->_readConnection->fetchCol($relation);
      $relation = '"'.implode('","', $relation).'"';
      $relationmaster = 'select pid AS lowest_diamond_id,price AS lowest_diamond from wizardmaster where sku IN ('.$relation.') order by price asc';
      $relationmaster = $this->_readConnection->fetchRow($relationmaster);
      $this->_lowestDiamond[$variantid] = $relationmaster;
      return $relationmaster;
    }

    public function getRingfilter($categoryId){
      $filterlist = 'select * from wizardmaster where construction =  "PRESET" AND category_id='.$categoryId;
      $filterlist = $this->_readConnection->fetchAll($filterlist);
      $allRingFilter = array();

      foreach ($filterlist as $value) {
        $allRingFilter['metal_color'][str_replace(' ', '_', $value['metal_color'])] = $value['metal_color'];
        $allRingFilter['karat'][str_replace(' ', '_', $value['karat'])] = $value['karat'];
        if($value['sub_category'] != 'WEDDING BAND'){
          $allRingFilter['sub_category'][str_replace(' ', '_', $value['sub_category'])] = $value['sub_category'];
        }
        $allRingFilter['total_dia_wt'][str_replace(' ', '_', $value['total_dia_wt'])] = $value['total_dia_wt'];
        $allRingFilter['back_type'][str_replace(' ', '_', $value['back_type'])] = $value['back_type'];
      }
      asort($allRingFilter['total_dia_wt']);
      $diaArr = array();
      foreach ($this->_readRange as $key=>$value) {
        foreach ($allRingFilter['total_dia_wt'] as $totalDia) {
          if($totalDia > 0){
            if($totalDia > $value[0] && $totalDia < $value[1]){
              $diaArr[$key] = $value[0].'-'.$value[1];
            }
          }
        }
      }
      $allRingFilter['total_dia_wt'] = $diaArr;
      return $allRingFilter;
    }

    public function getCollectionfilter($flag=false){
      if($flag == false){
        $filterlist = 'select * from wizardmaster where construction =  "PRESET" AND collection != ""';
      }else{

        $queryString = Mage::app()->getRequest()->getParam('q');

        $whereArr = array();
        $whereArr[] = 'description LIKE "%'.$queryString.'%"';
        $whereArr[] = 'variant_remark LIKE "%'.$queryString.'%"';
        $whereArr[] = 'stone_shape LIKE "%'.$queryString.'%"';
        $whereArr[] = 'metal_color LIKE "%'.$queryString.'%"';
        $whereArr[] = 'karat LIKE "%'.$queryString.'%"';
        $whereArr[] = 'metal_type LIKE "%'.$queryString.'%"';
        $whereArr[] = 'sub_category LIKE "%'.$queryString.'%"';
        $whereArr[] = 'gender LIKE "%'.$queryString.'%"';
        $whereArr[] = 'product_type LIKE "%'.$queryString.'%"';
        $whereArr[] = 'finish_type LIKE "%'.$queryString.'%"';

        $whereArr = implode(" OR ", $whereArr);

        $filterlist = 'select * from wizardmaster where construction =  "PRESET" and ('.$whereArr.')';
      }

      $filterlist = $this->_readConnection->fetchAll($filterlist);
      $allRingFilter = array();

      foreach ($filterlist as $value) {
        if($value['collection'] != ''){
          $allRingFilter['collection'][str_replace(' ', '_', $value['collection'])] = $value['collection'];
        }
        $allRingFilter['product_type'][str_replace(' ', '_', $value['product_type'])] = $value['product_type'];
        $allRingFilter['metal_color'][str_replace(' ', '_', $value['metal_color'])] = $value['metal_color'];
        $allRingFilter['karat'][str_replace(' ', '_', $value['karat'])] = $value['karat'];
        if($value['sub_category'] != 'WEDDING BAND'){
          $allRingFilter['sub_category'][str_replace(' ', '_', $value['sub_category'])] = $value['sub_category'];
        }
        $allRingFilter['total_dia_wt'][str_replace(' ', '_', $value['total_dia_wt'])] = $value['total_dia_wt'];
        $allRingFilter['back_type'][str_replace(' ', '_', $value['back_type'])] = $value['back_type'];
      }
      asort($allRingFilter['total_dia_wt']);
      $diaArr = array();
      foreach ($this->_readRange as $key=>$value) {
        foreach ($allRingFilter['total_dia_wt'] as $totalDia) {
          if($totalDia > 0){
            if($totalDia > $value[0] && $totalDia < $value[1]){
              $diaArr[$key] = $value[0].'-'.$value[1];
            }
          }
        }
      }
      $allRingFilter['total_dia_wt'] = $diaArr;
      return $allRingFilter;
    }

    public function getDiamondShapeFilter($categoryId){
      $variantlist = 'select DISTINCT(pid) from wizardmaster where category_id='.$categoryId;
      $variantlist = $this->_readConnection->fetchCol($variantlist);
      if(!empty($variantlist)){
        $filterlist = 'select DISTINCT(variant_refsmryid) from wizardrelation where pid IN('.implode(",", $variantlist).') and variant_refsmryid != "" and type="material" ';
        $filterlist = $this->_readConnection->fetchCol($filterlist);

        $filterlist = '"'.implode('","', $filterlist).'"';
        $masterlist = 'select DISTINCT(stone_shape) from wizardmaster where sku IN ('.$filterlist.')';
        $masterlist = $this->_readConnection->fetchCol($masterlist);
      }else{
        $masterlist = array();
      }
      return $masterlist;
    }
    public function readRange(){
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

    public function getTotalDiaWtRange($totalDiaWtLabel){
        foreach($this->_readRange as $range=>$value){
           if($totalDiaWtLabel >=$value[0] && $totalDiaWtLabel <=$value[1]){
               $totalDiaWt[$value[0]."-".$value[1]] = array($range,$totalDiaWtLabel);
               return $totalDiaWt;
           }
        }

    }
    public function getAttributeOption($attr,$categoryId){
      $filterlist = 'select * from wizardmaster where category_id='.$categoryId;
      $filterlist = $this->_readConnection->fetchAll($filterlist);
      $attrArr = array();
      foreach ($filterlist as $value) {
          foreach ($attr as $key) {
              if(!in_array($value[strtolower($key)], $attrArr[$key])){
                if($value[strtolower($key)] != ''){
                  $attrArr[$key][] = $value[strtolower($key)];
                }
              }

          }
      }

      foreach ($attr as $key) {
        sort($attrArr[$key]);
      }
      $attrArr = array_filter($attrArr);
      return $attrArr;
    }


    public function getChainOption($catId,$code){
      $filterlist = 'select variant_refsmryid from wizardrelation where pid IN (select pid from wizardmaster where category_id='.$catId.') and type="chain"';
      $filterlist = $this->_readConnection->fetchCol($filterlist);

      if(count($filterlist) > 0){
        $filterlist = '"'.implode('","', $filterlist).'"';
        $filterlist = 'select '.$code.' from wizardmaster where '.$code.' != "" and '.$code.' != "0" and sku IN ('.$filterlist.') group by '.$code;
        $filterlist = $this->_readConnection->fetchCol($filterlist);
      }
      //echo "<pre/>";print_r($filterlist);exit;
      $allRingFilter = array();
      foreach ($filterlist as $value) {
        $allRingFilter[str_replace(' ', '_', $value)] = $value;
      }
      return $allRingFilter;
    }
}
