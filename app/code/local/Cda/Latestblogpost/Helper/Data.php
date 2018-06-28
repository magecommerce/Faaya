<?php
class Cda_Latestblogpost_Helper_Data extends Mage_Core_Helper_Abstract{   
   public function getResizedHomeNewsImage($image,$width,$height,$uploadDir){
        if($image!=""){
              $orignalimagepath =  $image;
              $patharray = explode("/",$orignalimagepath);
              $imagename = end($patharray);
              $samepath = Mage::getBaseUrl()."wp/wp-content/uploads/";
              $folderpath=  str_ireplace($samepath,"",$orignalimagepath);
              $folderarray = explode('/',$folderpath);
              $im = explode('/',$image);
              $imageUrl = Mage::getBaseDir().DS."wp".DS."wp-content".DS."uploads".DS.$im[count($im)-3].DS.$im[count($im)-2].DS.$imagename;
              $imageResized = Mage::getBaseDir('media').DS.$uploadDir.DS.$width."x".$height.DS.$imagename;
              if (!file_exists($imageResized)&&file_exists($imageUrl)){
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepTransparency(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width,$height);
                $imageObj->save($imageResized);
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $uploadDir."/".$width."x".$height."/".$imagename;
                return $resizedURL;
              }
              elseif(file_exists($imageResized)) {
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $uploadDir."/".$width."x".$height."/". $imagename;
                return $resizedURL;    
              }
        }
   }
  /* public function getResizedCategoryImage($image,$width,$height){
       
        if($image!=""){
              $orignalimagepath =  $image;
              $patharray = explode("/",$orignalimagepath);
              $imagename = end($patharray);
              $samepath = Mage::getBaseDir('media')."catalog/category/";
              $folderpath=  str_ireplace($samepath,"",$orignalimagepath);
              $folderarray = explode('/',$folderpath);
              $imageUrl = Mage::getBaseDir('media').DS."catalog".DS."category".DS.$imagename;
              $imageResized = Mage::getBaseDir('media').DS."catalog/category".DS.$width."x".$height.DS.$imagename;
              if (!file_exists($imageResized)&& file_exists($imageUrl)){
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepTransparency(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width,$height);
                $imageObj->save($imageResized);
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."catalog/category/".$width."x".$height."/".$imagename;
                return $resizedURL;
              }
              elseif(file_exists($imageResized)) {
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."catalog/category/".$width."x".$height."/". $imagename;
                return $resizedURL;    
              }
             
        }
   }

   

   public function getResizedProductVideoImage($image,$width,$height,$id){
       
        if($image!=""){
              $orignalimagepath =  $image;
              $patharray = explode("/",$orignalimagepath);
              $imagename = end($patharray);
              $samepath = Mage::getBaseDir('media')."cmsmart/productvideo/thumbnail/product/".$id;
              $folderpath=  str_ireplace($samepath,"",$orignalimagepath);
              $folderarray = explode('/',$folderpath);
              $imageUrl = Mage::getBaseDir('media').DS."cmsmart".DS."productvideo".DS."thumbnail".DS."product".DS.$id.DS.$imagename;
              $imageResized = Mage::getBaseDir('media').DS."cmsmart/productvideo".DS."thumbnail".DS."product".DS.$id.DS.$width."x".$height.DS.$imagename;
              if (!file_exists($imageResized)&& file_exists($imageUrl)){
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepTransparency(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width,$height);
                $imageObj->save($imageResized);
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."cmsmart/productvideo/thumbnail/product/".$id."/".$width."x".$height."/".$imagename;
                return $resizedURL;
              }
              elseif(file_exists($imageResized)) {
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."cmsmart/productvideo/thumbnail/product/".$id."/".$width."x".$height."/". $imagename;
                return $resizedURL;    
              }
             
        }
   }

   public function getResizedProductNoneImage($image,$width,$height){
       
        if($image!=""){
              $orignalimagepath =  $image;
              $patharray = explode("/",$orignalimagepath);
              $imagename = end($patharray);
              $samepath = $this->getSkinUrl("images/video-placeholder.jpg");
              $folderpath=  str_ireplace($samepath,"",$orignalimagepath);
              $folderarray = explode('/',$folderpath);
              $imageUrl = $this->getSkinUrl("images/video-placeholder.jpg");
              $imageResized = Mage::getBaseDir('media').DS."catalog/product".DS."none".DS.$width."x".$height.DS.$imagename;
              if (!file_exists($imageResized)&& file_exists($imageUrl)){
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepTransparency(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width,$height);
                $imageObj->save($imageResized);
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."catalog/product/".$width."x".$height."/".$imagename;
                return $resizedURL;
              }
              elseif(file_exists($imageResized)) {
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."catalog/product/".$width."x".$height."/". $imagename;
                return $resizedURL;    
              }
             
        }
   }*/
}