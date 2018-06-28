<?php
//ini_set('memory_limit', '-1');
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); //Path to Magento
umask(0);
echo "<pre>";
Mage::app('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$orderId = $_GET['orderid'];
$order = Mage::getModel('sales/order')->load($orderId); // 26 //22 // 20(6) , //14 , //19(2)
$fedexcross = Mage::getStoreConfig('wizard/wizard_group/fedex_cross');
$fedexcross = floatval($fedexcross);
//print_R($order->getData());exit;
$orderRefId = $order->getRefId();
$specialInstruction = "";
//exit;
if($orderId){
    //print_R($order->getData());exit;
    $specialInstruction = $order->getSpecialInstruction();
    if($order->getDeliveryCall()){
        $specialInstruction .= " | call before comming";
    }
    $billingAddress = $order->getBillingAddress();
    $shippingAddress = $order->getShippingAddress();
    $orderDetail = $orderItem = $orderCollection = array();
    $customerId = $order->getCustomerId();
    $email = $order->getCustomerEmail();
    $flag = 0;
    $customerPartyCode = "";
    if($customerId){
        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $customerPartyCode = $customer->getPartyCode();
    }
    else{
        $customerguestuser = Mage::getModel('orderapi/customerguestuser')->load($email,'email');
        if($customerguestuser->getId()){
            $customerId = $customerguestuser->getId();
            $customerPartyCode = $customerguestuser->getPartyCode();
        }else{
            $customerguestuser = Mage::getModel('orderapi/customerguestuser');
            $customerguestuser->setEmail($email);
            $customerguestuser->save();
            $customerId = $customerguestuser->getId();
            //Mage::log($customerId, null, 'customerguestuser.log', true);
        }
        $flag = 1;
    }
$contactName = $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname();
// Order Details
$orderDetail['ObjHdr']['TransDate'] =  date("d-m-Y",strtotime($order->getCreatedAt()));
$orderDetail['ObjHdr']['PartyCode'] =  $customerPartyCode;
//$orderDetail['ObjHdr']['FinalValue'] =  floatval($order->getBaseSubtotalInclTax());//Mage::helper('core')->formatPrice($order->getBaseSubtotalInclTax());//$order->getBaseSubtotalInclTax();
$orderDetail['ObjHdr']['FinalValue'] =  floatval($order->getGrandTotal());//Mage::helper('core')->formatPrice($order->getBaseSubtotalInclTax());//$order->getBaseSubtotalInclTax();
$orderDetail['ObjHdr']['ContactName'] =  ($contactName) ? $contactName : '';
$orderDetail['ObjHdr']['ContactMobileNo'] =  ($shippingAddress->getTelephone()) ? $shippingAddress->getTelephone() : '';
$orderDetail['ObjHdr']['CurrencyCode'] =  "USD";
$orderDetail['ObjHdr']['CurrencyRate'] =  1;
$orderDetail['ObjHdr']['OrderRefNo'] =  $order->getIncrementId();
$orderDetail['ObjHdr']['Remarks'] =  $specialInstruction;
$orderDetail['ObjHdr']['FirstName'] =  ($order->getCustomerFirstname()) ? $order->getCustomerFirstname() : '';
$orderDetail['ObjHdr']['LastName'] =  ($order->getCustomerLastname()) ? $order->getCustomerLastname() : '';
// Billing Address Details
$orderDetail['ObjHdr']['BillAddress1'] =  ($order->getBillingAddress()->getStreet1()) ? $order->getBillingAddress()->getStreet1() : '';
$orderDetail['ObjHdr']['BillAddress2'] =  ($order->getBillingAddress()->getStreet2()) ? $order->getBillingAddress()->getStreet2() : '';
$orderDetail['ObjHdr']['BillCountryName'] =  ($billingAddress->getCountryId()) ? $billingAddress->getCountryId() : '';
$orderDetail['ObjHdr']['BillStateName'] =  ($billingAddress->getRegion()) ? $billingAddress->getRegion() : 'london';
$orderDetail['ObjHdr']['BillCityName'] =  ($billingAddress->getCity()) ? $billingAddress->getCity() : '';
$orderDetail['ObjHdr']['BillPinCode'] =  ($billingAddress->getPostcode()) ? $billingAddress->getPostcode() : '';
$orderDetail['ObjHdr']['BillMobileNo'] =  ($billingAddress->getTelephone()) ? $billingAddress->getTelephone() : '';
$orderDetail['ObjHdr']['BillEmailId'] =  ($billingAddress->getEmail()) ? $billingAddress->getEmail() : '';
// Shipping Address Details
$orderDetail['ObjHdr']['ShipAddress1'] =  ($order->getShippingAddress()->getStreet1()) ? $order->getShippingAddress()->getStreet1() : '';
$orderDetail['ObjHdr']['ShipAddress2'] =  ($order->getShippingAddress()->getStreet2()) ? $order->getShippingAddress()->getStreet2() : '';
$orderDetail['ObjHdr']['ShipCountryName'] = ($shippingAddress->getCountryId()) ? $shippingAddress->getCountryId() : '';
$orderDetail['ObjHdr']['ShipStateName'] =  ($shippingAddress->getRegion()) ? $shippingAddress->getRegion() : 'london';
$orderDetail['ObjHdr']['ShipCityName'] =  ($shippingAddress->getCity()) ? $shippingAddress->getCity() : '';
$orderDetail['ObjHdr']['ShipPinCode'] =  ($shippingAddress->getPostcode()) ? $shippingAddress->getPostcode() : '';
$orderDetail['ObjHdr']['ShipMobileNo'] =  ($shippingAddress->getTelephone()) ? $shippingAddress->getTelephone() : '';
$orderDetail['ObjHdr']['ShipEmailId'] =  ($shippingAddress->getEmail()) ? $shippingAddress->getEmail() : '';
$orderDetail['ObjHdr']['PreCarriage'] =  'FEDEX';
$orderDetail['ObjHdr']['ServiceType'] =  ($order->getGrandTotal() < $fedexcross)?'FEDEX_2_DAY':'PRIORITY_OVERNIGHT';

$quoteId = $order->getQuoteId();
$_items = $order->getItemsCollection();
$itemArray  = $products = array();
foreach ($_items as $orderItem) { //print_r($orderItem->getData());
 $options = Mage::getResourceModel('sales/quote_item_option_collection');
    $options->addItemFilter($orderItem->getQuoteItemId());
    foreach ($options as $option) {
        //print_R($option->getData());
         if($option->getCode() == "setting"){
          $additionalOption =  unserialize($option->getValue());
         //print_R($additionalOption);
          if($additionalOption){
                //$product =  Mage::getModel('catalog/product')->load($orderItem->getProductId());
                $groupOption = $additionalOption['group']['option'];
                $ringSize = $groupOption['size'];
                $text = $groupOption['text'];
                $fontFamily = $groupOption['fontfamily'];

                $sid = $additionalOption['group']['sid'];
                $orderItem->setGroupType($additionalOption['group']['type']);
                $orderItem->setGroupSmryid($additionalOption['group']['smryid']);
                $orderItem->setGroupOrderdate($additionalOption['group']['orderdate']);
                $orderItem->setGroupVariantid($additionalOption['group']['variantid']);

                $orderItem->setGroupConstruction($additionalOption['group']['construction']);
                $orderItem->setGroupRing($additionalOption['group']['ring']);
                $orderItem->setGroupPendant($additionalOption['group']['pendant']);
                $orderItem->setGroupEarring($additionalOption['group']['earring']);
                $orderItem->setGroupBracelets($additionalOption['group']['bracelets']);
                $orderItem->setGroupDiamond($additionalOption['group']['diamond']);
                $orderItem->setGroupChain($additionalOption['group']['chain']);
                $orderItem->setGroupChainType($additionalOption['group']['chain_type']);
                $orderItem->setGroupChainLength($additionalOption['group']['chain_length']);
                $orderItem->setGroupSide1($additionalOption['group']['side1']);
                $orderItem->setGroupSide2($additionalOption['group']['side2']);
                $orderItem->setGroupPromise($additionalOption['group']['promise']);
                $orderItem->setGroupRingSize($ringSize);
                $orderItem->setGroupEngravingFont($fontFamily);
                $orderItem->setGroupEngravingText($text);

                $itemArray[$sid][$orderItem->getProductId()] = $orderItem;
                $products[$orderItem->getProductId()] = $orderItem;
          }else{
                $randomInt = strtotime("now");
                //$product =  Mage::getModel('catalog/product')->load($orderItem->getProductId());
                //$itemArray[$randomInt][$orderItem->getProduct()->getId()] = $orderItem;
                $itemArray[$randomInt][$orderItem->getProductId()] = $orderItem;
                $products[$orderItem->getProductId()] = $orderItem;
          }
        }
    }
}
//exit;

 $itemcollection = array();
 foreach($itemArray as $randomKey=>$items){
    $firstItem = array_keys($items);
    //print_R($firstItem);
    $pid = array();
    $cnt = count($firstItem);
    $diamondDetail = array();
    $discountTotal = $price = $taxPercentage = 0.0;
    $rulesName = $simpleAction ='';
    //print_R($firstItem);
    $mainSmryId = $matchPairSmryID1 = $matchPairSmryID2 = $findingVariantID = $discountValue = $finalValue = $taxPer = $refSmryID = $ringSize = $chainLength = 0;
    $discountType = $engravingFonts = $engravingText = $chainType = $deliveryTimelineDate = "";

    foreach($firstItem as $key=>$itm){
    $itemDetail = array();
          // print_R($items[$itm]->getData());
          // echo $itm;
           //echo "<br/>";
           $cnt = count($items);
           //echo "<br/>";
           $type = $items[$itm]->getGroupType();
           //echo "<br/>";
           $orderDate = $items[$itm]->getGroupOrderdate();
           //echo "<br/>";
           $smryId = $items[$itm]->getGroupSmryid();
           //echo "<br/>";
           $size = $items[$itm]->getGroupRingSize();
           $ctype = $items[$itm]->getGroupChainType(); // chain type
           $clength = $items[$itm]->getGroupChainLength(); // chain type
           $eFont = $items[$itm]->getGroupEngravingFont(); // Engraving Font
           $eText = $items[$itm]->getGroupEngravingText(); // Engraving Text
           //echo "<br/>";
        // Rules code
        //$ruleId = $pro['applied_rule_ids'];
        $ruleId = $items[$itm]->getAppliedRuleIds();
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);
        $simpleAction = $rule->getSimpleAction();
         if($simpleAction == "by_percent"){
             $discountType = "PERCENT";
         }else{
              $discountType = "FIXED";
         }
         if(($cnt == 1 && $type == "did") || ($cnt == 1 && $type == "wedding")){
             if($type == "did" || $type == "wedding"){
                 //$itemDetail['SmryID'] = $smryId;
                 $mainSmryId = $smryId;
             }
            /* elseif($type == "did"){
                 //$itemDetail['RefSmryID'] = $smryId;
                 $refSmryID = $smryId;
             }*/
             elseif($type == "chain"){
                 //$itemDetail['FindingVariantID'] = $smryId;
                 $findingVariantID = $smryId;
             }
             elseif($type == "side1"){
                 //$itemDetail['MatchPairSmryID1'] = $smryId;
                 $matchPairSmryID1 = $smryId;
             }elseif($type == "side2"){
                 //$itemDetail['MatchPairSmryID2'] = $smryId;
                 $matchPairSmryID2 = $smryId;
             }
             if($orderDate){
                $deliveryTimelineDate = date('d-m-Y',strtotime($orderDate));
             }
             if($size){
                $ringSize = $size;
             }
             if($ctype){
                $chainType = $ctype;
             }
             if($clength){
                $chainLength = $clength;
             }
             if($eFont){
                $engravingFonts = $eFont;
             }
             if($eText){
                $engravingText = $eText;
             }
             $refSmryID = 0;
             $price =  floatval($price + $items[$itm]->getPrice() + $items[$itm]->getTaxAmount()); // Need to Add Tax
             $discountTotal = $discountTotal + $items[$itm]->getDiscountAmount();
             if($simpleAction == "by_percent"){
                $discountValue = $items[$itm]->getDiscountPercent();
                $finalValue = floatval($price - $discountTotal);
             }else{
                $discountValue = floatval($discountValue + $items[$itm]->getDiscountAmount());
                $finalValue = floatval($price - $discountValue);
             }
            $taxPercentage = $items[$itm]->getTaxPercent();
             $taxPer = floatval($taxPercentage);
         }else{
             if($type == "sid"){
                 //$itemDetail['SmryID'] = $smryId;
                 $mainSmryId = $smryId;
             }
             elseif($type == "did"){
                 //$itemDetail['RefSmryID'] = $smryId;
                 $refSmryID = $smryId;
             }
             elseif($type == "chain"){
                 //$itemDetail['FindingVariantID'] = $smryId;
                 $findingVariantID = $smryId;
             }
             elseif($type == "side1"){
                 //$itemDetail['MatchPairSmryID1'] = $smryId;
                 $matchPairSmryID1 = $smryId;
             }elseif($type == "side2"){
                 //$itemDetail['MatchPairSmryID2'] = $smryId;
                 $matchPairSmryID2 = $smryId;
             }
             if($orderDate){
                $deliveryTimelineDate = date('d-m-Y',strtotime($orderDate));
             }
             if($size){
                $ringSize = $size;
             }
             if($ctype){
                $chainType = $ctype;
             }
             if($clength){
                $chainLength = $clength;
             }
             if($eFont){
                $engravingFonts = $eFont;
             }
             if($eText){
                $engravingText = $eText;
             }
            //$discountTotal = $discountTotal + $items[$itm]->getDiscountAmount();
             $price =  floatval($price + $items[$itm]->getPrice() + $items[$itm]->getTaxAmount()); // Need to Add Tax
             $discountTotal = $discountTotal + $items[$itm]->getDiscountAmount();
             if($simpleAction == "by_percent"){
                $discountValue = $items[$itm]->getDiscountPercent();
                $finalValue = floatval($price - $discountTotal);
             }else{
                $discountValue = floatval($discountValue + $items[$itm]->getDiscountAmount());
                $finalValue = floatval($price - $discountValue);
             }
            $taxPercentage = $items[$itm]->getTaxPercent();
            $taxPer = floatval($taxPercentage);
         }
    }
    $itemDetail['SmryID'] = $mainSmryId;
    $itemDetail['MatchPairSmryID1'] = $matchPairSmryID1;
    $itemDetail['MatchPairSmryID2'] = $matchPairSmryID2;
    $itemDetail['FindingVariantID'] = $findingVariantID;
    $itemDetail['DiscountType'] = $discountType;
    $itemDetail['DiscountValue'] = $discountValue;
    $itemDetail['FinalValue'] = $finalValue;
    $itemDetail['TaxPer'] = $taxPer;
    $itemDetail['RefSmryID'] = $refSmryID;
    $itemDetail['EngravingFonts'] = $engravingFonts;
    $itemDetail['EngravingText'] = $engravingText;
    $itemDetail['ChainLength'] = $chainLength;
    $itemDetail['ChainType'] = $chainType;
    $itemDetail['RingSize'] = $ringSize;
    $itemDetail['DeliveryTimelineDate'] = $deliveryTimelineDate;
    $itemDetail['SmryUnqID'] = $randomKey;
    //print_R($itemDetail);
    $itemcollection[] = $itemDetail;
}
    //print_R($itemcollection);exit;
    $itemDetails['lstOrderItem'] = $itemcollection;
    $orderCollection = array_merge($orderDetail,$itemDetails);
   // print_R($orderCollection);
    $json = json_encode($orderCollection);
    echo "<pre><br/>";
    json_decode($json);
    echo  'POST JSON: '. $json;
   // exit;
    $ch = curl_init("http://52.7.153.114/SEAUtilityLive/SEAUtilityService.svc/OrderImportFaaya");
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec($ch);
    if (curl_error($ch)) {
        $error_msg = curl_error($ch);
    }
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $res = json_decode($result);
    $output = json_decode($res);
    echo "<br/>";
    echo "<br/>";
    echo "<br/>";
    echo "Output:";
    echo "<br/>";
    print_R($output);

    if($httpcode != 200){
        echo "Error status code ".$httpcode;
        if (isset($error_msg)) {
            echo $error_msg;exit;
        }
    }

    //echo $transId = $output->TransId;

    //echo $partyCode = $output->PartyCode;

    $order->setRefId($transId);
    $order->save();
    if($flag == 0){
        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $customer->setPartyCode($partyCode);
        $customer->save();
    }
    else{
        $customer = Mage::getModel('orderapi/customerguestuser')->load($customerId);
        $customer->setPartyCode($partyCode);
        $customer->save();
    }
}
else{
    if($orderRefId || $orderRefId == 0)
    {
        echo "Order has been sent already. Ref Id is : ".$orderRefId;
    }
    else{
        echo "please enter Order ID";
    }
}
