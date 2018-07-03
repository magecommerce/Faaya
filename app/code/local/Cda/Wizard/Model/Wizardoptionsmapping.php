<?php

class Cda_Wizard_Model_Wizardoptionsmapping extends Mage_Core_Model_Abstract
{
    public $_pageLimit;
    public $_styleListId;
    public $_resource;
    public $_readConnection;
    public $_productType;
    protected function _construct(){
       //$this->_init("wizard/wizardoptionsmapping");
        $this->_pageLimit = 18;
        $this->_styleListId = $this->getProductstyle();
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection('core_read');
        $this->_productType = Mage::helper('wizard')->getProductTypeList();
    }

    public function deleteCartItem($deleteid,$matchflag)
    {
        $cart = Mage::helper('checkout/cart')->getCart();
        $quoteItems = $cart->getItems();
        $totalItem = array();
        foreach($quoteItems as $item){
            $additionalOption = $item->getProduct()->getCustomOption('setting');
            $additionalOption = unserialize($additionalOption->getValue());
            $totalItem[$additionalOption['group']['sid']][] = $item->getItemId();
        }
        if(count($totalItem)>1){
            foreach($quoteItems as $item){
                $additionalOption = $item->getProduct()->getCustomOption('setting');

                $additionalOption = unserialize($additionalOption->getValue());

                if($additionalOption['group']['sid'] == $deleteid){
                    Mage::helper('wizard')->deleteCartItemRow($deleteid);
                    $cart->removeItem($item->getItemId())->save();
                }
                if($matchflag == 1 && $additionalOption['group']['matchingset'] == $deleteid){
                    Mage::helper('wizard')->deleteCartItemRow($deleteid);
                    $cart->removeItem($item->getItemId())->save();
                }
            }
        }else{
            try{
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $quote->removeAllItems();
                $quote->save();
            }catch (Exception $e)
            {
                Mage::log('Failed to remove item from cart'.$e.'.');
            }
        }
    }

