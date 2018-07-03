<?php
class Cda_Custompost_Helper_Data extends Mage_Core_Helper_Abstract{
    public function textlimit($x, $length){
        if(strlen($x)<=$length){
            return $x;
        } else {
            $y=substr($x,0,$length);
            return $y;
        }
    }
}
	 