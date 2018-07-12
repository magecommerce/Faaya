<?php
class Cda_Wizard_Model_Search extends Mage_Core_Model_Abstract{
    public $_resource;
    public $_readConnection;
    public function _construct()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
    }

    public function search($queryString){
        $query = 'SELECT sku FROM wizardmaster where product_type="DIAMOND" and sku="'.$queryString.'" OR stock_code="'.$queryString.'"';
        $collection = $this->_readConnection->fetchOne($query);
        return $collection;
    }
    public function getFilterCollection($params){
        $queryString = $params['querystring'];
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


            }elseif (count($params['sort']) == 3 && isset($params['sort']['metal']) && isset($params['sort']['metal_karat']) && count($params['sort']) == 1 && isset($params['sort']['sub_category'])) {

                $query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET" and is_default = 1 and metal_color IN ('.$implodeMetal.')';
                $query .= ' and karat IN ('.$implodeMetalKarat.')';

            }else{

                $query = 'SELECT * FROM wizardmaster WHERE IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET"';


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
            $query .= ' and ('.$whereArr.')';

            $query .= ' order by multiprice '.$params['sortby'];
        }else{
            $query = 'SELECT * FROM wizardmaster WHERE is_basevariant = 1 and IF(center_diamond=1, special_character LIKE  "%C%", 1 )  AND IF(matchpair =1, special_character LIKE  "%M%", 1 ) AND  construction =  "PRESET"';

            if($params['priceflag'] == 'true'){
                $query .= ' and multiprice >= '.$params['minPrice'].' and multiprice <= '.$params['maxPrice'];
            }
            $query .= ' and ('.$whereArr.')';
            $query .= ' group by variant_id';
            $query .= ' order by multiprice '.$params['sortby'];
        }
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