    public function getProductstyle(){
        $itemType = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'smry_item_type');
        $options = $itemType->getSource()->getAllOptions(false);
        $styleId = 0;
        foreach ($options as $opt) {
            if($opt['label'] == 'style'){
                $styleId = $opt['value'];
                break;
            }
        }
        return $styleId;
    }


    public function getRingProduct($values){
        if($values['page'] == 1){
            $pageOffset = 0;
        }else{
            $pageOffset = ($values['page']*$this->_pageLimit)-$this->_pageLimit;
        }

        $parentCat = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('is_active', 1)->addAttributeToFilter('name', 'Jewelry');
        $parentCatId =  $parentCat->getFirstItem()->getId();
        $category = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('is_active', 1)->addAttributeToFilter('name', 'Ring')->addAttributeToFilter('parent_id',$parentCatId);
        $categoryId =  $category->getFirstItem()->getId();

        $newJsonArr = array();

        $b = serialize($values);
        Mage::getSingleton('core/session')->setRingSelected($b);
        Mage::getSingleton('core/session')->unsSidestone();
        if($values['productId'] > 0){
            return json_encode($newJsonArr);
        }

        $variantId = $values['variantId'];
        $shapeSelected = false;
        if($variantId == 0 && isset($values['value']['STONE_SHAPE'])){
            $shapeSelected = true;
        }

        if($values['page'] > 1){
            $html = $this->getRingByPage($pageOffset,$categoryId,$shapeSelected,$variantId);
            $newJsonArr['product'] = $html;
            return json_encode($newJsonArr);
        }



        if(isset($values['value']['STONE_SHAPE'])){
            if($values['editid']){
                $existDiamond = Mage::helper('wizard')->existDiamonds(true);
            }else{
                $existDiamond = Mage::helper('wizard')->existDiamonds(false);
            }
            if(!empty($existDiamond)){
                $query = 'SELECT sku FROM wizardmaster where stone_shape IN ("'.implode('","',$values['value']['STONE_SHAPE']).'") and LOWER(product_type) = "'.$this->_productType['diamond'].'" and pid NOT IN ('.implode(",",$existDiamond).')';
            }else{
                $query = 'SELECT sku FROM wizardmaster where stone_shape IN ("'.implode('","',$values['value']['STONE_SHAPE']).'") and LOWER(product_type) = "'.$this->_productType['diamond'].'"';
            }

            $diamondData = $this->_readConnection->fetchCol($query);

            $query = 'SELECT DISTINCT(pid) FROM wizardrelation where variant_refsmryid IN ("'.implode('","',$diamondData).'") and type = "material" and special_character="C"';
            $diamondStyle = $this->_readConnection->fetchCol($query);
        }

        $metalSelected = false;
        $query = 'SELECT pid,price,special_price,image,variant_remark,variant_name FROM wizardmaster where IF(  `center_diamond` =1, special_character LIKE  "%C%", 1 ) AND IF(  `matchpair` =1, special_character LIKE  "%M%", 1 ) AND ';
        $where = array('LOWER(product_type) = "'.$this->_productType['ring'].'"');
        foreach ($values['value'] as $key=>$value) {
            if($key == 'STONE_SHAPE'){ continue; }
            if($key == 'PRODUCT_SIZE'){ continue; }
            $key = strtolower($key);
            if($key == 'metal_color'){
                $metalSelected = true;
                $metalwhere = array();
                foreach ($value as $mk) {
                    $value = explode('-', $mk);
                    $metalwhere[]= '('.$key.' = "'.$value[0].'" and karat = "'.$value[1].'")';
                }
                $where[] = '('.implode(' OR ', $metalwhere).')';
            }else{
                $where[]= $key.' IN ("'.implode('","',$value).'")';
            }
        }
        /*$productsize = explode('_', $values['value']['PRODUCT_SIZE'][0]);
        $where []= 'product_size > "'.$productsize[0].'" and product_size < "'.$productsize[1].'"';*/
        $where []= 'construction = "Create your own"';
        $where []= 'lower(sub_category) != "wedding band"';
        $where []= 'lower(sub_category) != "band"';

        if(!$metalSelected && !$shapeSelected && $variantId == 0){
            $where []= 'is_basevariant = 1';
        }

        $k14 = false;
        $defaultInd = false;
        if($variantId == 0){
            if($shapeSelected && !$metalSelected){
                $k14 = true;
            }elseif (!$shapeSelected && $metalSelected) {
                $defaultInd = true;
            }
        }else{
            if (!$metalSelected) {
                $k14 = true;
            }
        }

        if($defaultInd){
            $where[] = 'is_default = 1';
        }
        if($k14){
            $karatLabel = '14K';
            $metalClabel = 'White Gold';
            if(!$metalSelected){
                $where[] = 'karat = "'.$karatLabel.'"';
                $where[] = 'metal_color = "'.$metalClabel.'"';
            }
        }

        if(isset($values['value']['STONE_SHAPE'])){
            if(!empty($diamondStyle)){
                $where[] = 'pid IN ('.implode(',', $diamondStyle).')';
            }else{
                $where = array();
            }
        }
        if($variantId != 0){
            $relationRing = $this->_readConnection->fetchCol('select DISTINCT pid from wizardrelation where    variant_refsmryid="'.$variantId.'"');
            if(!empty($relationRing)){
                $where[] = 'pid IN ('.implode(',', $relationRing).')';
            }else{
                $where = array();
            }
        }

        $mainQuery = '';
        if(!empty($where)){
            $whereQry = implode(' AND ', $where);
            $mainQuery = $query.$whereQry;
            $shapeQuery = 'SELECT sub_category,COUNT(sub_category),MIN(price) as min_price,MAX(price) as max_price  FROM wizardmaster where IF(  `center_diamond` =1, special_character LIKE  "%C%", 1 ) AND IF(  `matchpair` =1, special_character LIKE  "%M%", 1 ) AND '.$whereQry.'  group by sub_category';
            $shapedata = $this->_readConnection->fetchAll($shapeQuery);
        }else{
            $alldata = array();
            $shapedata = array();
        }


            $totalCount = 0;
            foreach ($shapedata as $value) {
                $totalCount += $value['COUNT(sub_category)'];
                $subcatvalue = str_replace(" ", "_", $value['sub_category']);
                $shapeimage = Mage::helper('wizard')->getAttImage(array('code'=>'SUB_CATEGORY','type'=>'RING'),$subcatvalue);
        $productShapes .= '<li class="item">
                                <div class="image">'.$shapeimage.'</div>
                                <div class="price-box">
                                    <span>'.Mage::helper('core')->currency($value['min_price'], true, false).' - '.Mage::helper('core')->currency($value['max_price'], true, false).'</span>
                                </div>
                                <div class="availability">('.$value['COUNT(sub_category)'].' styles available)</div>
                            </li>';
            }

        Mage::getSingleton('core/session')->setVarianList(serialize($mainQuery));
        Mage::getSingleton('core/session')->setMetalSelected($metalSelected);

        $html = $this->getRingByPage($pageOffset,$categoryId,$shapeSelected,$variantId);
        $newJsonArr['product'] = $html;
        $newJsonArr['option'] = $productShapes;
        $newJsonArr['count'] = $totalCount;
        $newJsonArr['price'] = Mage::helper('core')->currency($minPrice, true, false).' - '.Mage::helper('core')->currency($maxPrice, true, false);
        return json_encode($newJsonArr);
    }

    public function getSidestone($values)
    {
        $category = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('is_active', 1)->addAttributeToFilter('name', 'Diamond');
        $categoryId =  $category->getFirstItem()->getId();

        if($values['page'] == 1){
            $pageOffset = 0;
        }else{
            $pageOffset = ($values['page']*$this->_pageLimit)-$this->_pageLimit;
        }

        $newJsonArr = array();
        if($values['page'] > 1){
            $jsonArr = $this->setSideStoneList($pageOffset,$categoryId);
            $newJsonArr['product'] = $jsonArr;
            return json_encode($newJsonArr);
        }

       /* $b = serialize($values);
        Mage::getSingleton('core/session')->setSidestone($b);*/
        $jsonArr = array();
        $newJsonArr = array();


        $pid = $values['ringid'];

        $relationRing = array();
        /*$relationRing = $this->_readConnection->fetchCol("select DISTINCT variant_refsmryid from wizardrelation where type='material' and special_character='M' and pid=".$pid." group by variant_refsmryid");

        $relationRing = Mage::Helper('wizard')->getIdfromRefSmy(implode(',', $relationRing));*/
        $relationRing = $this->_readConnection->fetchCol("select group_code from wizardrelation where type='material' and special_character='M' and pid=".$pid." group by group_code HAVING COUNT(group_code)=2");

        if(!empty($relationRing)){
            $groupsetting = ' and group_code IN ("'.implode('","',$relationRing).'")';
            $relationRing = $this->_readConnection->fetchCol("select DISTINCT variant_refsmryid from wizardrelation where type='material' and special_character='M'".$groupsetting);
        }

        $query = 'SELECT * FROM wizardmaster where ';
        $where = array('LOWER(product_type) = "'.$this->_productType['diamond'].'"');
        $where[] = 'group_code != ""';
        foreach ($values['value'] as $key=>$value) {
            $key = strtolower($key);
            $where []= $key.' IN ("'.implode('","',$value).'")';
        }
        $where []= 'weight > "'.$values['caratArr'][0].'" and weight < "'.$values['caratArr'][1].'"';
        //$where []= 'pid IN ('.implode(",",$relationRing).')';
        $where []= 'sku IN ("'.implode('","',$relationRing).'")';
        if($values['editid']){
            $existDiamond = Mage::helper('wizard')->existDiamonds(true);
        }else{
            $existDiamond = Mage::helper('wizard')->existDiamonds(false);
        }
        if(!empty($existDiamond)){
            $where []= 'pid NOT IN ('.implode(",",$existDiamond).')';
        }
        $whereQry = implode(' AND ', $where);

        if(empty($relationRing)){
            $alldata = array();
        }else{
            $alldata = $this->_readConnection->fetchALL($query.$whereQry.' order by
field( `stone_shape`, "ROUND", "PRINCESS", "CUSHION","OVAL","EMERALD"),weight ASC');
        }
        $updatedData = array();
        foreach ($alldata as $value) {
            $updatedData[$value['group_code']][] = $value;
        }
        $pidArr = array();
        foreach ($updatedData as $key=>$value) {
            if(count($value) != 2){
                unset($updatedData[$key]);
            }else{
                $pidArr[] = $value[0]['pid'];
                $pidArr[] = $value[1]['pid'];
            }
        }

        $shapeQuery = 'SELECT stone_shape,COUNT(stone_shape),MIN(price) as min_price,MAX(price) as max_price  FROM wizardmaster where '.$whereQry.' and pid IN ('.implode(",",$pidArr).') group by stone_shape order by
field( `stone_shape`, "ROUND", "PRINCESS", "CUSHION","OVAL","EMERALD"),weight ASC';
        if(empty($relationRing) || empty($updatedData)){
            $shapedata = array();
        }else{
            $shapedata = $this->_readConnection->fetchAll($shapeQuery);
        }

        $optionValue = array();
        foreach ($attOption as $value) {
            $optionValue[$value->getId()]['code'] = $value->getValue();
            $optionValue[$value->getId()]['image'] = Mage::helper('wizard')->getAttImage(array('code'=>'STONE_SHAPE','type'=>'OTHER'),$value->getValue());
        }
        $allcount= 0;
        foreach ($shapedata as $value) {
            $allcount += floor($value['COUNT(stone_shape)']/2);
            $shapeimage = Mage::helper('wizard')->getAttImage(array('code'=>'STONE_SHAPE','type'=>'OTHER'),$value['stone_shape']);
$productShapes .= '<li class="item">
                            <div class="image">'.$shapeimage.'</div>
                            <div class="price-box">
                                <span>'.Mage::helper('core')->currency($value['min_price'], true, false).' - '.Mage::helper('core')->currency($value['max_price'], true, false).'</span>
                            </div>
                            <div class="availability">('.floor($value['COUNT(stone_shape)']/2).' diamonds available)</div>
                        </li>';
        }
        Mage::getSingleton('core/session')->setSideStoneList(serialize($updatedData));
        $jsonArr = $this->getSideStoneByPage($pageOffset,$categoryId);
        $newJsonArr['product'] = (!empty($jsonArr))?$jsonArr:'';
        $newJsonArr['option'] = $productShapes;
        $newJsonArr['count'] = $allcount;
        return json_encode($newJsonArr);
    }


    public function setSidestone($values){
        $b = serialize($values);
        Mage::getSingleton('core/session')->setSidestone($b);
    }

    public function getProduct($values)
    {
        $ringId = $values['variantId'];
        $ringCollection = array();
        if($ringId > 0){
            $ringCollection = $this->_readConnection->fetchCol("select DISTINCT(variant_refsmryid) from wizardrelation where item_id=(select item_id from wizardmaster where pid=".$ringId.") and type='material' and special_character='C'");

        }
        $category = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('is_active', 1)->addAttributeToFilter('name', 'Diamond');
        $categoryId =  $category->getFirstItem()->getId();

        if($values['page'] == 1){
            $pageOffset = 0;
        }else{
            $pageOffset = ($values['page']*$this->_pageLimit)-$this->_pageLimit;
        }

        $newJsonArr = array();
        if($values['page'] > 1){
            $jsonArr = $this->getDiamondByPage($pageOffset,$categoryId);
            $newJsonArr['product'] = $jsonArr;
            return json_encode($newJsonArr);
        }
        if($values['productId'] > 0 && $ringId > 0){
            $diaSku = Mage::helper('wizard')->getSkufromId($values['productId']);
            $setStyle = $this->_readConnection->fetchRow("select metal_color,karat,item_id from wizardmaster where pid=".$ringId);

            $currentStyle = $this->_readConnection->fetchCol("select pid from wizardrelation where variant_refsmryid='".$diaSku[0]."' ");
            $newStyle = $this->_readConnection->fetchCol("select pid from wizardmaster where metal_color='".$setStyle['metal_color']."' and  karat ='".$setStyle['karat']."' and pid IN (".implode(',', $currentStyle).") AND item_id=".$setStyle['item_id']." AND IF(  `center_diamond` =1, special_character LIKE  '%C%', 1 ) AND IF(  `matchpair` =1, special_character LIKE  '%M%', 1 ) AND LOWER(product_type) = '".$this->_productType['ring']."' AND construction = 'Create your own'");
            $updatedRingId = $ringId;
            if(!in_array($ringId, $newStyle) && !empty($newStyle)){
                $updatedRingId = $newStyle[0];
                $ringSltd = Mage::getSingleton('core/session')->getRingSelected();
                $ringSltd = unserialize($ringSltd);
                $ringSltd['productId'] = $updatedRingId;
                $ringData =  Mage::getSingleton('core/session')->getRingData();
                $ringData[$updatedRingId] =  $ringData[$ringId];
                Mage::getSingleton('core/session')->setRingData($ringData);
                Mage::getSingleton('core/session')->setRingSelected(serialize($ringSltd));
            }
        }
        if($values['productId'] > 0 && $values['promise'] > 0){
            $promiseSet = Mage::getSingleton('core/session')->getPromiseRing();
            $promiseSet['did'] = $values['productId'];
            Mage::getSingleton('core/session')->setPromiseRing($promiseSet);
            Mage::getSingleton('core/session')->setSelectedValue(serialize($values));
        }else{
            Mage::getSingleton('core/session')->setSelectedValue(serialize($values));
        }

        $jsonArr = array();
        $newJsonArr = array();


        $query = 'SELECT * FROM wizardmaster where ';
        $where = array('LOWER(product_type) = "'.$this->_productType['diamond'].'"');
        $where[] = 'group_code = ""';
        foreach ($values['value'] as $key=>$value) {
            $key = strtolower($key);
            $where []= $key.' IN ("'.implode('","',$value).'")';
        }
        $where []= 'weight >= '.$values['caratArr'][0].' and weight <= '.$values['caratArr'][1];
        $where []= 'depth_per >= '.$values['deptharr'][0].' and depth_per <= '.$values['deptharr'][1];
        $where []= 'table_per >= '.$values['tablearr'][0].' and table_per <= '.$values['tablearr'][1];
        if($ringId > 0){
            if(!empty($ringCollection)){
                $where []= 'sku IN ("'.implode('","',$ringCollection).'")';
            }else{
                $where []= 'sku IN ("remove_all")';
            }
        }
        $caratImages = Mage::helper('wizard')->getcaratDiamond($values['caratArr'][0],$values['caratArr'][1]);
        if($values['editid']){
            $existDiamond = Mage::helper('wizard')->existDiamonds(true);
        }else{
            $existDiamond = Mage::helper('wizard')->existDiamonds(false);
        }

        if(!empty($existDiamond)){
            $where []= 'pid NOT IN ('.implode(",",$existDiamond).')';
        }
        $whereQry = implode(' AND ', $where);

        $alldata = $this->_readConnection->fetchALL($query.$whereQry.' order by
field( `stone_shape`, "ROUND", "PRINCESS", "CUSHION","OVAL","EMERALD"),weight ASC');

        $shapeQuery = 'SELECT stone_shape,COUNT(stone_shape),MIN(price) as min_price,MAX(price) as max_price  FROM wizardmaster where '.$whereQry.'  group by stone_shape order by
field( `stone_shape`, "ROUND", "PRINCESS", "CUSHION","OVAL","EMERALD"),weight ASC';

        $shapedata = $this->_readConnection->fetchAll($shapeQuery);
        foreach ($shapedata as $value) {
            $shapeimage = Mage::helper('wizard')->getAttImage(array('code'=>'STONE_SHAPE','type'=>'OTHER'),$value['stone_shape']);
$productShapes .= '<li class="item">
                            <div class="image">'.$shapeimage.'</div>
                            <div class="price-box">
                                <span>'.Mage::helper('core')->currency($value['min_price'], true, false).' - '.Mage::helper('core')->currency($value['max_price'], true, false).'</span>
                            </div>
                            <div class="availability">('.$value['COUNT(stone_shape)'].' diamonds available)</div>
                        </li>';
        }
        Mage::getSingleton('core/session')->setDiamondList(serialize($alldata));
        $jsonArr = $this->getDiamondByPage($pageOffset,$categoryId);
        $newJsonArr['product'] = (!empty($jsonArr))?$jsonArr:'';
        $newJsonArr['option'] = $productShapes;
        $newJsonArr['count'] = count($alldata);
        $newJsonArr['caratimg'] = $caratImages;
        return json_encode($newJsonArr);
    }


    public function getRingByPage($pageOffset,$categoryId,$shapeSelected,$variantId){
        $compareCollection = Mage::helper('catalog/product_compare')->getItemCollection();
        $compareIds = array();
        foreach ($compareCollection as $value) {
            $compareIds[] = $value->getId();
        }


        $variantList =  Mage::getSingleton('core/session')->getVarianList();
        $variantList = unserialize($variantList);
//added-to-compare
        $html = array();
        if($variantList != ''){
            $variantList = $variantList.' order by price LIMIT '.$pageOffset.','.$this->_pageLimit;
            $html = $this->_readConnection->fetchALL($variantList);
            foreach ($html as $key=>$value) {

                $price = Mage::helper('core')->currency($value['price'], true, false);
                $image = Mage::Helper('wizard')->getResizeImage($value['image'],310,310);
                $html[$key]['sortprice'] = $value['price'];
                $html[$key]['image'] = $image;
                $html[$key]['price'] = $price;
                $html[$key]['comparecls'] = (in_array($value['pid'], $compareIds))?'added-to-compare':'';
            }
        }
        return $html;
    }


    public function sortBy($field, &$array, $direction = 'asc')
    {
        usort($array, create_function('$a, $b', '
            $a = $a["' . $field . '"];
            $b = $b["' . $field . '"];

            if ($a == $b) return 0;

            $direction = strtolower(trim($direction));

            return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
        '));

        return true;
    }

    public function getDiamondByPage($pageOffset,$categoryId){
        $diamondList =  Mage::getSingleton('core/session')->getDiamondList();
        $diamondList = unserialize($diamondList);
        $output = array_slice($diamondList, $pageOffset, $this->_pageLimit);
        $mediapath = Mage::getBaseUrl("media");
        foreach ($output as $product) {
            $price = number_format($product['price'],2);
            //$image = Mage::app()->getLayout()->createBlock('core/template')->setData('product', $product)->setTemplate('wizard/diamond.phtml')->toHtml();
            $pid = $product["pid"];
            $image = '<span class="zoom-icon">
            <a data-fancybox data-type="ajax" data-src="'.Mage::getBaseURL().'popuphtml/popup.php?pid='.$pid.'" href="javascript:;">
                    <img src="'.$mediapath.'wizard/zoom-icon.png" alt="'.$product["pid"].'" class="mCS_img_loaded">
                </a>
            </span>
            <input type="hidden" class="productid" value="'.$product["pid"].'">';
            $shape = $product['stone_shape'];
            $catat = $product['weight'];
            $color = $product['stone_color'];
            $clarity = $product['stone_quality'];
            $cut = $product['stone_cut'];
            $compare = '<a href="javascript:void(0);" class="compare-icon"></a>';
            $jsonArr[] = array($image,$shape,$catat,$color,$clarity,$cut,$price,$compare);
        }
        return $jsonArr;
    }


    public function getSideStoneByPage($pageOffset,$categoryId){
        $diamondList =  Mage::getSingleton('core/session')->getSideStoneList();
        $diamondList = unserialize($diamondList);
        $output = array_slice($diamondList, $pageOffset, $this->_pageLimit);
        $mediapath = Mage::getBaseUrl("media");

        $output = array_slice($diamondList, $pageOffset, $this->_pageLimit);

        $jsonArr = array();
        foreach ($output as $item) {
            $product1 = $item[0];
            $product2 = $item[1];
            $price1 = ($product1['special_price'] > 0 && $product1['special_price'] < $product1['price'])?$product1['special_price']:$product1['price'];
            $price2 = ($product2['special_price'] > 0 && $product2['special_price'] < $product2['price'])?$product2['special_price']:$product2['price'];
            $price = $price1+$price2;


            $color1 = $product1['stone_color'];
            $color2 = $product2['stone_color'];

            $clarity1 = $product1['stone_quality'];
            $clarity2 = $product2['stone_quality'];

            $multiArr = array($product1,$product2);
            $image = '<span class="zoom-icon">
            <a data-fancybox data-type="ajax" data-src="'.Mage::getBaseURL().'popuphtml/popup.php?pid='.$product1['pid'].'" href="javascript:;">
                    <img src="'.$mediapath.'wizard/zoom-icon.png" alt="'.$product1["pid"].'" class="mCS_img_loaded">
                </a>
            </span>
            <input type="hidden" class="productid1" value="'.$product1["pid"].'">
            <input type="hidden" class="productid2" value="'.$product2["pid"].'">';
            $shape1 = $product1['stone_shape'];
            $shape2 = $product2['stone_shape'];
            $catat1 = $product1['weight'];
            $catat2 = $product2['weight'];

            $cut1 = $product1['stone_cut'];
            $cut2 = $product2['stone_cut'];
            $compare = '<a href="javascript:void(0);" class="compare-icon"></a>';
            $jsonArr[] = array($image,$shape1.'<br>'.$shape2,$catat1.'<br>'.$catat2,$color1.'<br>'.$color2,$clarity1.'<br/>'.$clarity2,$cut1.'<br>'.$cut2,$price,$compare);
        }
        return $jsonArr;
    }

    public function setRingData($params){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $metalData = $params['id'];
        $metalData = explode('_', $metalData);
        $metalColor = $metalData[1];
        $karat = $metalData[2];

        $product = Mage::getModel('catalog/product')->load($params['pid']);
        $rowIdentity = $product->getRowIdentity();
        $relationRing = $readConnection->fetchCol('select DISTINCT variant_refsmryid from wizardrelation where pid = '.$params['pid'].' and type = "metal"');
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToSelect('karat')->addAttributeToSelect('item_id')->addAttributeToSelect('metal_color')->addFieldToFilter('row_identity',$rowIdentity)->addFieldToFilter('sku', array('in' => $relationRing))->addFieldToFilter('metal_color',$metalColor)->addFieldToFilter('karat',$karat);
        $newId = $collection->getFirstItem()->getId();
        $ringSelected =  Mage::getSingleton('core/session')->getRingSelected();
        $ringSelected = unserialize($ringSelected);
        $ringSelected['productId'] = $newId;
        $ringSelected = serialize($ringSelected);
        Mage::getSingleton('core/session')->setRingSelected($ringSelected);
    }


    public function setRingbyDiamondShape($params){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $id = explode('_', $params['id']);

        $diamondList = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('stone_shape',$id[0]);
        $variantSku = $diamondList->getColumnValues('sku');
        if(!empty($variantSku)){
            $ringList = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('item_id', $id[1])->addAttributeToFilter('smry_item_type', $this->_styleListId);
            $variantRing = $ringList->getColumnValues('entity_id');
            if(!empty($variantRing)){
                $variantSku = '"'.implode('","', $variantSku).'"';
                $shapeRing = $readConnection->fetchCol("select DISTINCT pid from wizardrelation where variant_refsmryid IN (".$variantSku.") and pid IN (".implode(',', $variantRing).")");
                if(!empty($shapeRing)){
                    $ringSelected =  Mage::getSingleton('core/session')->getRingSelected();
                    $ringSelected = unserialize($ringSelected);
                    $ringSelected['productId'] = $shapeRing[0];
                    $ringSelected = serialize($ringSelected);
                    Mage::getSingleton('core/session')->setRingSelected($ringSelected);
                }
            }
        }
    }
}
