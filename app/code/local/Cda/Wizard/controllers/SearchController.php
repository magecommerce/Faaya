<?php
class Cda_Wizard_SearchController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $queryString = Mage::app()->getRequest()->getParam('q');
        $diamondResult = Mage::getModel('wizard/search')->search($queryString);
        if($diamondResult){
            Mage::helper('wizard')->clearselection();
            $this->_redirect('wizard/diamond/index/sku/'.$diamondResult);
        }else{
            $this->loadLayout();
            $this->renderLayout();
        }
    }
    public function loadmoreAction() {
      $page = $this->getRequest()->getParam('page');
      $catId = $this->getRequest()->getParam('catId');
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
      $result = Mage::getModel('wizard/search')->getFilterCollection($params);
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
      $html = Mage::app()->getLayout()->createBlock('core/template')->setData('products', $_productCollection)->setTemplate('catalog/product/list-result.phtml')->toHtml();
      $dataarr = array('html'=>$html,'count'=>count(json_decode($result,true)),'minprice'=>$minPrice,'maxprice'=>$maxPrice);
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($dataarr));
      /*$dataarr = json_encode($dataarr);
      echo $dataarr;exit;*/
    }
}
