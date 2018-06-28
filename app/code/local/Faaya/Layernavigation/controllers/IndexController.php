<?php
class Faaya_Layernavigation_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('layernavigation/layernavigation')->setTemplate("catalog/product/list-response.phtml")->toHtml());
    }

    public function loadmoreAction() {
      $page = $this->getRequest()->getParam('page');
      $catId = $this->getRequest()->getParam('catId');
      $catId = Mage::Helper('layernavigation')->categoryId($catId);
      $editid = $this->getRequest()->getParam('editid');
      $limit =  Mage::getSingleton('core/session')->getLimitData();
      $offset = ((int)$page*(int)$limit)-(int)$limit;
      $_productCollection = Mage::getSingleton('core/session')->getPresetData();
      $_productCollection = unserialize($_productCollection);
      $_productCollection = $_productCollection[$catId];
      $_productCollection = array_slice($_productCollection,$offset,$limit);

      echo  Mage::app()->getLayout()->createBlock('core/template')->setData('products',$_productCollection)->setData('editid',$editid)->setTemplate('catalog/product/list-result.phtml')->toHtml();
      exit;
    }

    public function loadnewAction() {
      $params = $this->getRequest()->getParams();
     // $params = array('catId'=>38,'sort'=>array('diamond_size'=>array('0.45-0.59','0.60-0.65'),'metal'=>array('White_Gold'),'metal_karat'=>array('14K','18K'),'shape'=>array('PRINCESS'),'sub_category'=>array('Halo_Ring')),'orderby'=>'created_at','sortby'=>'desc','minPrice'=>'','maxPrice'=>'');
     //$params = array('catId'=>38,'sort'=>array('shape'=>array('PRINCESS'),'orderby'=>'created_at','sortby'=>'desc','minPrice'=>'','maxPrice'=>''));
      $params['catId'] = Mage::Helper('layernavigation')->categoryId($params['catId']);
      $result = Mage::getModel('layernavigation/layer')->getFilterCollection($params);
      $limit =  Mage::getSingleton('core/session')->getLimitData();
      $minPrice = 0;
      $maxPrice = 0;
      foreach (json_decode($result,true) as $value) {
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
      $_productCollection = array_slice(json_decode($result,true),0,$limit);
      //echo "<pre/>";print_r($_productCollection);exit;
      $html = Mage::app()->getLayout()->createBlock('core/template')->setData('products', $_productCollection)->setTemplate('catalog/product/list-result.phtml')->toHtml();
      $dataarr = array('html'=>$html,'count'=>count(json_decode($result,true)),'minprice'=>$minPrice,'maxprice'=>$maxPrice);
      $dataarr = json_encode($dataarr);
      echo $dataarr;exit;
    }

    public function changevarintAction(){
        $id = $this->getRequest()->getParam('id');
        $editid = $this->getRequest()->getParam('editid');
        $pid = $this->getRequest()->getParam('pid');
        $name = $this->getRequest()->getParam('name');
        $itemId = $this->getRequest()->getParam('itemid');
        $diamondid = $this->getRequest()->getParam('diamondid');
        /*$pid = 63774;
        $id = 'White-Gold_18K';
        $name = 'metalkarat-62711';
        $itemId = '2296';*/
        echo Mage::getModel('layernavigation/layer')->getVarintHtml($pid,$editid);
        exit;
    }

    public function changemetalAction(){
        $diawt = $this->getRequest()->getParam('diawt');
        $pid = $this->getRequest()->getParam('productid');
        $editid = $this->getRequest()->getParam('editid');
        $metalselected = $this->getRequest()->getParam('metalselected');

        $itemid = Mage::getModel('layernavigation/layer')->getitemId($pid);
        $metalArr =  Mage::getModel('layernavigation/layer')->getMetalColorArr($itemid,$diawt);
        $keyarr = array_keys($metalArr);
        $selected = (isset($metalArr[$metalselected]))?$metalselected:$keyarr[0];
        $selectedvalue = (isset($metalArr[$metalselected]))?$metalArr[$metalselected]:$metalArr[$keyarr[0]];
        $passarr = array('metal'=>$metalArr,'selected'=>$selected,'selectedvalue'=>$selectedvalue);
        $html = Mage::app()->getLayout()->createBlock('core/template')->setData('data', $passarr)->setTemplate('catalog/product/metalhtml.phtml')->toHtml();
        $varintHtml =  Mage::getModel('layernavigation/layer')->getVarintHtml($selectedvalue['pid'],$editid);

        $jsonArr = array('html'=>$html,'varianthtml'=>$varintHtml,'styleid'=>$selectedvalue['pid']);
        echo json_encode($jsonArr);exit;
    }

    public function filterAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('layernavigation/layernavigation')->setTemplate("catalog/product/filter-response.phtml")->toHtml());
    }
    public function changestyleAction(){
       $productId = $this->getRequest()->getParam('productid');
       $itemId = $this->getRequest()->getParam('itemid');
       $categoryId = $this->getRequest()->getParam('categotyId');
       $totalDiaWtId = $this->getRequest()->getParam('totaldiawt');
       $totalDiaWtId = explode("-",$totalDiaWtId);
       $metalId = $this->getRequest()->getParam('metal');
       $karatId = $this->getRequest()->getParam('karat');
       $_productCollection = Mage::getModel('catalog/product')->getCollection()
         ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left')
         ->addAttributeToSelect('*')
         ->addAttributeToFilter('construction','PRESET')
         ->addAttributeToFilter('category_id',$categoryId)
         ->addAttributeToFilter('item_id',$itemId)
         ->addFieldToFilter('total_dia_wt',array('gteq'=>$totalDiaWtId[0]))
         ->addFieldToFilter('total_dia_wt',array('lteq'=>$totalDiaWtId[1]))
         ->addAttributeToFilter('metal_color',$metalId)
         ->addAttributeToFilter('karat',$karatId);
        $_product = $_productCollection->getFirstItem();
        $newProductId = $_product->getId();
      if($newProductId){
           /*echo '<pre>';
           print_r($_product->getData());
           exit;       */
           $getDiamondId = $this->getLayout()->getBlockSingleton('layernavigation/layernavigation')->getDiamond($newProductId,$categoryId);
           $diamondPrice = $diamondSpecialPrice = 0;
           $stylePrice =  $styleSpecialPrice = $styleTotal = $styleSpecialTotal = 0;
           $isSpecial = 0;
           if($getDiamondId){
            $did = $getDiamondId['productId'];
            $diamondProduct = Mage::getModel('catalog/product')->load($did);
            $diamondId = $diamondProduct->getId();
            $diamondPrice = $diamondProduct->getPrice();
            $diamondSpecialPrice = $diamondProduct->getSpecialPrice();
           }
           $productName = $_product->getName();
           $variantRemark = $_product->getVariantRemark();
           $stylePrice = $_product->getPrice();
           $styleSpecialPrice = $_product->getSpecialPrice();
           if(!$styleSpecialPrice || !$diamondSpecialPrice){
               $styleTotal = $stylePrice + $diamondPrice;
           }else{
                $isSpecial = 1;
                $styleTotal = $stylePrice + $diamondPrice;
                $styleSpecialTotal = $styleSpecialPrice + $diamondSpecialPrice;
           }
           $mainTotal = Mage::helper('core')->currency($styleTotal, true, false);
           $specialTotal = Mage::helper('core')->currency($styleSpecialTotal, true, false);
           $url = $_product->getProductUrl();
           $imageUrl = Mage::helper('catalog/image')->init($_product, 'small_image')->keepFrame(false)->resize(625);
           $image = (string)$imageUrl;
           $url = $_product->getProductUrl()."?did=" . $diamondId;
           $response = array('productId'=>$newProductId,'productName'=>$productName,'variantRemark'=>$variantRemark,'price'=>$mainTotal,'special_price'=>$specialTotal,'is_special'=>$isSpecial,'url'=>$url,'imageUrl'=>$image,'diamondId'=>$diamondId);
           $this->getResponse()->setHeader('Content-type', 'application/json');
           $this->getResponse()->setBody(json_encode($response));
      }else{
           $response = array('msg'=>false);
           $this->getResponse()->setHeader('Content-type', 'application/json');
           $this->getResponse()->setBody(json_encode($response));
      }
    }
}