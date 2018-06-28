<?php

class Faaya_Layernavigation_Model_Layer extends Mage_Core_Model_Abstract
{
    protected $_resource;
    protected $_readConnection;
    protected $_lowestDiamond = array();


    protected function _construct(){
      $this->_resource = Mage::getSingleton('core/resource');
      $this->_readConnection = $this->_resource->getConnection('core_read');
    }
    public function getVarintHtml($pid,$editid)
    {
        $listblock = Mage::getBlockSingleton('catalog/product_list');
        $productUrl =  Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($pid);
        $url = $productUrl->getProductUrl();
        $wishlist = Mage::Helper('wishlist')->getAddUrl($productUrl);
        $compareUrl = $listblock->getAddToCompareUrl($productUrl);

        $query = 'SELECT matchpair_id,lowest_diamond_id,multiprice,image,variant_name FROM wizardmaster where pid='.$pid;
        $product = $this->_readConnection->fetchRow($query);
        $url .= ($product['lowest_diamond_id'] > 0)?"?did=".$product['lowest_diamond_id']:"?did=0";
        $url .= ($product['matchpair_id'] > 0)?"&side=".$product['matchpair_id']:'';
        $url .= ($editid)?"&editid=".$editid:'';


        $diaId = ($product['lowest_diamond_id'] != '')?$product['lowest_diamond_id']:0;
        $sideId = ($product['matchpair_id'] != '')?$product['matchpair_id']:0;

        $diahtml =  Mage::app()->getLayout()->createBlock('core/template')->setData('diamondid',$diaId)->setData('sideid',$sideId)->setTemplate('catalog/product/4c-content.phtml')->toHtml();

        $product['image'] = Mage::Helper('wizard')->getResizeImageWidth($product['image'],310);
        $returnArr = array('price'=>Mage::helper('core')->currency($product['multiprice'], true, false),'image'=>$product['image'],'url'=>$url,'variant_name'=>$product['variant_name'],'wishlist'=>$wishlist,'compareurl'=>$compareUrl,'pid'=>$pid,'diahtml'=>$diahtml);
        return json_encode($returnArr);
    }

    public function getitemId($pid){
        $query = 'SELECT item_id FROM wizardmaster where pid='.$pid;
        $vid = $this->_readConnection->fetchCol($query);
        return $vid[0];
    }

    public function getMetalColorArr($itemid,$totalDia){
        $totalDia = explode('-', $totalDia);
        //$query = 'select metal_color,karat,pid from wizardmaster where pid IN (SELECT pid FROM wizardrelation where base_variant_id="'.$vid.'") and total_dia_wt >= "'.$totalDia[0].'" and total_dia_wt <= "'.$totalDia[1].'" order by metal_color,karat';
        $query = 'select metal_color,karat,pid from wizardmaster where item_id ="'.$itemid.'" and total_dia_wt >= '.$totalDia[0].' and total_dia_wt <= '.$totalDia[1].' and product_type!="DIAMOND" order by metal_color,karat';
        $collection = $this->_readConnection->fetchAll($query);
        $metalColor = array();
        foreach ($collection as $value) {
          $keylbl = str_replace(' ', '-', $value['metal_color']).'_'.$value['karat'];
          $metalColor[$keylbl] = array('mkarat'=>$value['karat'].' '.$value['metal_color'],'pid'=>$value['pid'],'itemid'=>$itemid);
        }
        return $metalColor;
    }


