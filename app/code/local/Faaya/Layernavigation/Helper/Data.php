<?php
class Faaya_Layernavigation_Helper_Data extends Mage_Core_Helper_Abstract{
    public function categoryId($cid)
    {
        if($cid == 46){
            return 0;
        }else{
            return $cid;
        }
    }
}
