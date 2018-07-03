<?php
class Cda_Wizard_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction() {
        $params = $this->getRequest()->getParam('shape');
        if($params){
            Mage::getSingleton('core/session')->unsRingSelected();
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    public function checkcartlockAction()
    {
        $sidArr = Mage::getModel('wizard/checklock')->checkLock();
        if(!empty($sidArr)){
            Mage::getSingleton('core/session')->setExistDid($sidArr);
            Mage::getSingleton('core/session')->addError('We can not proceed your order right now, try again after some time');
        }
        echo $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($sidArr));exit;
    }

    public function taxAmountAction()
    {
        $cart = Mage::helper('checkout')->getQuote();
        $taxpercent = (($cart->getData('grand_total')-$cart->getData('base_subtotal'))*100)/$cart->getData('grand_total');
        $tax = $cart->getShippingAddress()->getData('tax_amount');
        $tax = Mage::helper('core')->currency($tax, true, false).'('.round($taxpercent).'%)';
        echo $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($tax));exit;
    }
    public function compareproductAction()
    {
        $response = array();
        $productId = $this->getRequest()->getParam('pid');

        $product = Mage::getModel('catalog/product')
        ->setStoreId(Mage::app()->getStore()->getId())
        ->load($productId);

        if ($product->getId()) {
            $response['status'] = 'SUCCESS';
            $response['pid'] = $productId;
            $response['message'] = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
            Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
            Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
            Mage::helper('catalog/product_compare')->calculate();

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));return;
        }else{
            $response['status'] = 'ERROR';
            $response['pid'] = $productId;
            $response['message'] = $this->__('Something wrong with this product');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));return;
        }
    }
    public function checkDiamondAction()
    {
        $backData = '0';
        $did = Mage::app()->getRequest()->getParam('did');
        $variantId = Mage::app()->getRequest()->getParam('variantid');
        $editid = Mage::app()->getRequest()->getParam('editid');
        if($editid){
            $productId = Mage::helper('wizard')->existDiamonds(true);
        }else{
            $productId = Mage::helper('wizard')->existDiamonds(false);
        }

        if(in_array($did, $productId)){
            $result = Mage::helper('wizard')->lowestPriceDiamond($variantId,$productId);
            if($result){
                $backData = $result;
            }else{
                $backData = '00';
            }
        }
        echo $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($backData));exit;
    }


    public function updateRingsizeAction(){
        $data = Mage::app()->getRequest()->getParams();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $existData = $readConnection->fetchRow('select * from wizardedit where editid='.$data['randomkey']);
        if($existData && $existData['construction'] == 'PRESET'){
            $dataparams = unserialize($existData['data']);
            $params = unserialize($existData['params']);
            $productId = $params['product'];
            $dataparams['option'][$productId]['size'] = $data['value'];
            $dataparams = serialize($dataparams);
            $updateEdit = "update wizardedit set data='".$dataparams."' where id=".$existData['id'];
            $writeConnection->query($updateEdit);
        }elseif($existData && $existData['construction'] == 'Create your own'){
            $params = unserialize($existData['data']);
            $style = unserialize($params['style']);
            $style = $style['productId'];
            $option = $params['option'];
            $params['option'][$style]['size'] =  $data['value'];
            $params = serialize($params);
            $updateEdit = "update wizardedit set data='".$params."' where id=".$existData['id'];
            $writeConnection->query($updateEdit);
        }

      $cart = Mage::getModel('checkout/cart')->getQuote();
      foreach($cart->getAllVisibleItems() as $item){
          if($item->getId() == $data['itemid']){
                $buyRequest = $item->getOptionByCode('setting');
                $buyRequestArr = unserialize($buyRequest->getValue());
                $buyRequestArr['group']['option']['size'] = $data['value'];
                $buyRequest->setValue(serialize($buyRequestArr))->save();
                break;
          }
      }
    }

    public function selectionAction(){
        $params = $this->getRequest()->getParams();
        $data = Mage::getModel('wizard/wizardoptionsmapping')->getProduct($params);
        echo $data;
    }

    public function setinscriptionAction()
    {
        $params = $this->getRequest()->getParams();
        $ringData = Mage::getSingleton('core/session')->getRingData();
        $ringData[$params['pid']]['text'] = $params['text'];
        $ringData[$params['pid']]['fontfamily'] = $params['fontfamily'];
        Mage::getSingleton('core/session')->setRingData($ringData);
    }

    public function setinscriptionsizeAction()
    {
        $params = $this->getRequest()->getParams();
        $ringData = Mage::getSingleton('core/session')->getRingData();
        $ringData[$params['pid']]['size'] = $params['size'];
        Mage::getSingleton('core/session')->setRingData($ringData);
    }
    public function editcartAction()
    {
        //$staticArray = array('sid','did','chain','wedding','side1','side2');
        $staticArray = array('did','side1');
        $editid = $this->getRequest()->getParam('editid');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $existData = $readConnection->fetchRow('select * from wizardedit where editid='.$editid);
        Mage::helper('wizard')->clearselection();
        if(!empty($existData) && $existData['construction'] == 'Create your own'){
            $json  = unserialize($existData['data']);
            Mage::getSingleton('core/session')->setSelectedValue($json['diamond']);
            Mage::getSingleton('core/session')->setRingSelected($json['style']);
            Mage::getSingleton('core/session')->setRingData($json['option']);
            if(isset($json['sidestone'])){
                Mage::getSingleton('core/session')->setSidestone($json['sidestone']);
            }
            $this->_redirect('wizard/completed/index/editid/'.$editid);
        }elseif(!empty($existData) && $existData['construction'] == 'Promise Ring'){
            $json  = unserialize($existData['data']);
            //$json['promise']['editid'] = $editid;
            Mage::getSingleton('core/session')->setPromiseRing($json['promise']);
            Mage::getSingleton('core/session')->setRingData($json['option']);
            $this->_redirect('wizard/completedset/index/editid/'.$editid);
        }elseif (!empty($existData) && $existData['construction'] == 'PRESET') {
            $json  = unserialize($existData['data']);
            Mage::getSingleton('core/session')->setRingData($json['option']);
            $params  = unserialize($existData['params']);
            $url = 'catalog/product/view/id/'.$params['product'].'/editid/'.$editid;
            $array = array();
            foreach ($params as $key=>$value) {
                if(!in_array($key, $staticArray)){
                    continue;
                }
                $key = ($key == 'side1')?'side':$key;
                $array[] = $key.'/'.reset(explode('-', $value));
            }
            if(!empty($array)){
                $url .= '/'.implode("/", $array);
            }
            $this->_redirect($url);
        }else{

            if(!empty($existData)){
                $params  = unserialize($existData['params']);
                if(count($params) == 1 && isset($params['did'])){
                    Mage::getSingleton('core/session')->setPromiseRing(array('did'=>$params['did']));
                    Mage::getSingleton('core/session')->setSelectedValue(serialize(array('productId'=>$params['did'])));
                    ///Mage::getSingleton('core/session')->setPromiseRing(array('did'=>$params['did']));
                    $this->_redirect('wizard/diamond/index/shape/all/editid/'.$editid);
                }elseif(count($params) > 1 && isset($params['promise'])){
                    Mage::getSingleton('core/session')->setPromiseRing(array('did'=>$params['did']));
                    Mage::getSingleton('core/session')->setSelectedValue(serialize(array('productId'=>$params['did'])));
                    //Mage::getSingleton('core/session')->setPromiseRing(array('did'=>$params['did']));
                    $this->_redirect('wizard/diamond/index/shape/all/editid/'.$editid);
                }else{
                    $this->_redirect('checkout/cart');
                }
            }else{
                $this->_redirect('checkout/cart');
            }
        }

    }



    public function deletecartAction()
    {
        $deleteid = $this->getRequest()->getParam('deleteid');
        $deleteid = explode("-", $deleteid);
        //echo "<pre/>";print_r($deleteid);exit;
        foreach ($deleteid as $id) {
            Mage::getModel('wizard/wizardoptionsmapping')->deleteCartItem($id,false);
        }
        $this->_redirect('checkout/cart');
    }

    public function confirmdeleteAction()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $deleteid = $this->getRequest()->getParam('editid');
        $quoteid = $this->getRequest()->getParam('quoteid');
        $items = Mage::getModel('sales/quote_item')->getCollection()->addFieldToSelect('item_id')->addFieldToFilter('quote_id',$quoteid);
        $sidreturn = false;
        $errormessage = '';
        foreach($items->getData() as $_item) {
            $option = Mage::getModel('sales/quote_item_option')->getCollection()->addFieldToSelect('value')->addFieldToFilter('item_id',$_item['item_id'])->addFieldToFilter('code','setting');
            $option = $option->getData();
            $option = $option[0]['value'];
            $option = unserialize($option);


            if(isset($option['group']['matchingset']) && $option['group']['matchingset'] == $deleteid && $option['group']['type'] == 'sid'){

                 $existData = $readConnection->fetchRow('select * from wizardedit where editid='.$option['group']['sid']);
                 if(!empty($existData)){
                    if($existData['construction'] != 'Create your own' && $option['group']['matchingset'] == $deleteid && $option['group']['type'] == 'sid'){
                        $errormessage = 'Associated promise ring will also be deleted, are you sure?';
                        $sidreturn = $option['group']['sid'];
                        break;
                    }
                 }else{
                    $errormessage = 'Associated promise ring will also be deleted, are you sure?';
                    $sidreturn = $option['group']['sid'];
                    break;
                 }
            }elseif(isset($option['group']['matchingset']) && $option['group']['matchingset'] == $deleteid && $option['group']['type'] == 'wedding'){
                $errormessage = 'Associated wedding band will also be deleted, are you sure?';
                $sidreturn = $option['group']['sid'];
                    break;
            }
        }

        if($sidreturn){
            $sidreturn = array('url'=>Mage::app()->getStore()->getUrl('wizard/index/deletecart/deleteid/'.$deleteid.'-'.$sidreturn),'flag'=>1,'errormsg'=>$errormessage);
        }else{
            $sidreturn = array('url'=>Mage::app()->getStore()->getUrl('wizard/index/deletecart/deleteid/'.$deleteid),'flag'=>0,'errormsg'=>$errormessage);
        }
        echo json_encode($sidreturn);

    }


    public function sortupdateAction(){
        $category = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('is_active', 1)->addAttributeToFilter('name', 'Diamond');
        $categoryId =  $category->getFirstItem()->getId();
        $pageOffset = 0;

        $sort = $this->getRequest()->getParam('sort');
        $sort = ($sort == 'descending')?'desc':'asc';
        $sortby = $this->getRequest()->getParam('sortby');
        $sortArray = array('shapesort'=>'stone_shape','caratsort'=>'weight','colorsort'=>'stone_color','claritysort'=>'stone_quality','cutsort'=>'stone_cut','pricesort'=>'price');
        $diamondList =  Mage::getSingleton('core/session')->getDiamondList();
        $diamondList = unserialize($diamondList);

        //$diamondList = array_slice($diamondList, 0,10);
        Mage::getModel('wizard/wizardoptionsmapping')->sortBy($sortArray[$sortby],$diamondList,$sort);
        Mage::getSingleton('core/session')->setDiamondList(serialize($diamondList));

        $newJsonArr = array();
        $jsonArr = Mage::getModel('wizard/wizardoptionsmapping')->getDiamondByPage($pageOffset,$categoryId);
        $newJsonArr['product'] = $jsonArr;
        echo json_encode($newJsonArr);
    }




    public function chainoptionAction(){
        $pid = $this->getRequest()->getParam('pid');
        $length = $this->getRequest()->getParam('length');
        $data = Mage::helper('wizard')->getChainType($pid,$length);

        $html = '';
        foreach ($data as $key=>$option) {
            $html .= '<option value="'.$option['chain_type'].'">'.$option['chain_type'].'</option>';
        }

        $chainId = Mage::helper('wizard')->getChainId($pid,$length,$data[0]['chain_type']);
        echo json_encode(array('html'=>$html,'id'=>$chainId['pid']));
        $promiseSet = Mage::getSingleton('core/session')->getPromiseRing();
        $promiseSet['chain'] = $chainId['pid'];
        Mage::getSingleton('core/session')->setPromiseRing($promiseSet);
    }

    public function chainAction(){
        $pid = $this->getRequest()->getParam('pid');
        $length = $this->getRequest()->getParam('length');
        $productprice = $this->getRequest()->getParam('productprice');
        $data = Mage::helper('wizard')->getChainType($pid,$length);

        $html = '';
        foreach ($data as $key=>$option) {
            $checked = '';
            if($key == 0){
                $checked = 'checked="checked"';
            }
             $img = Mage::getBaseUrl('media').'chaintype/'.str_replace(" ","-",strtolower($option)).'.png';
        $html .= '<li>
            <input type="radio" class="radio" id="chain_type-'.str_replace(" ","-",strtolower($option)).'" name="chain_type[]" value="'.$option.'" '.$checked.' />
            <label for="chain_type-'.str_replace(" ","-",strtolower($option)).'">
                <span class="image">
                    <img src="'.$img.'">
                </span>
                <span>'.$option.'</span>
            </label>
        </li>';
        }
        $chainId = Mage::helper('wizard')->getChainId($pid,$length,$data[0]['chain_type']);

        $styleTotal = $productprice+$chainId['price'];
        $price = Mage::helper('core')->currency($styleTotal, true, false);

        echo json_encode(array('html'=>$html,'id'=>$chainId['pid'],'price'=>$price));
        $promiseSet = Mage::getSingleton('core/session')->getPromiseRing();
        $promiseSet['chain'] = $chainId['pid'];
        Mage::getSingleton('core/session')->setPromiseRing($promiseSet);
    }


    public function setchainidAction(){
        $pid = $this->getRequest()->getParam('pid');
        $length = $this->getRequest()->getParam('length');
        $type = $this->getRequest()->getParam('type');
        $productprice = $this->getRequest()->getParam('productprice');
        $chainId = Mage::helper('wizard')->getChainId($pid,$length,$type);
        //$styleTotal = $productprice+$chainId['price'];
        $styleTotal = $chainId['price'];
        $price = Mage::helper('core')->currency($styleTotal, true, false);
        echo json_encode(array('id'=>$chainId['pid'],'price'=>$price));
    }

    public function ringselectionAction(){
        $params = $this->getRequest()->getParams();
        $data = Mage::getModel('wizard/wizardoptionsmapping')->getRingProduct($params);
        echo $data;
    }

    public function popuphtmlAction(){
        $pid = $this->getRequest()->getParam('pid');
        echo Mage::app()->getLayout()->createBlock('core/template')->setData('pid', $pid)->setTemplate('wizard/popup.phtml')->toHtml();
    }

    public function ringpopupAction(){
        $pid = $this->getRequest()->getParam('pid');
        echo Mage::app()->getLayout()->createBlock('core/template')->setData('pid', $pid)->setTemplate('wizard/ringpopup.phtml')->toHtml();
    }

    public function clearselectionAction(){
        Mage::helper('wizard')->clearselection();
        echo "All Session data cleared...";
    }

    public function setringdataAction(){
        $ringData =  Mage::getSingleton('core/session')->getRingData();
        $params = $this->getRequest()->getParams();

        //$params = array('id'=>'9_2348','pid'=>'46222','metaloption'=>0,'shapeoption'=>1);
        //$params = array('id'=>'150_356_29','pid'=>'36195');

        $idE = explode('_', $params['id'],2);

        if(!empty($idE) && count($idE) > 1){
            if($params['metaloption'] == 'true'){
                $data = Mage::getModel('wizard/wizardoptionsmapping')->setRingData($params);
            }elseif($params['shapeoption'] == 'true'){
                $data = Mage::getModel('wizard/wizardoptionsmapping')->setRingbyDiamondShape($params);
            }else{
                $ringData[$params['pid']][$idE[0]] = $idE[1];
                Mage::getSingleton('core/session')->setRingData($ringData);
            }
        }
        exit;
    }


    public function addtocartAction(){
        $cart = Mage::getModel('checkout/cart');
        $cart->init();
        $data = Mage::app()->getRequest()->getParams();
        $ringData =  Mage::getSingleton('core/session')->getRingData();
        if(isset($data['ring-size'])){
            $ringData[$data['product']]['size'] = $data['ring-size'];
        }
        $dataAdded =  Mage::getModel('wizard/observer')->addtocartData($data,$ringData);
        if($dataAdded){
            Mage::helper('wizard')->clearselection();
            if(isset($data['editid']) && $data['editid'] > 0){
                Mage::getSingleton('core/session')->addSuccess('Product successfully updated to your shopping bag');
            }else{
                Mage::getSingleton('core/session')->addSuccess('Product successfully added to your shopping bag');
            }

          $this->_redirect('checkout/cart');
        }else{
          $this->_redirect("/");
        }

    }
    public function getpopupproductAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function promiseAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function promiseupdateAction() {
        $metal = $this->getRequest()->getParam('metal');
        $editid = $this->getRequest()->getParam('editid');
        $karat = $this->getRequest()->getParam('karat');
        $promisestep = $this->getRequest()->getParam('promisestep');
        $promiseid =  Mage::getModel('wizard/promise')->updatePromise($metal,$karat);

        if($promisestep){
            $url =  Mage::getUrl('wizard/index/promise/prid/'.$promiseid.'/prms/1');
        }else{
            $url = Mage::getUrl('wizard/index/promise/prid/'.$promiseid);
        }
        if($editid){
            $url = $url.'editid/'.$editid;
        }
        echo $url;
    }


    public function productimageAction(){
        $pid = $this->getRequest()->getParam('pid');
        $product = Mage::getModel('catalog/product')->load($pid);
        $categoryIds = $product->getCategoryIds();
        $catArr = array();
        if(count($categoryIds)>0){
            $categories = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter('entity_id', $categoryIds);

            foreach ($categories as $category) {
                $catArr[] = $category->getName();
            }
        }
        $loggedIn = (!Mage::getSingleton('customer/session')->isLoggedIn())?false:true;
        $img = '<img src="'.Mage::helper('catalog/image')->init($product, 'image', $product->getImage())->resize(200, 200).'">';
        $data = array('img'=>$img,'name'=>$catArr,'loggedin'=>$loggedIn);
        echo json_encode($data);
        exit;
    }
}