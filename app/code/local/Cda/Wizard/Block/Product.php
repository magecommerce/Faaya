<?php
class Cda_Wizard_Block_Product extends Mage_Catalog_Block_Product_Abstract{
    public $_product;
    public function _construct()
    {
        $id = $this->getRequest()->getParam('id');
        $this->_product = Mage::getModel('catalog/product')->load($id);
    }


    public function getProduct($id){
        $product = Mage::getModel('catalog/product')->load($id);
        $product->load('media_gallery');
        return $product;
    }

    public function getAttributeArry($position){
        $attrArr = array();
        $attrArr['left'][] = array('title'=>'Certificate number','code'=>'certificate_no','oid'=>0,'tooltip'=>'','type'=>'other','number_format'=>false);
        //$attrArr['left'][] = array('title'=>'Price per carat','code'=>'price','oid'=>0,'tooltip'=>'1','type'=>'other');
        $attrArr['left'][] = array('title'=>'Carat weight','code'=>'weight','oid'=>0,'tooltip'=>'','type'=>'other','number_format'=>true);
        $attrArr['left'][] = array('title'=>'Shape','code'=>'stone_shape','oid'=>1,'tooltip'=>'','type'=>'other','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Cut','code'=>'stone_cut','oid'=>1,'tooltip'=>'','type'=>'other','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Color','code'=>'stone_color','oid'=>1,'tooltip'=>'','type'=>'other','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Clarity','code'=>'stone_quality','oid'=>1,'tooltip'=>'','type'=>'other','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Length/width ratio','code'=>'lw_ratio','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>true);

        $attrArr['right'][] = array('title'=>'Depth%','code'=>'depth_per','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Table%','code'=>'table_per','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Polish%','code'=>'polish','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Symmetry%','code'=>'symmetry','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Girdle%','code'=>'gridle_per','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Culet%','code'=>'culet','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Fluorescence%','code'=>'fluorescence','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);
        $attrArr['right'][] = array('title'=>'Measurements%','code'=>'measurements','oid'=>0,'tooltip'=>'1','type'=>'other','number_format'=>false);


        return $attrArr[$position];
    }



    public function getRingArry($position){
        $attrArr = array();
        $attrArr['left'][] = array('title'=>'Stock number','code'=>'item_name','oid'=>0,'tooltip'=>'','function'=>'','type'=>'ring','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Metal','code'=>'metal_type','oid'=>1,'tooltip'=>'1','function'=>'','type'=>'ring','number_format'=>false);
        //$attrArr['left'][] = array('title'=>'Width','code'=>'width','oid'=>0,'tooltip'=>'1','function'=>'','type'=>'ring');
        //$attrArr['left'][] = array('title'=>'Prong Metal','code'=>'prong_metal','oid'=>0,'tooltip'=>'','function'=>'','type'=>'ring');
        $attrArr['left'][] = array('title'=>'Available in sizes','code'=>'availale_in_sizes','oid'=>0,'tooltip'=>'','function'=>'getAvailbleSizes','type'=>'ring','number_format'=>false);
        $attrArr['left'][] = array('title'=>'Matching Wedding Band','code'=>'matching_wedding_band','oid'=>0,'tooltip'=>'','function'=>'getMatchingBand','type'=>'ring','number_format'=>false);


        return $attrArr[$position];
    }
    public function getListPopupProduct()
    {
        $id = $this->getRequest()->getParams('id');
        $product = Mage::getModel('catalog/product')->load($id);
        return $product;
    }
}