    public function getFilterCollection($params){
        //echo "<pre/>";print_r($params);exit;
        if(count($params['sort']) > 0){
            if(isset($params['sort']['sub_category']) && count($params['sort']['sub_category'])>0){
                $implodeSub = str_replace("_", " ",'"'.implode('","', $params['sort']['sub_category']).'"');
            }
            if(isset($params['sort']['metal']) && count($params['sort']['metal'])>0){
                $implodeMetal = '"'.implode('","', $params['sort']['metal']).'"';
                $implodeMetal = str_replace("_", " ", $implodeMetal);
            }
            if(isset($params['sort']['metal_karat']) && count($params['sort']['metal_karat'])>0){
                $implodeMetalKarat = '"'.implode('","', $params['sort']['metal_karat']).'"';
                $implodeMetalKarat = str_replace("_", " ", $implodeMetalKarat);
            }
            if(isset($params['sort']['back_type']) && count($params['sort']['back_type'])>0){
                $back_type = '"'.implode('","', $params['sort']['back_type']).'"';
                $back_type = str_replace("_", " ", $back_type);
            }
            if(isset($params['sort']['collection']) && count($params['sort']['collection'])>0){
                $procollection = '"'.implode('","', $params['sort']['collection']).'"';
                $procollection = str_replace("_", " ", $procollection);
            }
            if(isset($params['sort']['product_type']) && count($params['sort']['product_type'])>0){
                $productType = '"'.implode('","', $params['sort']['product_type']).'"';
                $productType = str_replace("_", " ", $productType);
            }
            if(isset($params['sort']['shape']) && count($params['sort']['shape'])>0){
                $shape = '"'.implode('","', $params['sort']['shape']).'"';
                $shape = str_replace("_", " ", $shape);
                $dquery = 'SELECT sku from wizardmaster where stone_shape IN ('.$shape.')';
                $dcollection = $this->_readConnection->fetchCol($dquery);
                $dcollection = '"'.implode('","', $dcollection).'"';

                $squery = 'SELECT DISTINCT(pid) from wizardrelation where variant_refsmryid IN ('.$dcollection.')';
                $scollection = $this->_readConnection->fetchCol($squery);
            }


            if((isset($params['sort']['chain_length']) && count($params['sort']['chain_length'])>0) || (isset($params['sort']['chain_type']) && count($params['sort']['chain_type'])>0)){
                $chainwhere = array();
                if(isset($params['sort']['chain_length']) && count($params['sort']['chain_length'])>0){
                    $chainlength = '"'.implode('","', $params['sort']['chain_length']).'"';
                    $chainlength = str_replace("_", " ", $chainlength);
                    $chainwhere[] = 'chain_length IN ('.$chainlength.')';
                }

                if(isset($params['sort']['chain_type']) && count($params['sort']['chain_type'])>0){
                    $chaintype = '"'.implode('","', $params['sort']['chain_type']).'"';
                    $chaintype = str_replace("_", " ", $chaintype);
                    $chainwhere[] = 'chain_type IN ('.$chaintype.')';
                }
                $chainwhere = implode(" and ", $chainwhere);

                $dquery = 'SELECT sku from wizardmaster where '.$chainwhere;
                $dcollection = $this->_readConnection->fetchCol($dquery);
                $dcollection = '"'.implode('","', $dcollection).'"';
                $squery = 'SELECT DISTINCT(pid) from wizardrelation where variant_refsmryid IN ('.$dcollection.') and type= "chain"';
                $chaincollection = $this->_readConnection->fetchCol($squery);
            }



            if(count($params['sort']) == 1 && isset($params['sort']['sub_category'])){

                $query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and is_basevariant = 1 and sub_category IN('.$implodeSub.')';
                if($params['catId'] > 0){
                    $query .= ' and category_id='.$params['catId'];
                }else{
                    $query .= ' and collection != ""';
                }

            }elseif (count($params['sort']) == 3 && isset($params['sort']['metal']) && isset($params['sort']['metal_karat']) && count($params['sort']) == 1 && isset($params['sort']['sub_category'])) {

                $query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and is_default = 1 and metal_color IN ('.$implodeMetal.')';
                $query .= ' and karat IN ('.$implodeMetalKarat.')';
                if($params['catId'] > 0){
                    $query .= ' and category_id='.$params['catId'];
                }else{
                    $query .= ' and collection != ""';
                }
            }else{

                //$query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and is_ldmaterial = 1';
                $query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET"';

                if($params['catId'] > 0){
                    $query .= ' and category_id='.$params['catId'];
                }else{
                    $query .= ' and collection != ""';
                }
                if($implodeMetal != ''){
                    $query .= ' and metal_color IN ('.$implodeMetal.')';
                }
                if($implodeSub != ''){
                    $query .= ' and sub_category IN ('.$implodeSub.')';
                }
                if($implodeMetalKarat != ''){
                    $query .= ' and karat IN ('.$implodeMetalKarat.')';
                }
                if($back_type != ''){
                    $query .= ' and back_type IN ('.$back_type.')';
                }
                if($procollection != ''){
                    $query .= ' and collection IN ('.$procollection.')';
                }
                if($productType != ''){
                    $query .= ' and product_type IN ('.$productType.')';
                }

                if(isset($params['sort']['diamond_size']) && count($params['sort']['diamond_size'])>0){
                    $orQuery = array();
                    foreach ($params['sort']['diamond_size'] as $value) {
                        $value = explode('-', $value);
                        $orQuery[] = '(total_dia_wt >= "'.$value[0].'" and total_dia_wt <= "'.$value[1].'")';
                    }
                    if(count($orQuery)>0){
                        $query .= ' and ('.implode(' OR ', $orQuery).')';

                    }
                }
            }
            if($params['priceflag'] == 'true'){
                $query .= ' and multiprice >= '.$params['minPrice'].' and multiprice <= '.$params['maxPrice'];
            }
            if(isset($params['sort']['shape'])){
                if(count($params['sort']) == 1){
                    $query .= ' and pid IN ('.implode(",", $scollection).') and karat ="14K" and metal_color = "White Gold"';
                }else{
                    $query .= ' and pid IN ('.implode(",", $scollection).')';
                }
            }
            if((isset($params['sort']['chain_length']) && count($params['sort']['chain_length'])>0) || (isset($params['sort']['chain_type']) && count($params['sort']['chain_type'])>0)){
                    $query .= ' and pid IN ('.implode(",", $chaincollection).')';
            }


            $query .= ' order by multiprice '.$params['sortby'];
        }else{
            $query = 'SELECT * FROM wizardmaster WHERE is_basevariant = 1 and IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET"';
            if($params['catId'] > 0){
                $query .= ' and category_id='.$params['catId'];
            }else{
                $query .= ' and collection != ""';
            }
            if($params['priceflag'] == 'true'){
                $query .= ' and multiprice >= '.$params['minPrice'].' and multiprice <= '.$params['maxPrice'];
            }
            $query .= ' group by variant_id';
            $query .= ' order by multiprice '.$params['sortby'];
        }
        //echo $query;exit;
        if(isset($params['sort']['shape']) && empty($scollection) || isset($params['sort']['chain_type']) && empty($chaincollection) || isset($params['sort']['chain_length']) && empty($chaincollection)){
            $collection = array();
        }else{
            $collection = $this->_readConnection->fetchAll($query);
        }

    $getData = Mage::getSingleton('core/session')->getPresetData();
    $getData = unserialize($getData);
    $getData[$params['catId']] = $collection;
      Mage::getSingleton('core/session')->setPresetData(serialize($getData));
      return json_encode($collection);
    }
}

