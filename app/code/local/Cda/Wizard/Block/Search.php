<?php
class Cda_Wizard_Block_Search extends Mage_Core_Block_Template{


    public $_resource;
    public $_readConnection;
    public function _construct()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
    }


    public function getLoadCollection($cid){
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


        $query = 'SELECT * FROM wizardmaster WHERE is_default = 1 and IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and ('.$whereArr.') group by variant_id order by multiprice';

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
}