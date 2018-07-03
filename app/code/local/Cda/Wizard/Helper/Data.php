<?php
class Cda_Wizard_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $_allTitle;
    public $_caratReader;
    public $_caratFilePath;
    public $_resource;
    public $_writeConnection;
    public $_readConnection;
    public $_productTypeList;
    public function __construct(){
       $this->_allTitle = $this->getAlltitle();
       $this->_caratReader = $this->getcaratFileRead();
       $this->_caratFileUrl = Mage::getBaseUrl('media').'wizard/diamond-carat/';
       $this->_resource = Mage::getSingleton('core/resource');
       $this->_writeConnection = $this->_resource->getConnection('core_write');
       $this->_readConnection = $this->_resource->getConnection('core_read');
       $this->_productTypeList = $this->getProductTypeList();
    }

    public function getAttributeValue($code,$oid){
        $productModel = Mage::getModel('catalog/product');
        $optionAttr = $productModel->getResource()->getAttribute($code);
        return ($optionAttr->getSource()->getOptionText($oid))?$optionAttr->getSource()->getOptionText($oid):'N/A';
    }


    public function getAllAttribute()
    {
        return array(
        'STONE_SHAPE'=>array('code'=>'STONE_SHAPE','title'=>'STONE_SHAPE','type'=>'OTHER'),
        'STONE_QUALITY'=>array('code'=>'STONE_QUALITY','title'=>'STONE_QUALITY','type'=>'OTHER'),
        'STONE_CUT'=>array('code'=>'STONE_CUT','title'=>'STONE_CUT','type'=>'OTHER'),
        'STONE_COLOR'=>array('code'=>'STONE_COLOR','title'=>'STONE_COLOR','type'=>'OTHER'),
        'WEIGHT'=>array('code'=>'WEIGHT','title'=>'WEIGHT','type'=>'OTHER'),
        'CHAIN_TYPE'=>array('code'=>'CHAIN_TYPE','title'=>'CHAIN_TYPE','type'=>'CHAIN'),
        'CHAIN_LENGTH'=>array('code'=>'CHAIN_LENGTH','title'=>'CHAIN_LENGTH','type'=>'CHAIN'),
        'SUB_CATEGORY'=>array('code'=>'SUB_CATEGORY','title'=>'SUB_CATEGORY','type'=>'RING'),
        'METAL_COLOR'=>array('code'=>'METAL_COLOR','title'=>'METAL_COLOR','type'=>'RING'),
        'KARAT'=>array('code'=>'KARAT','title'=>'KARAT','type'=>'RING'),
        'PRODUCT_SIZE'=>array('code'=>'PRODUCT_SIZE','title'=>'PRODUCT_SIZE','type'=>'RING'),
        'BAND_WIDTH'=>array('code'=>'BAND_WIDTH','title'=>'BAND_WIDTH','type'=>'RING'),
        'METAL_TYPE'=>array('code'=>'METAL_TYPE','title'=>'METAL_TYPE','type'=>'RING'),
        'POLISH'=>array('code'=>'POLISH','title'=>'POLISH','type'=>'OTHER'),
        'SYMMETRY'=>array('code'=>'SYMMETRY','title'=>'SYMMETRY','type'=>'OTHER'),
        'FLUORESCENCE'=>array('code'=>'FLUORESCENCE','title'=>'FLUORESCENCE','type'=>'OTHER'),
        'DEPTH_MM'=>array('code'=>'DEPTH_MM','title'=>'DEPTH_MM','type'=>'OTHER'),
        'DEPTH_PER'=>array('code'=>'DEPTH_PER','title'=>'Depth%','type'=>'OTHER'),
        'TABLE_PER'=>array('code'=>'TABLE_PER','title'=>'Table%','type'=>'OTHER')
        );

    }

    public function deleteCartItemRow($deleteid)
    {
        $this->_writeConnection->query('delete from wizardedit where editid='.$deleteid);
    }
    public function getAlltitle(){
        $xmlPath = Mage::getBaseDir().DS.'wizardxml'.DS.'widget.xml';
        $xmlObj = new Varien_Simplexml_Config($xmlPath);
        $xmlData = $xmlObj->getNode();
        $attrArr = array();
        foreach ($xmlData->attribute as $attribute) {
            $attribute = (array)$attribute;
            $attrArr[$attribute['code'].'_'.$attribute['type']] = array('title'=>$attribute['title'],'tooltip'=>$attribute['tooltip'],'tooltip_image'=>$attribute['tooltip_image'],'option'=>   $attribute['option']);
        }
        return $attrArr;
    }


    public function getAttrTitle($attribute){
        $text = $this->_allTitle;
        $concate = strtoupper($attribute['code'].'_'.$attribute['type']);
        if(isset($text[$concate])){
            return $text[$concate]['title'];
        }else{
            return $attribute['title'];
        }
    }

    public function getTooltip($attribute){
        $text = $this->_allTitle;
        $concate = strtoupper($attribute['code'].'_'.$attribute['type']);
        return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($text[$concate]['tooltip'])->toHtml();
    }

    public function getAttImage($attribute,$val){

        $text = $this->_allTitle;
        $concate = strtoupper($attribute['code'].'_'.$attribute['type']);
        $image = '';
        foreach ($text[$concate]['option'] as $opt) {
            $opt = (array)$opt;
            if(strtolower($opt['title']) == strtolower($val)){
                $image = $opt['image'];
            }
        }
        if($image != ''){
            return '<img src="'.Mage::getBaseUrl('media').$image.'" alt="'.$val.'">';
        }
        return '<img src="'.Mage::getBaseUrl('media').'catalog'.DS.'product'.DS.'placeholder'.DS.'default'.DS.'placeholder.jpg" alt="'.$val.'">';
    }

    public function getAllOptions($code){
        $metalcolor = array();
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $code);
        $allOptions = $attribute->getSource()->getAllOptions(false, true);
        if($code == "metal_color"){
            foreach($allOptions as $metal){
                if($metal['label'] == "White Gold"){
                    $metalcolor[0] = $metal;
                }
                elseif($metal['label'] == "Yellow Gold"){
                    $metalcolor[1] = $metal;
                }
                elseif($metal['label'] == "Rose Gold"){
                    $metalcolor[2] = $metal;
                }else{
                    $metalcolor[3] = $metal;
                }
            }
            sort($metalcolor);
            return $metalcolor;
        }
        $updatedOptions = array();
        foreach ($allOptions as $option) {
            $optionCount = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter($code,$option['value']);
            if(count($optionCount) > 0){
                $updatedOptions[] = $option;
            }
        }
        return $updatedOptions;
    }

    public function getOptionbyItem($code,$itemId){
        $variantcollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('item_id',$itemId)->addFieldToFilter('construction','Create your own');
        $variantRing = $variantcollection->getColumnValues('entity_id');
        if(!empty($variantRing)){
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT variant_refsmryid from wizardrelation where type='material' and special_character='C' AND pid IN (".implode(',', $variantRing).")");
        }else{
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT variant_refsmryid from wizardrelation where type='material' and special_character='C'");
        }

        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $code);
        $allOptions = $attribute->getSource()->getAllOptions(false, true);

        $optionAvailable = array_column($allOptions, 'value');

        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('stone_shape')->addFieldToFilter('stone_shape', array('in' => $optionAvailable))->addFieldToFilter('sku', array('in' => $itemDiamond));
        $availableStonShape = $collection->getColumnValues('stone_shape');
        $availableStonShape = array_unique($availableStonShape);
        foreach ($allOptions as $key=>$value) {
            if(!in_array($value['value'], $availableStonShape)){
                unset($allOptions[$key]);
            }
        }
        sort($allOptions);
        return $allOptions;
    }
    public function getPinfo($pid)
    {
        $pinfo = $this->_readConnection->fetchOne("select pinfo from wizardmaster where pid=".$pid.' and pinfo!=""');
        return $pinfo;
    }
    public function getWeddingBand($pid)
    {
        $weddingband = $this->_readConnection->fetchOne("select variant_refsmryid from wizardrelation where pid=".$pid.' and type="wedding"');
        if($weddingband){
            $weddingband = $this->_readConnection->fetchRow("select * from wizardmaster where sku='".$weddingband."'");
        }
        return $weddingband;
    }

    public function getSelectedWedding($editid)
    {
        $weddingband = $this->_readConnection->fetchOne("select params from wizardedit where editid=".$editid);
        if($weddingband){
            $weddingband = unserialize($weddingband);
            if(isset($weddingband['wedding'])){
                return true;
            }
        }
        return false;
    }
    public function getAttrributeOptions($aid)
    {
        $collection = Mage::getModel('wizard/wizardoptions')->getCollection()->addFieldToFilter('attr_id',$aid)->setOrder('sort', 'ASC');
        return $collection;
    }

    public function getChangeAttrribute($code){

        $selectedStyle = $this->getRingSelected(false);
        $ringCollection = array();
        if($selectedStyle){
            $ringId = $selectedStyle['productId'];
            if($ringId > 0){
                $ringCollection = $this->_readConnection->fetchCol("select DISTINCT(variant_refsmryid) from wizardrelation where item_id=(select item_id from wizardmaster where pid=".$ringId.") and type='material' and special_character='C'");

            }
        }
        if($code == ''){
            return array();
        }
        $code = strtolower($code);
        $shaesorting = array('ROUND','PRINCESS','CUSHION','OVAL','PEAR','EMERALD');
        $stonecut = array('IDEAL','EXCELLENT','VERY GOOD','GOOD');
        $stonequality = array('FL','IF','VVS1','VVS2','VS1','VS2','SI1','SI2','SI3','I1','I2','I3');
        $stonecolor = array('D','E','F','G','H','I','J','K','L','M','COL');
        if(!empty($ringCollection)){
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['diamond']."' and sku IN ('".implode("','",$ringCollection)."')");

        }else{
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['diamond']."'");
        }

        if($code == 'stone_cut'){
            $itemDiamond = array_intersect($stonecut, $itemDiamond);
        }
        if($code == 'stone_quality'){
            $itemDiamond = array_intersect($stonequality, $itemDiamond);
        }
        if($code == 'stone_shape'){
            $itemDiamond = array_intersect($shaesorting, $itemDiamond);
        }
        if($code == 'stone_color'){
            $itemDiamond = array_intersect($stonecolor, $itemDiamond);
        }
        return $itemDiamond;
    }

    public function getRange($code){
        if($code == ''){
            return array();
        }

        $code = strtolower($code);
        $itemDiamond = $this->_readConnection->fetchRow("select MIN({$code}) as min,MAX({$code}) as max from wizardmaster where LOWER(product_type)='".$this->_productTypeList['diamond']."'");
        return $itemDiamond;
    }

    public function getChangeRingAttrribute($code){

        $diaId = $this->getSelectedValue();
        $relationRing = array();
        if($diaId){
            $sku = $this->getSkufromId($diaId['productId']);
            $relationRing = $this->_readConnection->fetchCol('select DISTINCT pid from wizardrelation where variant_refsmryid="'.$sku[0].'"');
        }
        $qury = '';
        if(!empty($relationRing)){
            $qury = ' and pid IN ('.implode(',', $relationRing).')';
        }
        $code = strtolower($code);
        if($code == 'sub_category'){
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['ring']."' and lower(sub_category) != 'wedding band' ".$qury);
            if(empty($itemDiamond)){
                $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['ring']."' and lower(sub_category) != 'wedding band' ");
            }

            $stylesorting = array('Solitaires','Halo Ring','3 Stone Ring','Trellis','Vintage');
            $itemDiamond = array_intersect($stylesorting, $itemDiamond);
        }else{
            $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['ring']."' ".$qury);
            if(empty($itemDiamond)){
                $itemDiamond = $this->_readConnection->fetchCol("select DISTINCT({$code}) from wizardmaster where LOWER(product_type)='".$this->_productTypeList['ring']."'");
            }
        }

        return $itemDiamond;
    }


    public function getCustomAttrribute($type)
    {
        $collection = Mage::getModel('wizard/wizardattribute')->getCollection()->addFieldToFilter('type',$type);
        $newData = array();
        foreach ($collection->getData() as $value) {
            $newData[$value['code']] = $value;
        }
        return $newData;
    }

    public function getVariantId($ids)
    {
        $ids = explode(',', $ids);
        if(!empty($ids)){
            $productList = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('entity_id', array('in'=> $ids))->addAttributeToSelect('variant_id');
            $variantArr = array();
            foreach ($productList as $product) {
                $variantArr[$product->getId()] = $product->getVariantId();
            }
            $variantArr = array_unique($variantArr);
            sort($variantArr);
            if(!empty($variantArr)){
                return $variantArr;
            }
        }
        return false;

    }

    public function getSkufromId($ids)
    {
        $ids = explode(',', $ids);
        if(!empty($ids)){
            $productList = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('entity_id', array('in'=> $ids))->addAttributeToSelect('variant_id');
            $variantArr = array();
            foreach ($productList as $product) {
                $variantArr[$product->getId()] = $product->getSku();
            }
            $variantArr = array_unique($variantArr);
            sort($variantArr);
            if(!empty($variantArr)){
                return $variantArr;
            }
        }
        return false;

    }


    public function getProductId($ids)
    {
        $vids = explode(',', $ids);
        if(!empty($ids)){
            if(!empty($vids)){
                $collection=Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('variant_id', array('in' => $vids));
                if(count($collection) > 0){
                    return $collection->getColumnValues('entity_id');
                }
            }
        }
        return false;
    }


    public function getIdfromRefSmy($ids)
    {
        $vids = explode(',', $ids);
        if(!empty($ids)){
            if(!empty($vids)){
                $collection=Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('sku', array('in' => $vids));
                if(count($collection) > 0){
                    return $collection->getColumnValues('entity_id');
                }
            }
        }
        return false;
    }


    public function getStyleId($id)
    {
        $product =  Mage::getModel('catalog/product')->load($id);
        if($product){
            return $product->getItemId();
        }else{
            return false;
        }
    }

    public function promisestepList($step,$order,$half)
    {
        $editid = Mage::app()->getRequest()->getParam('editid');
        $promiseSet = Mage::getSingleton('core/session')->getPromiseRing();

        $diaText = '';
        if(!empty($promiseSet) && $promiseSet['did'] > 0){


            $diaData = $this->_readConnection->fetchRow("select stone_shape,weight,stone_cut,stone_quality,stone_color,price from wizardmaster where pid=".$promiseSet['did']);
            $price = Mage::helper('core')->currency($diaData['price'], true, false);
            $diaText = '<span class="tab-subtitle">'.$diaData['stone_shape'].' '.$diaData['weight'].' CARAT '.$diaData['stone_color'].' '.$diaData['stone_cut'].' '.$diaData['stone_quality'].' <strong class="price">'.$price.'</strong></span>';
        }
        $ringText = '';
        if(!empty($promiseSet) && $promiseSet['promise'] > 0){


            $ringData = $this->_readConnection->fetchRow("select karat,metal_color,sub_category,price from wizardmaster where pid=".$promiseSet['promise']);
            $price = Mage::helper('core')->currency($ringData['price'], true, false);
            $ringText = '<span class="tab-subtitle">'.$ringData['metal_color'].' '.$ringData['sub_category'].' <strong class="price">'.$price.'</strong></span>';
        }

        $stepList = array();

            if($ringText != ''){
                $stepList[] = array('title'=>'Promise Ring','class'=>'','url'=>'wizard/index/promise','text'=>$ringText,'backurl'=>'');
            }else{
                $stepList[] = array('title'=>'Select Promise Ring','class'=>'','url'=>'wizard/index/promise','text'=>$ringText,'backurl'=>'');
            }

            if($diaText != ''){
                $stepList[] = array('title'=>'Diamond','class'=>'','url'=>'wizard/diamond/index/prms/1','text'=>$diaText,'backurl'=>'wizard/index/index/prms/1');
            }else{
                $stepList[] = array('title'=>'Select Diamond','class'=>'','url'=>'wizard/diamond/index/prms/1','text'=>$diaText,'backurl'=>'wizard/index/index/prms/1');
            }

            $stepList[] = array('title'=>'View Completed set','class'=>'disable','url'=>'#','text'=>'','backurl'=>'');


        sort($stepList);
        //$listArr[$step]['class'] = 'selected';
        if($half){
            $stepList[$step]['class'] = 'selected half-progress';
        }else{
           $stepList[$step]['class'] = 'selected full-progress';
        }
        //echo "<pre/>";print_r($stepList);exit;
        $this->moveElement($stepList,$step,$order);
        foreach ($stepList as $key=>$value) {
            if($editid){
                $stepList[$key]['url'] = $stepList[$key]['url'].'/editid/'.$editid;
                if($stepList[$key]['backurl'] != ''){
                    $stepList[$key]['backurl'] = $stepList[$key]['backurl'].'/editid/'.$editid;
                }

            }
            if($key < $order){
                $stepList[$key]['class'] = $value['class'].' full-progress';
            }
        }

        return $stepList;
    }


    public function stepList($step,$order,$half)
    {
        $editid = Mage::app()->getRequest()->getParam('editid');


        $currentURL = Mage::helper('core/url')->getCurrentUrl();
        $sidestone = false;

        $ringSelected =  Mage::getSingleton('core/session')->getRingSelected();
        $selected =  Mage::getSingleton('core/session')->getSelectedValue();
        $disableClass = '';
        $selected = unserialize($selected);
        $ringSelected = unserialize($ringSelected);
        if($selected['productId'] == 0 || $ringSelected['productId'] == 0){
            $disableClass = "disable";
        }

        if(($ringSelected != '' && (strpos($currentURL, 'wizard/ring/') != true))) {
            $allSubCat = $this->getAllOptions('sub_category');
            $subCatId = 0;
            foreach ($allSubCat as $value) {
                if($value['label'] == '3 Stone Ring'){
                    $subCatId = $value['value'];
                    break;
                }
            }
            //$ringSelected = unserialize($ringSelected);
            if($ringSelected['productId']){
                $product = Mage::getModel('catalog/product')->load($ringSelected['productId']);
                if($product->getSubCategory() == $subCatId){
                    $sidestone = true;
                }
            }
        }
        if(strpos($currentURL, 'wizard/sidestone/') == true || strpos($currentURL, 'wizard/sidedetail/') == true){
            $sidestone = true;
        }
        $listArr = array();
        if($selected['productId'] == 0){
            $listArr[0] = array('title'=>'Select Diamond','class'=>'','url'=>'wizard/index','text'=>'','backurl'=>'wizard/index');

        }else{
            $diaData = $this->_readConnection->fetchRow("select stone_shape,weight,stone_cut,stone_quality,stone_color,price from wizardmaster where pid=".$selected['productId']);
            $price = Mage::helper('core')->currency($diaData['price'], true, false);
            $diaText = '<span class="tab-subtitle">'.$diaData['stone_shape'].' '.$diaData['weight'].' CARAT '.$diaData['stone_color'].' '.$diaData['stone_cut'].' '.$diaData['stone_quality'].' <strong class="price">'.$price.'</strong></span>';
            $listArr[0] = array('title'=>'Diamond','class'=>'','url'=>'wizard/diamond','text'=>$diaText,'backurl'=>'wizard/index');

        }
        if($ringSelected['productId'] == 0){
            $listArr[1] = array('title'=>'Select Setting','class'=>'','url'=>'wizard/ring','text'=>'','backurl'=>'wizard/ring');

        }else{
            $ringData = $this->_readConnection->fetchRow("select karat,metal_color,sub_category,price from wizardmaster where pid=".$ringSelected['productId']);
            $price = Mage::helper('core')->currency($ringData['price'], true, false);
            $ringText = '<span class="tab-subtitle">'.$ringData['karat'].' '.$ringData['metal_color'].' '.$ringData['sub_category'].' <strong class="price">'.$price.'</strong></span>';
            $listArr[1] = array('title'=>'Setting','class'=>'','url'=>'wizard/ringdetail','text'=>$ringText,'backurl'=>'wizard/ring');

        }

        if($sidestone){
            $sidestoneList = Mage::getSingleton('core/session')->getSidestone();
            $sidestoneList = unserialize($sidestoneList);

            if(isset($sidestoneList['pid1']) && $sidestoneList['pid1'] > 0){
                $diaData = $this->_readConnection->fetchRow("select stone_shape,weight,stone_cut,stone_quality,stone_color,price from wizardmaster where pid=".$sidestoneList['pid1']);
                $price = Mage::helper('core')->currency($diaData['price'], true, false);
                $diaText = '<span class="tab-subtitle">'.$diaData['stone_shape'].' '.$diaData['weight'].' CARAT '.$diaData['stone_color'].' '.$diaData['stone_cut'].' '.$diaData['stone_quality'].' <strong class="price">'.$price.'</strong></span>';
                if($editid){
                    $listArr[2] = array('title'=>'Sidestones','class'=>'','url'=>'wizard/sidedetail','text'=>$diaText,'backurl'=>'wizard/sidestone');
                }else{
                    $listArr[2] = array('title'=>'Sidestones','class'=>'','url'=>'wizard/sidedetail','text'=>$diaText,'backurl'=>'wizard/sidestone');
                }

            }else{
                if($editid){
                    $listArr[2] = array('title'=>'Sidestones','class'=>'','url'=>'wizard/sidestone','text'=>'','backurl'=>'wizard/sidestone');
                }else{
                    $listArr[2] = array('title'=>'Sidestones','class'=>'','url'=>'wizard/sidestone','text'=>'','backurl'=>'wizard/sidestone');
                }

            }


        }

        $listArr[3] = array('title'=>'View Completed Ring','class'=> $disableClass,'url'=>'wizard/completed','text'=>'','backurl'=>'');

        sort($listArr);
        //$listArr[$step]['class'] = 'selected';
        if($half){
            $listArr[$step]['class'] = 'selected half-progress';
        }else{
           $listArr[$step]['class'] = 'selected full-progress';
        }
        $this->moveElement($listArr,$step,$order);
        foreach ($listArr as $key=>$value) {
            if($editid){
                $listArr[$key]['url'] = $listArr[$key]['url'].'/index/editid/'.$editid;
                if($listArr[$key]['backurl'] != ''){
                    $listArr[$key]['backurl'] = $listArr[$key]['backurl'].'/index/editid/'.$editid;
                }

            }
            if($key < $order){
                $listArr[$key]['class'] = $value['class'].' full-progress';
            }
        }

        return $listArr;
    }


    public function getcaratFileRead(){
        $caratDir = Mage::getBaseDir().DS.'media'.DS.'wizard'.DS.'diamond-carat';
        $results_array = array();
        if (is_dir($caratDir))
        {
            if ($handle = opendir($caratDir))
            {
                    while(($file = readdir($handle)) !== FALSE)
                    {
                        if($file != '.' && $file != '..'){
                            $results_array[] = str_replace(".png", "", $file);
                        }
                    }
                    closedir($handle);
            }
        }
        sort($results_array);
        return $results_array;
    }

    public function getcaratDiamond($min,$max){
        $minflg = false;
        $minupdated = $maxupdated = 0;
        foreach ($this->_caratReader as $value) {
            if($value > $min && !$minflg){
                $minupdated = $this->_caratFileUrl.$value.'.png';
                $minflg = true;
            }
            if($value < $max){
                $maxupdated = $this->_caratFileUrl.$value.'.png';
            }
        }
        return array($minupdated,$maxupdated);
    }

    public function moveElement(&$array, $a, $b) {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }

    public function getShippingDate($day){
        date_default_timezone_set('America/New_York');
        $start = date("Y-m-d");
        $weekend = 0;
        $currentDate =  date("Y-m-d");
        //$currentDate =  "2018-02-24";
        $dates=array();
        for($i = 1; $i<=$day; $i++)
        {
            array_push($dates,date('Y-m-d', strtotime($start . "+$i day")));
        }
        $sunday = Mage::Helper('wizard')->getDateForSpecificDayBetweenDates($dates[0],end($dates),7);
        $saturday = Mage::Helper('wizard')->getDateForSpecificDayBetweenDates($dates[0],end($dates),6);
        $totalday = $day + count($saturday)+ count($sunday);

        $addedDate = date('Y-m-d', strtotime($currentDate. "+". $totalday . "days"));
        $day = date("w",strtotime($addedDate));
        if($day !="0" && $day != "6"){
              //$receive = (int)$receive + 1;
              //echo $offWeekDate = date('Y-m-d', strtotime($currentDate. "+". $receive . "days"));
              return date("l, F j",strtotime($addedDate));
        }else{
              if($day == 6){
                  $receive = $totalday + 2;
                  $weekDate = date('Y-m-d', strtotime($currentDate. "+". $receive . "days"));
                  return date("l, F j",strtotime($weekDate));
              }else{
                   $receive = $totalday + 1;
                   $weekDate = date('Y-m-d', strtotime($currentDate. "+". $receive . "days"));
                   return date("l, F j",strtotime($weekDate));
              }
        }
    }
    public function getDateForSpecificDayBetweenDates($startDate,$endDate,$day_number){
        $endDate = strtotime($endDate);
        $days=array('1'=>'Monday','2' => 'Tuesday','3' => 'Wednesday','4'=>'Thursday','5' =>'Friday','6' => 'Saturday','7'=>'Sunday');
        for($i = strtotime($days[$day_number], strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i))
        $date_array[]=date('Y-m-d',$i);
        return $date_array;
    }

    public function getOrderDate($pid){
        $receive = Mage::getStoreConfig('wizard/wizard_group/wizard_receiveday');
        date_default_timezone_set('America/New_York');
        $hour = date('H');
        $amPm = date('a');
        $orderTime = '3-b';
        if($hour >= 15){
            $orderTime = '3-a';
        }
        $_product = Mage::getModel('catalog/product')->load($pid);
        $catIds = $_product->getCategoryIds();
        $catId = $catIds[0];
        $subCategory = $_product->getSubCategory();
        $smryItemType = $_product->getResource()->getAttribute('smry_item_type')->getFrontend()->getValue($_product);
        if($catId){
            $query = 'SELECT * FROM customshipping where jewelery = '.$catId;
            $results = $this->_readConnection->fetchAll($query);
            $matchTime = $receiveDate = 0;
            if($smryItemType == "diamond"){
                foreach($results as $ship){
                    if($ship['order_time'] == $orderTime){
                        $matchTime =  $ship['days'];
                    }
                }
                if($matchTime){
                    return $receiveDate =  Mage::Helper('wizard')->getShippingDate($matchTime);
                }
                else{
                    return $receiveDate =  Mage::Helper('wizard')->getShippingDate($receive);
                }
            }else{
                foreach($results as $ship){
                    if($ship['jewelery_style'] == $subCategory && $ship['order_time'] == $orderTime){
                        $matchTime =  $ship['days'];
                    }
                }
                if($matchTime){
                    return $receiveDate =  Mage::Helper('wizard')->getShippingDate($matchTime);
                }
                else{
                    return $receiveDate =  Mage::Helper('wizard')->getShippingDate($receive);
                }
            }
        }else{
            return $receiveDate =  Mage::Helper('wizard')->getShippingDate($receive);
        }
    }
    public function getMapping($attr,$value){

        $query = 'SELECT pid FROM wizardoptionsmapping where attr_id = '.$attr.' AND option_id IN('.implode(",", $value).')';
        return $results = $this->_readConnection->fetchCol($query);
    }

    public function getSelectedValue(){
        $selected =  Mage::getSingleton('core/session')->getSelectedValue();
        if($selected != ''){
            $selected = unserialize($selected);
            $pid = $selected['productId'];
            if($pid > 0){
                return $selected;
            }
        }
        return false;
    }

    public function getRingSelected($flag=false) {
        $ringSelected =  Mage::getSingleton('core/session')->getRingSelected();
        if($ringSelected != ''){
            $ringSelected = unserialize($ringSelected);
            $pid = $ringSelected['productId'];
            if($pid > 0 || $flag){
                return $ringSelected;
            }
        }
        return false;
    }

    public function getCurSymbol(){
        return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
    }

    public function getAttributeId($code){
        return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $code);
    }

    public function getProductType($pid){

        $res = $this->_readConnection->fetchOne('SELECT product_type FROM wizardmaster where pid = '.$pid);
        return $res;
    }
    public function getShapeValue($selected,$ringSelected){
        $shape = '';
        if(isset($selected['shapeValue'])){
            if(isset($selected['value'][$selected['shapeValue']][0])){
                $shape = Mage::getModel('wizard/wizardoptions')->load($selected['value'][$selected['shapeValue']][0]);
                $shape = $shape->getValue();
            }
        }
        if($shape == '' && isset($ringSelected['shapeValue'])){
            if(isset($ringSelected['value'][$ringSelected['shapeValue']][0])){
                $shape = Mage::getModel('wizard/wizardoptions')->load($ringSelected['value'][$ringSelected['shapeValue']][0]);
                $shape = $shape->getValue();
            }
        }
        return $shape;
    }

    public function getRedirect($id,$url){
        $url = Mage::app()->getStore()->getUrl($url);
        if($id == 0){
            echo Mage::app()->getFrontController()->getResponse()->setRedirect($url);
            exit;
        }
    }

    public function clearselection(){
        Mage::getSingleton('core/session')->unsSelectedValue();
        Mage::getSingleton('core/session')->unsRingSelected();
        Mage::getSingleton('core/session')->unsRingData();
        Mage::getSingleton('core/session')->unsPromiseRing();
        Mage::getSingleton('core/session')->unsSidestone();
    }

    public function checkoutStep(){
        $array = array(1=>'Checkout',2=>'Shipping',3=>'Payment',4=>'Finish');
        return $array;
    }


    public function customerLogin($user,$password){
        try{
            $session = Mage::getSingleton('customer/session');
            $result = $session->login($user,$password);
            $customer = $session->getCustomer();
            $session->setCustomerAsLoggedIn($customer);

            $resultArr['flag'] = 1;
            $resultArr['msg'] ='Logged in as '.$customer->getName();
            $jsonReturn = json_encode($resultArr);
            return $jsonReturn;
        }catch(Exception $e){
            $resultArr['flag'] = 0;
            $resultArr['msg'] = $e->getMessage();
            $jsonReturn = json_encode($resultArr);
            return $jsonReturn;
        }
    }


    public function getResizeImage($productImage = null, $width = 250, $height = 250)
    {
            $productImage = str_replace("/", DS, $productImage);
          // return when no image exists
          if (!$productImage) {
              return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
          }

          // return when the original image doesn't exist
          $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product'
                     . DS . $productImage;
          if (!file_exists($imagePath)) {
              return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
          }


            if (@getimagesize($imagePath)) {

            }else{
                return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
            }
          // resize the image if needed
          $rszImagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product'
                        . DS . 'cache' . DS . $width . 'x' . $height . DS
                        . $productImage;

          if (!file_exists($rszImagePath)) {
              $image = new Varien_Image($imagePath);
              $image->resize($width, $height);
              $image->save($rszImagePath);
          }
          // return the image URL
          return Mage::getBaseUrl('media') . '/catalog/product/cache/' . $width . 'x'
               . $height . '/' . $productImage;
    }


    public function getResizeImageWidth($productImage = null, $width = 250, $height = 100)
    {
            $productImage = str_replace("/", DS, $productImage);
          // return when no image exists
          if (!$productImage) {
              return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
          }

          // return when the original image doesn't exist
          $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product'. DS . $productImage;
          $imageurl = Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product'.DS.$productImage;
          if (!file_exists($imagePath)) {
              return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
          }


            /*if (@getimagesize($imagePath)) {

            }else{
                return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
            }*/
          // resize the image if needed
          /*$rszImagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product'
                        . DS . 'cache' . DS . $width . 'x' . $height . DS
                        . $productImage;

          if (!file_exists($rszImagePath)) {
              $image = new Varien_Image($imagePath);
              //$image->resize($width, $height);
            $image->constrainOnly(TRUE);
            $image->keepAspectRatio(TRUE);
            $image->keepFrame(FALSE);
            $image->resize($width,null);
            $image->save($rszImagePath);
          }*/
          // return the image URL
          //return Mage::getBaseUrl('media') . '/catalog/product/cache/' . $width . 'x'
          return $imageurl;
    }

    public function checkRemoteFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(curl_exec($ch)!==FALSE)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function getChainlength($pid){
        $chainlength = $this->_readConnection->fetchCol("select variant_refsmryid from wizardrelation where type='chain' AND pid =".$pid);
        if(!empty($chainlength)){
            $relationlength = '"'.implode('","', $chainlength).'"';
            $chainlength = $this->_readConnection->fetchCol("select chain_length from wizardmaster where sku IN (".$relationlength.") group by chain_length");
        }
        return $chainlength;
    }

    public function getSelectedlength($pid)
    {

        $chainlength = $this->_readConnection->fetchOne("select chain_length from wizardmaster where pid = ".$pid);
        return $chainlength;
    }
    public function getChainType($pid,$length){
        $chainTypeArr = array('Cable Chain- Thin','Cable Chain- Medium','Cable Chain- Thick','Thin','Medium','Thick');
        $chaintype = $this->_readConnection->fetchCol("select variant_refsmryid from wizardrelation where type='chain' AND pid =".$pid);
        if(!empty($chaintype)){
            $relationtype = '"'.implode('","', $chaintype).'"';
            $chaintype = $this->_readConnection->fetchCol("select chain_type from wizardmaster where sku IN (".$relationtype.") and chain_length=".$length." group by chain_type");
        }
        $chaintype = array_intersect($chainTypeArr,$chaintype);
        return $chaintype;
    }
    public function getChainId($pid,$length,$type){

        $chaintype = $this->_readConnection->fetchCol("select variant_refsmryid from wizardrelation where type='chain' AND pid =".$pid);
        if(!empty($chaintype)){
            $relationtype = '"'.implode('","', $chaintype).'"';
            $chaintype = $this->_readConnection->fetchRow("select pid,price from wizardmaster where sku IN (".$relationtype.") and chain_length=".$length." and chain_type='".$type."' group by chain_type");
            if(empty($chaintype)){
                $chaintype = false;
            }
        }

        return $chaintype;
    }
    public function getCartTotalItem(){
        $itemArray = array();
        $cnt = 0;
        $_items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        foreach ($_items as $_item) {
            $additionalOption = $_item->getProduct()->getCustomOption('setting');
            if($additionalOption){
                $additionalOptions =  unserialize($additionalOption->getValue());
                    $sid = $additionalOptions['group']['sid'];
                    $_item->setGroupType($additionalOptions['group']['type']);
                    $_item->setGroupOrderdate($additionalOptions['group']['orderdate']);
                    $itemArray[$sid][$_item->getProduct()->getId()] = $_item;
            }else{
                $randomInt = strtotime("now");
                $itemArray[$randomInt][$_item->getProduct()->getId()] = $_item;
            }

        }
        if(count($itemArray) > 0){
            $cnt = count($itemArray);
        }
        return $cnt;
    }
    public function getProductFromMaster($pid){
        $product = $this->_readConnection->fetchRow("select * from wizardmaster where pid =".$pid);
        return $product;
    }

    public function lowestPriceDiamond($variantid,$did){
          $relation = 'select variant_refsmryid from wizardrelation where variant_id = '.$variantid.' and type="material" and special_character ="C"';
          $relation = $this->_readConnection->fetchCol($relation);
          $relation = '"'.implode('","', $relation).'"';
          //$relationmaster = 'select pid AS lowest_diamond_id,MIN(price) AS lowest_diamond from wizardmaster where sku IN ('.$relation.')';
         $relationmaster = 'select pid AS lowest_diamond_id from wizardmaster where sku IN ('.$relation.') and pid NOT IN('.implode(",", $did).') and status=1 order by price';


          $relationmaster = $this->_readConnection->fetchOne($relationmaster);
          return $relationmaster;
    }

    public function existDiamonds($flag=false)
    {
        $productId = array();
        if(!$flag){
            $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
            foreach ($items as $item) {
                $productId[] = $item->getProductId();
            }
        }
        $existData = $this->_readConnection->fetchCol('select pid from wizardmaster where status=0');
        if(!empty($existData)){
            $productId = array_merge($productId,$existData);
        }
        return $productId;
    }


    public function getProductTypeList()
    {
        return array('ring'=>'rings','band'=>'bands','bracelet'=>'bracelets','chain'=>'chain','diamond'=>'diamond','earring'=>'earrings','pendant'=>'pendants','promise'=>'promise ring');
    }

}

