<?php
class Cda_Wizard_Model_Observer extends Mage_Core_Model_Abstract {
    public function addtocartData($data,$ringData){
        //echo "<pre/>";print_r($data);exit;

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $labelArr = array('weight'=>'Carat','stone_cut'=>'Cut','stone_quality'=>'Clarity','stone_color'=>'Color');
        $staticArray = array('sid','did','chain','wedding','side1','side2');
        if(isset($data['editid']) && $data['editid'] > 0){
            Mage::getModel('wizard/wizardoptionsmapping')->deleteCartItem($data['editid'],true);
            $randomInt = $data['editid'];
            //die('edit Mode');
        }else{
            $randomInt = strtotime("now");
        }


        //$cart = Mage::getSingleton('checkout/cart');
        $weddding = $diamondSet = array();
        $cnt = count($data);
        $promiseset =  false;
        if(isset($data['promise']) && $data['promise'] == 1 && $data['did'] > 0){
            $promiseset =  true;
            $diamondSet['item'] = $data['did'];
            $diamondSet['random'] = strtotime("now").$data['did'];
        }
        if(isset($data['wedding']) && $data['wedding'] > 0){
            $weddding['item'] = $data['wedding'];
            $weddding['random'] = strtotime("now").$data['wedding'];
        }
        $flg = 0;

        foreach ($data as $key=>$item) {
            if(!in_array($key, $staticArray)){
                continue;
            }
            if($key == 'wedding'){
                continue;
            }
            if(($promiseset) && $key == 'did'){
                continue;
            }
            $item = explode("-",$item);
            $id = $item[0];
            $flag = $item[1];
            if($id){
                $cart = Mage::getModel('checkout/cart');
                $cart->init();
                $subItem = Mage::getModel('catalog/product')->load($id);
                $smryItemType = $subItem->getResource()->getAttribute('smry_item_type')->getFrontend()->getValue($subItem);
                $product =  Mage::Helper('wizard')->getProductFromMaster($id);

                $smryId = $subItem->getSmryId();
                $variantId = $subItem->getVariantId();
                $options = array();
                if(!empty($weddding)){
                    $options['group']['matchingset'] = $weddding['random'];
                }
                if(!empty($diamondSet)){
                    $options['group']['matchingset'] = $diamondSet['random'];
                }

                $options['group']['sid'] = $randomInt;
                $options['group']['sku'] = $subItem->getSku();
                $options['group']['type'] = $key;
                $options['group']['smryid'] = $smryId;
                if (array_key_exists($id,$ringData)){
                    $options['group']['option'] = $ringData[$id];
                }
                if($flag == 0){
                    $mainPrice = $subItem->getPrice();
                    $options['group']['flag'] = $flag;
                    $options['group']['mainprice'] = $mainPrice;
                }else{
                    $specialPrice = $subItem->getSpecialPrice();
                    $options['group']['flag'] = $flag;
                    $options['group']['specialprice'] = $specialPrice;
                }
                /* Set Cart Item Detail */
                    $options['group']['construction'] = $product['construction'];
                    $options['group']['product_type'] = $product['product_type'];
                    $productType = strtolower($product['product_type']);
                    if($productType == "ring"){
                       $ring  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                       $options['group']['ring'] = $ring;
                    }
                    elseif($productType == "pendant"){
                       $pendent  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                       $options['group']['pendant'] = $pendent;
                    }
                    elseif($productType == "earring"){
                        $earring  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                        $options['group']['earring'] = $earring;
                    }
                    elseif($productType == "bracelets"){
                        $bracelets  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                        $options['group']['bracelets'] = $bracelets;
                    }
                    elseif($productType == "diamond" && $key != "side1" && $key != "side2"){
                        //$diamond  = $product['weight']." Carat"." ".$product['stone_color']." ".$product['stone_cut']." ".$product['stone_quality'];
                        //$diamond  = 'Carat : ' . $product['weight']." |".' Color : '.$product['stone_color']." |".' Cut :'.$product['stone_cut']." |".' Clarity : '.$product['stone_quality'];
                        $diamond  = 'Carat :<strong> ' . $product['weight']." </strong>|".' Color : <strong>'.$product['stone_color']." </strong>|".' Cut :<strong>'.$product['stone_cut']."</strong> |".' Clarity : <strong>'.$product['stone_quality'].'</strong>';
                        $options['group']['diamond'] = $diamond;
                    }
                    elseif($productType == "chain"){
                        $chain  = 'Length : <strong>' . $product['chain_length']."</strong> | ".'Type : <strong>'.$product['chain_type'].'</strong>';
                        $options['group']['chain_length'] = $product['chain_length'];
                        $options['group']['chain_type'] = $product['chain_type'];
                        $options['group']['chain'] = $chain;
                    }
                    elseif($key == "side1"){
                        $side1  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                        $options['group']['side1'] = $side1;
                    }
                    elseif($key == "side2"){
                        $side2  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                        $options['group']['side2'] = $side2;
                    }
                    elseif($key == "side2"){
                        $side2  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                        $options['group']['side2'] = $side2;
                    }
                    elseif($productType == "promise ring"){
                       $promiseRing  = 'Carat : <strong>' . $product['karat']."</strong> | ".'Color : <strong>'.$product['metal_color']."</strong> | ".'Style : <strong>' .$product['sub_category'].'</strong>';
                       $options['group']['promise'] = $promiseRing;
                    }
                    // For Match pair Strng
                    if($key == "side1"){
                           if($id > 0){
                               $sideGroup = $readConnection->fetchCol("select pid from wizardmaster where group_code IN (select group_code from wizardmaster where pid=".$id.")");
                               $sidestoneArr = $readConnection->fetchRow("select GROUP_CONCAT(stone_cut) as stone_cut,GROUP_CONCAT(weight) as weight,GROUP_CONCAT(stone_color) as stone_color,GROUP_CONCAT(stone_quality) as stone_quality from wizardmaster where pid IN (".implode(',', $sideGroup).") LIMIT 2");
                               $multiDia = array();
                               foreach ($sidestoneArr as $key=>$value):
                                    $multiDia[] = $labelArr[$key].': <strong>'.$value.'</strong>';
                               endforeach;
                               $matchingPair = implode(" | ", $multiDia);
                               $options['group']['matchpair'] = $matchingPair;
                           }
                    }
                // For Chain
                if($key == "chain" && $flg == 0){
                    $options['group']['variantid'] = $variantId;
                    $receiveDay =  Mage::Helper('wizard')->getOrderDate($subItem->getId());
                    $options['group']['orderdate'] = $receiveDay;
                    $flg = 1;
                }
                // For Ring and Diamond
                elseif($key == "sid" && $flg == 0){
                    $receiveDay =  Mage::Helper('wizard')->getOrderDate($subItem->getId());
                    $options['group']['orderdate'] = $receiveDay;
                    $flg = 1;
                }
                // For Diamond
                elseif($key == "did" && $flg == 0){
                    $insetData = array();
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$randomInt."','".$insetData."','".$options['group']['construction']."','".serialize($data)."')";
                    $writeConnection->query($insertEdit);

                    $receiveDay =  Mage::Helper('wizard')->getOrderDate($subItem->getId());
                    $options['group']['orderdate'] = $receiveDay;
                    $flg = 1;
                }



                if($options['group']['construction'] == 'Create your own' && $productType == 'ring'){
                    $insetData = array();
                    $insetData['diamond'] = Mage::getSingleton('core/session')->getSelectedValue();
                    $insetData['style'] = Mage::getSingleton('core/session')->getRingSelected();
                    $insetData['option'] = Mage::getSingleton('core/session')->getRingData();
                    if(isset($data['side1']) && isset($data['side2'])){
                        $insetData['sidestone'] = Mage::getSingleton('core/session')->getSidestone();
                    }
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$randomInt."','".$insetData."','".$options['group']['construction']."','".serialize($data)."')";
                    $writeConnection->query($insertEdit);
                }elseif($options['group']['construction'] == 'PRESET'){
                    $insetData = array();
                    $insetData['option'] = Mage::getSingleton('core/session')->getRingData();
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$randomInt."','".$insetData."','".$options['group']['construction']."','".serialize($data)."')";
                    $writeConnection->query($insertEdit);
                }elseif($options['group']['construction'] == 'Promise Ring'){
                    $insetData = array();
                    $insetData['promise'] = Mage::getSingleton('core/session')->getPromiseRing();
                    $insetData['option'] = Mage::getSingleton('core/session')->getRingData();
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$randomInt."','".$insetData."','".$options['group']['construction']."','".serialize($data)."')";
                    $writeConnection->query($insertEdit);
                }
                /* End code for order date*/
                $subItem->addCustomOption('setting', serialize($options));
                Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
                $cart->addProduct($subItem, array('qty' => 1));
                try{
                    if($cart->save()){
                        $quoteId = 0;
                        $writeConnection->query('insert skumapping (editid,pid,sku,quoteid) values('.$randomInt.','.$id.',"'.$subItem->getSku().'",'.$quoteId.')');
                    }
                }catch (Exception $e) {
                    throw new Exception($e);
                }
            }
        }
        if(!empty($weddding)){
            $cart = Mage::getModel('checkout/cart');
            $cart->init();
            $subItem = Mage::getModel('catalog/product')->load($weddding['item']);
            $options = array();
            $smryId = $subItem->getSmryId();
            $options['group']['sid'] = $weddding['random'];
            $options['group']['type'] = 'wedding';
            $options['group']['smryid'] = $smryId;
            $options['group']['matchingset'] = $randomInt;
            $receiveDay =  Mage::Helper('wizard')->getOrderDate($subItem->getId());
            $options['group']['orderdate'] = $receiveDay;
            $subItem->addCustomOption('setting', serialize($options));
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            $cart->addProduct($subItem, array('qty' => 1));
            try{
                if($cart->save()){
                    $insetData = array();
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$weddding['random']."','".$insetData."','','".serialize($data)."')";
                    $writeConnection->query($insertEdit);

                    $quoteId = 0;
                    $writeConnection->query('insert skumapping (editid,pid,sku,quoteid) values('.$weddding['random'].','.$weddding['item'].',"'.$subItem->getSku().'",'.$quoteId.')');
                }
            }catch (Exception $e) {
                throw new Exception($e);
            }
        }
        if(!empty($diamondSet)){
            $cart = Mage::getModel('checkout/cart');
            $cart->init();
            $subItem = Mage::getModel('catalog/product')->load($diamondSet['item']);
            $product =  Mage::Helper('wizard')->getProductFromMaster($diamondSet['item']);
            $productType = strtolower($product['product_type']);
            $options = array();
            $smryId = $subItem->getSmryId();
            $options['group']['sid'] = $diamondSet['random'];
            $options['group']['type'] = 'did';
            $options['group']['smryid'] = $smryId;
            $options['group']['matchingset'] = $randomInt;
            $receiveDay =  Mage::Helper('wizard')->getOrderDate($subItem->getId());
            $options['group']['orderdate'] = $receiveDay;
             if($productType == "diamond"){
                //$diamond  = $product['weight']." Carat"." ".$product['stone_color']." ".$product['stone_cut']." ".$product['stone_quality'];
                $diamond  = 'Carat :<strong> ' . $product['weight']." </strong>|".' Color : <strong>'.$product['stone_color']." </strong>|".' Cut :<strong>'.$product['stone_cut']."</strong> |".' Clarity : <strong>'.$product['stone_quality'].'</strong>';
                $options['group']['diamond'] = $diamond;
            }
            $subItem->addCustomOption('setting', serialize($options));
            Mage::app()->setCurrentStore(Mage_Core_Model_App::DISTRO_STORE_ID);
            $cart->addProduct($subItem, array('qty' => 1));
            try{
                if($cart->save()){
                    $insetData = array();
                    $insetData = serialize($insetData);
                    $insertEdit = "insert wizardedit (editid,data,construction,params) values('".$diamondSet['random']."','".$insetData."','','".serialize($data)."')";
                    $writeConnection->query($insertEdit);

                    //$quoteId = $cartAdded->getQuote()->getData('entity_id');
                    $quoteId = 0;
                    $writeConnection->query('insert skumapping (editid,pid,sku,quoteid) values('.$diamondSet['random'].','.$diamondSet['item'].',"'.$subItem->getSku().'",'.$quoteId.')');
                }
            }catch (Exception $e) {
                throw new Exception($e);
            }


        }
        return true;

    }

    public function updateProduct(Varien_Event_Observer $observer){
        $quote = $observer->getEvent()->getQuote();
        $item = $observer->getQuoteItem();
        $product_id = $item->getProductId();
        $_product = Mage::getModel('catalog/product')->load($product_id);
        if($_product){
            $options = Mage::getModel('catalog/product_option')->getProductOptionCollection($_product);
            if ($additionalOption = $item->getProduct()->getCustomOption('setting'))
            {
               $additionalOptions =  unserialize($additionalOption->getValue());
               $flag = $additionalOptions['group']['flag'];
               $mainPrice = $additionalOptions['group']['mainprice'];
               $mainSpecialPrice = $additionalOptions['group']['specialprice'];
               if($flag ==0){
                  $item->setCustomPrice($mainPrice);
                  $item->setOriginalCustomPrice($mainPrice);
                  $item->getProduct()->setIsSuperMode(true);
               }else{
                  $item->setCustomPrice($mainSpecialPrice);
                  $item->setOriginalCustomPrice($mainSpecialPrice);
                  $item->getProduct()->setIsSuperMode(true);
               }
            }
        }
    }
}
?>
