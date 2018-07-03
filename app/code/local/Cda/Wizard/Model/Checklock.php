<?php
class Cda_Wizard_Model_Checklock extends Mage_Core_Model_Abstract {
    public function checkLock()
    {
        $diamondSmryId = array();
        $cart = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cart->getAllItems() as $item) {
            $additionalOption = $item->getProduct()->getCustomOption('setting');
            $additionalOptions =  unserialize($additionalOption->getValue());
            if($additionalOptions['group']['type'] == 'did'){
                $diamondSmryId[$additionalOptions['group']['sid']] = $additionalOptions['group']['smryid'];
            }
        }

        $sidArr = array();
        if(!empty($diamondSmryId)){
            Mage::log(print_r($diamondSmryId,true), null, 'lock-api.log');
            foreach ($diamondSmryId as $key=>$smryId) {
                $symrArr = array('SmryID'=>$smryId);
                $ch = curl_init("http://52.7.153.114/SeautilityTest/SEAUtilityService.svc/LockStockForOrder");
                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($symrArr) );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                $result = curl_exec($ch);
                curl_close($ch);
                $res = json_decode($result);
                if($res != 'SUCCESS'){
                //if($res == 'SUCCESS'){
                    $sidArr[] = $key;
                }
            }
        }
        //die('0001');
        return $sidArr;
    }
}
?>
