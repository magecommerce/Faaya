<?php
class Cda_Matchingband_Block_Matchingband extends Mage_Catalog_Block_Product_Abstract{
    public function getMatchingBandCollection(){
        $limit = 8;
        $matchingBand = Mage::getModel("wizard/wizardrelation")->getCollection()->addFieldToSelect('pid')->addFieldToFilter('type',"wedding")->setPageSize($limit)->setOrder('pid', 'ASC');
        return $matchingBand;
    }
    public function getTotalMatchingBand(){
        $TotalMatchingBand = Mage::getModel("wizard/wizardrelation")->getCollection()->addFieldToSelect('pid')->addFieldToFilter('type',"wedding");
        return $TotalMatchingBand;
   }
   public function getLoadMoreMatchingBandCollection(){
        $limit = 8;
        $getupperLimit = (int)$this->getRequest()->getParam('limit');
        $upperLimit  = $limit * $getupperLimit;
        $lowerLimit = $upperLimit -  $limit;
        //$getUpperLimit = $this->getRequest()->getParam('upperlimit');
        /*if($getLimit){
            $upperLimit = $getLimit;
            $lowerLimit = $getLimit - $limit;        
        }*/
        $matchingBand = Mage::getModel("wizard/wizardrelation")->getCollection()->addFieldToSelect('pid')->addFieldToFilter('type',"wedding")->setOrder('pid', 'ASC');
        //$matchingBand->getSelect()->limit($lowerLimit,$upperLimit);
        $matchingBand->getSelect()->limit($limit,$upperLimit);
        //echo count($matchingBand);
        //$matchingBand = Mage::getModel("wizard/wizardrelation")->getCollection()->addFieldToSelect('pid')->addFieldToFilter('type',"wedding")->setPageSize($upperLimit)->setCurPage($getupperLimit);
        return $matchingBand;
   }
}