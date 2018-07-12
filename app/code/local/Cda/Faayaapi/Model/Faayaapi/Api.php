<?php
class Cda_Faayaapi_Model_Faayaapi_Api extends Mage_Api_Model_Resource_Abstract
{
    protected $_text = array("TRANS_ID","VIDEO","VIDEO_HTML","CERTIFICATE_NO","DIA_PRICE_PER_CTS","TRANS_ITEM_ID","ITEM_ID","ITEM_NAME","VARIANT_ID","PIECES","PARENT_CHILD_MAPPING","VARIANT_NAME","OLD_VARIANT_NAME","QUALITY","FINISH","COMMENTS","CLASP","MAXIMUM_HEIGHT","MAXIMUM_WIDTH","CONSTRUCTION","BACK_TYPE","BANGLE","CLOSURE","CHAIN_INCLUDED","BALE_TYPE","STONE_RANGE","MEASUREMENT","C_HEIGHT","PAVILION_DEPTH","POLISH","SYMMETRY","CULET","FLUORESCENCE","WIDTH","LENGTH","STOCK_CATEGORY","SHADE_REFERENCE","GRIDLE_PER","TABLE_PER","TOTAL_DEPTH","C_ANGLE","PAVILION_ANGLE","HEIGHT_MM","PAVILION_HEIGHT","RATIO_L_W","POST_SHADE","ROUGH_SHAPE","ROUGH_PURITY","ROUGH_SC_SHADE","S_SHADE","S_PURITY","EDUCATION_INFORMATION","LENGTH_X_WIDTH","MAKE","BASE_VARIANT_ID","STOCK_CODE","SR_NO","ROW_STATUS","SMRY_ID","STYLE","STYLE_ID","ENGAGEMENT","PRODUCING_METHOD","ASSEMBLY","GRIDLE","SERIES","SUB_SIZE","SHADE","FAMILY_COLOR","HEART_AND_ARROW","NATIVE_PURITY","CERTIFICATE","STYLE_SUFFIX","CARAT","DESIGNER","ROW_IDENTITY","CERTIFICATE_PDF","GROUP_CODE","VARIANT_REMARK","TOTAL_DIA_WT","DEPTH_PER","MEASUREMENTS","LW_RATIO");

    protected $_dropdown = array("SMRY_ITEM_TYPE","BRAND","SHAPE","METAL_COLOR","METAL_TYPE","GENDER","PRODUCT_SIZE","CERT_COLOR","SUB_CATEGORY","BAND_WIDTH","STONE_SHAPE","STONE_QUALITY","STONE_CUT","STONE_COLOR","CERTIFIED_COLOR","KARAT","CHAIN_LENGTH","CHAIN_TYPE","COLLECTION","JEWELRY");
    protected $_multiselect = array();
    protected $_dropdownAttributeOptionData = array();
    protected $_systemattribute = array("WEIGHT","PRICE","SPECIAL_PRICE","PRODUCT_TYPE","DISCRIPTION","IMAGES","AVAILABLE_SIZES","LD_MATERIAL","METAL_OPTION","WEDDING_BAND","CHAIN_OPTION");
    /*protected $_childMapping = array("AVAILABLE_SIZES"=>"size","LD_MATERIAL"=>"material","METAL_OPTION"=>"metal","WEDDING_BAND"=>"wedding","CHAIN_OPTION"=>"chain","METAL_INFO"=>"metalinfo","DIAMOND_INFO"=>"diamondinfo");*/
    protected $_childMapping = array("AVAILABLE_SIZES"=>"size","METAL_OPTION"=>"metal","WEDDING_BAND"=>"wedding","CHAIN_OPTION"=>"chain","METAL_INFO"=>"metalinfo","DIAMOND_INFO"=>"diamondinfo");
    protected $_noAttribute = array("IMAGES","AVAILABLE_SIZES","LD_MATERIAL","METAL_OPTION","WEDDING_BAND","CHAIN_OPTION");
    protected $_finishType = array();
    protected $_formatData = array();
    protected $_ldData = array();
    protected $_categoryIds = array();
    protected $_dbCategories = array();
    protected $_webCategories = array();
    protected $_widzetAttributeValue = array();
    protected $_widzetAttributeList = array();
    protected $_entityTypeId = 4;
    protected $_arrayKey = array();
    protected $_allcsvsku;
    protected $_tradeId;
    protected $_valentineId;
    protected $_allAttribute;
    protected $_connectionRead;
    protected $_connectionWrite;
    public $rootCategoryPath = '1/2';
    protected $_response = array();
    protected $_insertArray = array();
    protected $_updateArray = array();
    protected $_deleteArray = array();

    public function _construct(){
        $this->_connectionRead = $this->_getConnection('core_read');
        $this->_connectionWrite = $this->_getConnection('core_write');
    }
    protected function _getConnection($type = 'core_read'){
        return Mage::getSingleton('core/resource')->getConnection($type);
    }
    protected function _getTableName($tableName){
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }
    protected function _getDbSkus($entity_type_code = 'catalog_product'){
        $sql        = "SELECT sku FROM " . $this->_getTableName('catalog_product_entity');
          return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchCol($sql);
    }
    // Read Json Data
    public function import($string,$ldString) {
        try {
            if($string != ""){
                $isJsonString = $this->json_validator($string);
                if($isJsonString){
                    $this->getAllDropdownValueArray();
                    $data = json_decode($string,true);
                    $attributeKeyList = $this->createArrayData($data);

                    /* Diamond data */
                    $isLdJsonString = $this->json_validator($ldString);
                    if($isLdJsonString){
                        $ldArray = json_decode($ldString,true);
                        $this->_ldData = $this->createLdData($ldArray);
                    }

                    array_push($attributeKeyList,"CARAT");
                    array_push($attributeKeyList,"STYLE_ID");
                    array_push($attributeKeyList,"JEWELRY");
                    $this->getAllAttributeGroup($this->_entityTypeId,"Custom Attribute");
                    $this->getAllAttribute();
                    $this->createAttributeData($attributeKeyList);
                    $this->getWidzetAttributeValue();
                    // Create Category
                    $this->collectCetegories();
                    $this->importCategories();
                    $dbSkus =  $this->_getDbSkus();
                    $jsonSkus = $this->_allcsvsku;
                    $updateSkus = array_unique(array_intersect($jsonSkus,$dbSkus));
                    $createSkus = array_unique(array_diff($jsonSkus,$dbSkus));
                    $deleteSku = array_unique(array_diff($dbSkus,$jsonSkus));

                    if(count($createSkus)>0){
                        $this->_createProduct = $this->createnewproduct($createSkus,$this->_formatData);
                    }
                    if(count($updateSkus)>0){
                        $this->_updateproduct = $this->createUpdateSimpleProduct($updateSkus,$this->_formatData);
                    }
                    $this->_response = array_merge($this->_insertArray,$this->_updateArray,$this->_deleteArray);
                    return json_encode($this->_response);
                }
                else{
                    return "Invalide Format String of JSON";
                }
            }else{
                return "JSON is Empty";
            }
        }catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
    }
    public function createLdData($ldArray){
        $ldArr = array();
        foreach($ldArray as $ld){
            //print_r($ld);
            $ldArr[$ld['SMRY_ID']][] = $ld;
        }
        return ($ldArr);
    }
    public function createArrayData($data){
        foreach($data as $key=>$firstData){
            if($firstData['SMRY_ITEM_TYPE']=='DIAMOND'){
                $this->_formatData[$firstData["STOCK_CODE"]] = $firstData;
            }else{
                $this->_formatData[$firstData['VARIANT_ID']] = $firstData;
            }
            if($firstData["PRODUCT_TYPE"] != "" && $firstData["PRODUCT_TYPE"] !="NA"){
                if($firstData["STOCK_CODE"] != "" && $firstData["STOCK_CODE"] != "NA"){
                    $this->_webCategories[$firstData["SMRY_ID"]][]  = array('name'=>strtolower($firstData["PRODUCT_TYPE"]),'path'=>trim(strtolower($firstData["PRODUCT_TYPE"])));
                }else{
                    $this->_webCategories[$firstData["SMRY_ID"]][]  = array('name'=>strtolower("jewelry"),'path'=> "jewelry"."/". trim(strtolower($firstData["PRODUCT_TYPE"])));
                }
            }else{
                 if($firstData["STOCK_CODE"] != "" && $firstData["STOCK_CODE"] != "NA"){
                    $this->_webCategories[$firstData["SMRY_ID"]][]  = array('name'=>strtolower("Diamond"),'path'=>trim(strtolower("Diamond")));
                }else{
                    $this->_webCategories[$firstData["SMRY_ID"]][]  = array('name'=>strtolower("jewelry"),'path'=>trim(strtolower("Other")));
                }
            }
            /*if($firstData["COLLECTION"] != "" && $firstData["COLLECTION"] !="NA"){
                $this->_webCategories[$firstData["SMRY_ID"]][]  = array('name'=>strtolower("Collection"),'path'=> "Collection"."/". trim(strtolower($firstData["COLLECTION"])));
            }*/
            /*if($firstData['SMRY_ITEM_TYPE']=='DIAMOND'){*/
            if($firstData['SMRY_ITEM_TYPE']=='DIAMOND'){
                $this->_allcsvsku[] = $firstData["STOCK_CODE"];
            }else{
                $this->_allcsvsku[] = $firstData["VARIANT_ID"];
            }
            /*}*/
            foreach(array_keys($firstData) as $key){
                $this->_arrayKey[] = $key;
            }


        }
        //print_R($this->_webCategories);exit;
        return array_unique($this->_arrayKey);
    }
    public function createAttributeGroup($groupName){
        $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $installer->startSetup();
        $entityTypeId = $installer->getEntityTypeId('catalog_product');
        $attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
        $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 2);
        $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        // Add existing attribute to group
        $attributeId = $installer->getAttributeId($entityTypeId, ATTRIBUTE_CODE);
        $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
        $installer->endSetup();
    }
    public function getAllAttributeGroup($setId,$groupName){
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->addFieldToFilter('attribute_set_id',$setId)
            ->addFieldToFilter('attribute_group_name',$groupName);
        if(count($groups) <= 0){
            $this->createAttributeGroup($groupName);
        }
    }
    public function getAllAttribute(){
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        foreach ($attributes as $attribute){
            $this->_allAttribute[] = $attribute->getAttributecode();
        }
    }
    public function createAttributeData($attributeKeyList){
        foreach($attributeKeyList as $attribute){
            if(in_array($attribute,$this->_text)){
                // Create Text Attribute
                $code = strtolower($attribute);
                if(!in_array($code,$this->_allAttribute)) {
                    $this->createNewAttribute(strtolower($attribute),"text",strtolower($attribute));
                    $this->_allAttribute[] = $code;
                }
            }

            else if(in_array($attribute,$this->_dropdown)){
                // Create Drop Down Attribute
                $code = strtolower($attribute);
                if(!in_array($code,$this->_allAttribute)) {
                    $this->createNewAttribute(strtolower($attribute),"select",strtolower($attribute));
                    $this->_allAttribute[] = $code;
                }
            }
            else if(in_array($attribute,$this->_multiselect)){
                // Create Multi Select Attribute
                $code = strtolower($attribute);
                if(!in_array($code,$this->_allAttribute)) {
                    $this->createMultiselectAttribute(strtolower($attribute) , strtolower($attribute), $this->_finishType);
                    $this->_allAttribute[] = $code;
                }
            }
            else {
                if (!in_array($attribute, $this->_systemattribute)) {
                    $code = strtolower($attribute);
                    if(!in_array($code,$this->_allAttribute)){
                        $this->createNewAttribute(strtolower($attribute), "text", strtolower($attribute));
                        $this->_allAttribute[] = $code;
                    }
                }
            }
        }
    }
    public function removeAttributeData($attributeKeyList){
        foreach($attributeKeyList as $attribute){
            if(!in_array($attribute,$this->_systemattribute)) {
                $attributeCode = strtolower($attribute);
                $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
                $installer->startSetup();
                $installer->removeAttribute('catalog_product', $attributeCode);
                $installer->endSetup();
                echo $attributeCode . " has been removed" . "<br/>";
            }
        }
        return true;
    }
    public function createMultiselectAttribute($value , $code , $optionVal){
        $option = array_map("unserialize", array_unique(array_map("serialize", $optionVal)));
        $_attribute_data = array(
            'is_global'                     => '0',
            'frontend_input'                => 'multiselect',
            'default_value_text'            => '',
            'default_value_yesno'           => '0',
            'default_value_date'            => '',
            'default_value_textarea'        => '',
            'is_unique'                     => '0',
            'is_required'                   => '0',
            'frontend_class'                => '',
            'is_searchable'                 => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable'                 => '1',
            'is_used_for_promo_rules'       => '0',
            'is_html_allowed_on_front'      => '1',
            'is_visible_on_front'           => '0',
            'used_in_product_listing'       => '0',
            'used_for_sort_by'              => '0',
            'is_configurable'               => '0',
            'is_filterable'                 => '1',
            'is_filterable_in_search'       => '1',
            'backend_type'                  => 'varchar',
            'default_value'                 => '',
            'is_user_defined'               => '1',
            'is_visible'                    => '1',
            'is_used_for_price_rules'       => '0',
            'position'                      => '0',
            'is_wysiwyg_enabled'            => '0',
            'backend_model'                 => '',
            'attribute_model'               => '',
            'backend_table'                 => '',
            'frontend_model'                => '',
            'source_model'                  => '',
            'note'                          => '',
            'frontend_input_renderer'       => '',
            'position_in_key_features'      => '',
            'use_in_key_features'           => '0',
            'use_in_key_features'           => '0',
            'is_used_for_customer_segment'  => '0',
            'is_used_for_target_rules'      => '0',
            'attribute_code'                => $code,
            'frontend_label'                => ucfirst(str_replace("_"," ",$value)),
            'option'                        => array ('value' =>  $option),
        );

        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->addData($_attribute_data);
        $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
        $model->setIsUserDefined(1);
        try {
            $model->save();
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

            $attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code','catalog_product')->getFirstItem();
            $attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection();
            // this is the attribute sets associated with this entity
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setCodeFilter($code)->getFirstItem();

            $attCode = $attributeInfo->getAttributeCode();
            $attId = $attributeInfo->getId();
            foreach ($attSetCollection as $a)
            {
                $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
                $setId = $set->getId();
                $group = Mage::getModel('eav/entity_attribute_group')->getCollection()->addFieldToFilter('attribute_set_id',$setId)->addFieldToFilter('attribute_group_name',"Custom Attribute")->getFirstItem();
                  $groupId = $group->getId();
                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$code);

                $attributeModel->setEntityTypeId($attSet->getId())
                    ->setAttributeSetId($setId)
                    ->setAttributeGroupId($groupId)
                    ->setAttributeId($attId)
                    ->setSortOrder(10)
                    ->save();
            }
        } catch (Exception $e) { echo '<p>Sorry, error occured while trying to save the attribute.  Error: '.$e->getMessage(). '  '.$code . '</p>';
        }
    }
    protected function generateRandomString(){
        $length = 15;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function createNewAttribute($attributeCode,$type,$lable){
        $attributeName  = ucfirst(str_replace("_"," ",$lable)); // Name of the attribute
        $attributeCode  = $attributeCode; // Code of the attribute
        $attributeGroup = 'Custom Attribute';          // Group to add the attribute to
        $attributeSetIds = array(4);
        $data = array(
            'type' => 'varchar', // Attribute type
            'backend'           => '',
            'frontend'          => '',
            'input' => $type, // Input type
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL, // Attribute scope
            'required' => false, // Is this attribute required?
            'user_defined' => true,
            'searchable' => false,
            'default'    => '',
            'is_wysiwyg_enabled' => '0',
            'is_used_for_price_rules' => '0',
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'unique' => false,
            'used_in_product_listing' => true,
            'label' => $attributeName
        );
        $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $installer->startSetup();
        $installer->addAttribute('catalog_product', $attributeCode, $data);
        $entity = Mage_Catalog_Model_Product::ENTITY;
        $attributeSetIds = $installer->getAllAttributeSetIds($entity);
        foreach($attributeSetIds as $attributeSetId)
        {
            $installer->addAttributeToGroup('catalog_product', $attributeSetId, $attributeGroup, $attributeCode );
        }
        $installer->endSetup();
        return;
    }
    public function createnewproduct($createSkus,$data){
        $createnewproduct = '';
        $n=0;
        foreach($createSkus as $sku){
            $value = $data[$sku];
            $transId = ($value["TRANS_ID"] !="NA" && $value["TRANS_ID"]) ? $value["TRANS_ID"] : '';
            $transItemId = ($value["TRANS_ITEM_ID"] !="NA" && $value["TRANS_ITEM_ID"]) ? $value["TRANS_ITEM_ID"] : '';
            $itemId = ($value["ITEM_ID"] !="NA" && $value["ITEM_ID"]) ? $value["ITEM_ID"] : '';
            $itemName = ($value["ITEM_NAME"] !="NA" && $value["ITEM_NAME"]) ? $value["ITEM_NAME"] : '';
            $smryItemType = ($value["SMRY_ITEM_TYPE"] !="NA" && $value["SMRY_ITEM_TYPE"]) ? strtolower($value["SMRY_ITEM_TYPE"]) : '';
            $variantId = ($value["VARIANT_ID"] !="NA" && $value["VARIANT_ID"])  ? $value["VARIANT_ID"] : '';
            $pieces = ($value["PIECES"] !="NA" && $value["PIECES"])  ? $value["PIECES"] : '';
            $carat = ($value["WEIGHT"] !="NA" && $value["WEIGHT"]) ? $value["WEIGHT"] : '';
            $weight = ($value["WEIGHT"] !="NA" && $value["WEIGHT"])  ? $value["WEIGHT"] : '';
            $parentChildMapping = ($value["PARENT_CHILD_MAPPING"] !="NA" && $value["PARENT_CHILD_MAPPING"]) ? $value["PARENT_CHILD_MAPPING"] : '';
            $varientName = ($value["VARIANT_NAME"] !="NA" && $value["VARIANT_NAME"]) ? $value["VARIANT_NAME"] : '';
            $rowIdentity = ($value["ROW_IDENTITY"] !="NA" && $value["ROW_IDENTITY"]) ? $value["ROW_IDENTITY"] : '';
            $oldVarientName = ($value["OLD_VARIANT_NAME"] !="NA" && $value["OLD_VARIANT_NAME"]) ? $value["OLD_VARIANT_NAME"] : '';
            $designer = ($value["DESIGNER"] !="NA" && $value["DESIGNER"])  ? $value["DESIGNER"] : '';
            $defaultInd = ($value["DEFAULT_IND"] !="NA" && $value["DEFAULT_IND"])  ? $value["DEFAULT_IND"] : '';
            $brand = ($value["BRAND"] !="NA" && $value["BRAND"]) ? $value["BRAND"] : '';
            $shape = ($value["SHAPE"] !="NA" && $value["SHAPE"]) ? $value["SHAPE"] : '';
            $metalColor = ($value["METAL_COLOR"] !="NA" && $value["METAL_COLOR"]) ? $value["METAL_COLOR"] : '';
            $karat = ($value["KARAT"] !="NA" && $value["KARAT"]) ? $value["KARAT"] : '';
            $quality = ($value["QUALITY"] !="NA" && $value["QUALITY"]) ? $value["QUALITY"] : '';
            $metalType = ($value["METAL_TYPE"] !="NA" && $value["METAL_TYPE"]) ? $value["METAL_TYPE"] : '';
            $finish = ($value["FINISH"] !="NA" && $value["FINISH"]) ? $value["FINISH"] : '';
            $subCategory = ($value["SUB_CATEGORY"] !="NA" && $value["SUB_CATEGORY"]) ? $value["SUB_CATEGORY"] : '';
            $totalDiaWt = ($value["TOTAL_DIA_WT"] !="NA" && $value["TOTAL_DIA_WT"]) ? $value["TOTAL_DIA_WT"] : '';
            $gender = ($value["GENDER"] !="NA" && $value["GENDER"]) ? $value["GENDER"] : '';
            $productType = ($value["PRODUCT_TYPE"] !="NA" && $value["PRODUCT_TYPE"]) ? $value["PRODUCT_TYPE"] : '';
            $productSize = ($value["PRODUCT_SIZE"] !="NA" && $value["PRODUCT_SIZE"]) ? $value["PRODUCT_SIZE"] : '';
            $finishType = ($value["FINISH_TYPE"] !="NA" && $value["FINISH_TYPE"]) ? $value["FINISH_TYPE"] : '';
            $chainLength = ($value["CHAIN_LENGTH"] !="NA" && $value["CHAIN_LENGTH"]) ? $value["CHAIN_LENGTH"] : '';
            $style = ($value["STYLE"] !="NA" && $value["STYLE"]) ? $value["STYLE"] : '';
            $styleId = ($value["STYLE"] !="NA" && $value["STYLE"]) ? $value["STYLE"] : '';
            $engagement = ($value["ENGAGEMENT"] !="NA" && $value["ENGAGEMENT"]) ? $value["ENGAGEMENT"] : '';
            $chainType = ($value["CHAIN_TYPE"] !="NA" && $value["CHAIN_TYPE"]) ? $value["CHAIN_TYPE"] : '';
            $certColor = ($value["CERT_COLOR"] !="NA" && $value["CERT_COLOR"]) ? $value["CERT_COLOR"] : '';
            $productMethod = ($value["PRODUCING_METHOD"] !="NA" && $value["PRODUCING_METHOD"]) ? $value["PRODUCING_METHOD"] : '';
            $assembly= ($value["ASSEMBLY"] !="NA" && $value["ASSEMBLY"]) ? $value["ASSEMBLY"] : '';
            $description = ($value["DESCRIPTION"] !="NA" && $value["DESCRIPTION"]) ? $value["DESCRIPTION"] : '';
            $comments = ($value["COMMENTS"] !="NA" && $value["COMMENTS"]) ? $value["COMMENTS"] : '';
            $styleSuffix = ($value["STYLE_SUFFIX"] !="NA" && $value["STYLE_SUFFIX"]) ? $value["STYLE_SUFFIX"] : '';
            $bandWidth = ($value["BAND_WIDTH"] !="NA" && $value["BAND_WIDTH"]) ? $value["BAND_WIDTH"] : '';
            $clasp = ($value["CLASP"] !="NA" && $value["CLASP"]) ? $value["CLASP"] : '';
            $maximumHeight = ($value["MAXIMUM_HEIGHT"] !="NA" && $value["MAXIMUM_HEIGHT"]) ? $value["MAXIMUM_HEIGHT"] : '';
            $maximumWidth = ($value["MAXIMUM_WIDTH"] !="NA" && $value["MAXIMUM_WIDTH"]) ? $value["MAXIMUM_WIDTH"] : '';
            $construction = ($value["CONSTRUCTION"] !="NA" && $value["CONSTRUCTION"]) ? $value["CONSTRUCTION"] : '';
            $backType = ($value["BACK_TYPE"] !="NA" && $value["BACK_TYPE"]) ? $value["BACK_TYPE"] : '';
            $bangle = ($value["BANGLE"] !="NA" && $value["BANGLE"]) ? $value["BANGLE"] : '';
            $closure = ($value["CLOSURE"] !="NA" && $value["CLOSURE"]) ? $value["CLOSURE"] : '';
            $chainIncluded = ($value["CHAIN_INCLUDED"] !="NA" && $value["CHAIN_INCLUDED"]) ? $value["CHAIN_INCLUDED"] : '';
            $baleType = ($value["BALE_TYPE"] !="NA" && $value["BALE_TYPE"]) ? $value["BALE_TYPE"] : '';
            $stoneShape = ($value["STONE_SHAPE"] !="NA" && $value["STONE_SHAPE"]) ? $value["STONE_SHAPE"] : '';
            $stoneQuality = ($value["STONE_QUALITY"] !="NA" && $value["STONE_QUALITY"]) ? $value["STONE_QUALITY"] : '';
            $stoneRange = ($value["STONE_RANGE"] !="NA" && $value["STONE_RANGE"]) ? $value["STONE_RANGE"] : '';
            $stoneCut = ($value["STONE_CUT"] !="NA" && $value["STONE_CUT"]) ? $value["STONE_CUT"] : '';
            $stoneColor = ($value["STONE_COLOR"] !="NA" && $value["STONE_COLOR"]) ? $value["STONE_COLOR"] : '';
            $measurement = ($value["MEASUREMENT"] !="NA" && $value["MEASUREMENT"]) ? $value["MEASUREMENT"] : '';
            $cHeight = ($value["C_HEIGHT"] !="NA" && $value["C_HEIGHT"]) ? $value["C_HEIGHT"] : '';
            $pavilionDepth = ($value["PAVILION_DEPTH"] !="NA" && $value["PAVILION_DEPTH"]) ? $value["PAVILION_DEPTH"] : '';
            $gridle = ($value["GRIDLE"] !="NA" && $value["GRIDLE"]) ? $value["GRIDLE"] : '';
            $polish = ($value["POLISH"] !="NA" && $value["POLISH"]) ? $value["POLISH"] : '';
            $symmetry = ($value["SYMMETRY"] !="NA" && $value["SYMMETRY"]) ? $value["SYMMETRY"] : '';
            $culet = ($value["CULET"] !="NA" && $value["CULET"]) ? $value["CULET"] : '';
            $fluorescence = ($value["FLUORESCENCE"] !="NA" && $value["FLUORESCENCE"]) ? $value["FLUORESCENCE"] : '';
            $width = ($value["WIDTH"] !="NA" && $value["WIDTH"]) ? $value["WIDTH"] : '';
            $length = ($value["LENGTH"] !="NA" && $value["LENGTH"]) ? $value["LENGTH"] : '';
            $series = ($value["SERIES"] !="NA" && $value["SERIES"]) ? $value["SERIES"] : '';
            $stockCategory = ($value["STOCK_CATEGORY"] !="NA" && $value["STOCK_CATEGORY"]) ? $value["STOCK_CATEGORY"] : '';
            $shade = ($value["SHADE"] !="NA" && $value["SHADE"]) ? $value["SHADE"] : '';
            $shadeReference = ($value["SHADE_REFERENCE"] !="NA" && $value["SHADE_REFERENCE"]) ? $value["SHADE_REFERENCE"] : '';
            $gridlePer = ($value["GRIDLE_PER"] !="NA" && $value["GRIDLE_PER"]) ? $value["GRIDLE_PER"] : '';
            $tablePer = ($value["TABLE_PER"] !="NA" && $value["TABLE_PER"]) ? $value["TABLE_PER"] : '';
            $totalDepth = ($value["TOTAL_DEPTH"] !="NA" && $value["TOTAL_DEPTH"]) ? $value["TOTAL_DEPTH"] : '';
            $cAngle = ($value["C_ANGLE"] !="NA" && $value["C_ANGLE"]) ? $value["C_ANGLE"] : '';
            $pavilionAngle = ($value["PAVILION_ANGLE"] !="NA" && $value["PAVILION_ANGLE"]) ? $value["PAVILION_ANGLE"] : '';
            $subSize = ($value["SUB_SIZE"] !="NA" && $value["SUB_SIZE"]) ? $value["SUB_SIZE"] : '';
            $heightMm = ($value["HEIGHT_MM"] !="NA" && $value["HEIGHT_MM"]) ? $value["HEIGHT_MM"] : '';
            $pavilionHeight = ($value["PAVILION_HEIGHT"] !="NA" && $value["PAVILION_HEIGHT"]) ? $value["PAVILION_HEIGHT"] : '';
            $familyColor = ($value["FAMILY_COLOR"] !="NA" && $value["FAMILY_COLOR"]) ? $value["FAMILY_COLOR"] : '';
            $ratioLW = ($value["RATIO_L_W"] !="NA" && $value["RATIO_L_W"]) ? $value["RATIO_L_W"] : '';
            $postShade = ($value["POST_SHADE"] !="NA" && $value["POST_SHADE"]) ? $value["POST_SHADE"] : '';
            $certifiedColor = ($value["CERTIFIED_COLOR"] !="NA" && $value["CERTIFIED_COLOR"]) ? $value["CERTIFIED_COLOR"] : '';
            $roughShape = ($value["ROUGH_SHAPE"] !="NA" && $value["ROUGH_SHAPE"]) ? $value["ROUGH_SHAPE"] : '';
            $roughPurity = ($value["ROUGH_PURITY"] !="NA" && $value["ROUGH_PURITY"]) ? $value["ROUGH_PURITY"] : '';
            $roughScShade = ($value["ROUGH_SC_SHADE"] !="NA" && $value["ROUGH_SC_SHADE"]) ? $value["ROUGH_SC_SHADE"] : '';
            $certificate = ($value["CERTIFICATE"] !="NA" && $value["CERTIFICATE"]) ? $value["CERTIFICATE"] : '';
            $heartAndArrow = ($value["HEART_AND_ARROW"] !="NA" && $value["HEART_AND_ARROW"]) ? $value["HEART_AND_ARROW"] : '';
            $nativePurity = ($value["NATIVE_PURITY"] !="NA" && $value["NATIVE_PURITY"]) ? $value["NATIVE_PURITY"] : '';
            $sShade = ($value["S_SHADE"] !="NA" && $value["S_SHADE"]) ? $value["S_SHADE"] : '';
            $sPurity = ($value["S_PURITY"] !="NA" && $value["S_PURITY"]) ? $value["S_PURITY"] : '';
            $educationInformation = ($value["EDUCATION_INFORMATION"] !="NA" && $value["EDUCATION_INFORMATION"]) ? $value["EDUCATION_INFORMATION"] : '';
            $lengthXWidth = ($value["LENGTH_X_WIDTH"] !="NA" && $value["LENGTH_X_WIDTH"]) ? $value["LENGTH_X_WIDTH"] : '';
            $make = ($value["MAKE"] !="NA" && $value["MAKE"]) ? $value["MAKE"] : '';
            $baseVariantId = ($value["BASE_VARIANT_ID"] !="NA" && $value["BASE_VARIANT_ID"]) ? $value["BASE_VARIANT_ID"] : '';
            $stockCode = ($value["STOCK_CODE"] !="NA" && $value["STOCK_CODE"]) ? $value["STOCK_CODE"] : '';
            $srNo = ($value["SR_NO"] !="NA" && $value["SR_NO"]) ? $value["SR_NO"] : '';
            $price = ($value["PRICE"] !="NA" && $value["PRICE"]) ? $value["PRICE"] : '0.00';
            $specialPrice = ($value["SPECIAL_PRICE"] !="NA" && $value["SPECIAL_PRICE"]) ? $value["SPECIAL_PRICE"] : '';
            $specialPriceFromDate = ($value["SPECIAL_PRICE_FROM_DATE"] !="NA" && $value["SPECIAL_PRICE_FROM_DATE"]) ? $value["SPECIAL_PRICE_FROM_DATE"] : '';
            $specialPriceToDate = ($value["SPECIAL_PRICE_TO_DATE"] !="NA" && $value["SPECIAL_PRICE_TO_DATE"]) ? $value["SPECIAL_PRICE_TO_DATE"] : '';
            $rowStatus = ($value["ROW_STATUS"] !="NA" && $value["ROW_STATUS"]) ? $value["ROW_STATUS"] : '';
            $smryId = ($value["SMRY_ID"] !="NA" && $value["SMRY_ID"]) ? $value["SMRY_ID"] : '';
            $creationTime = ($value["CREATION_TIME"] !="NA" && $value["CREATION_TIME"]) ? $value["CREATION_TIME"] : '';
            $certificatePdf = ($value["CERTIFICATE_PDF"] !="NA" && $value["CERTIFICATE_PDF"]) ? $value["CERTIFICATE_PDF"] : '';
            $variantRemark = ($value["VARIANT_REMARK"] !="NA" && $value["VARIANT_REMARK"]) ? $value["VARIANT_REMARK"] : '';
            $groupCode = ($value["GROUP_CODE"] !="NA" && $value["GROUP_CODE"]) ? $value["GROUP_CODE"] : '';
            $video = ($value["VIDEO_FILE_NAME"] !="NA" && $value["VIDEO_FILE_NAME"]) ? $value["VIDEO_FILE_NAME"] : '';
            $videohtml = ($value["VIDEO_HTML"] !="NA" && $value["VIDEO_HTML"]) ? $value["VIDEO_HTML"] : '';
            $certificateno = ($value["CERTIFICATE_NO"] !="NA" && $value["CERTIFICATE_NO"]) ? $value["CERTIFICATE_NO"] : '';
            $diapricepercts = ($value["DIA_PRICE_PER_CTS"] !="NA" && $value["DIA_PRICE_PER_CTS"]) ? $value["DIA_PRICE_PER_CTS"] : '';
            $depthPer = ($value["DEPTH_PER"] !="NA" && $value["DEPTH_PER"]) ? $value["DEPTH_PER"] : '';
            $measurements = ($value["MEASUREMENTS"] !="NA" && $value["MEASUREMENTS"]) ? $value["MEASUREMENTS"] : '';
            $lwRatio = ($value["LW_RATIO"] !="NA" && $value["LW_RATIO"]) ? $value["LW_RATIO"] : '';

            if($value['SMRY_ITEM_TYPE']=='DIAMOND'){
                $productSku = $value["STOCK_CODE"];
            }else{
                $productSku = $value["VARIANT_ID"];
            }

            $simProduct = Mage::getModel("catalog/product");
            $simProduct->setName($itemName);
            $simProduct->setSku($productSku);
            $simProduct->setUrlKey(str_replace(" ", "_",strtolower($itemName)).'-'.$productSku);
            $simProduct->setTypeId('simple');
            $simProduct->setAttributeSetId(4);     // Default
            $simProduct->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
            if($description){
                $simProduct->setDescription($description);
                $simProduct->setShortDescription($description);
            }else{
                $simProduct->setDescription($varientName);
                $simProduct->setShortDescription($varientName);
            }
            $simProduct->setStatus(1);     // Enabled
            $simProduct->setVisibility(4);

            $simProduct->setPrice($price);
            if($price > $specialPrice){
                $simProduct->setSpecialPrice($specialPrice);
            }
            // Assign Category
            $simProduct->setCategoryIds($this->_categoryIds[$smryId]);
            $simProduct->setTaxClassId(2);
            $simProduct->setCarat($carat);
            $simProduct->setWeight($weight);
            $simProduct->setTransId($transId);
            $simProduct->setTransItemId($transItemId);
            $simProduct->setItemId($itemId);
            $simProduct->setItemName($itemName);
            $simProduct->setVariantId($variantId);
            $simProduct->setVariantName($varientName);
            $simProduct->setRowIdentity($rowIdentity);
            $simProduct->setPieces($pieces);
            $simProduct->setParentChildMapping($parentChildMapping);
            $simProduct->setOldVarientName($oldVarientName);
            $simProduct->setDefaultInd($defaultInd);
            $simProduct->setQuality($quality);
            $simProduct->setDesigner($designer);
            $simProduct->setFinish($finish);
            $simProduct->setSubCategory($subCategory);
            $simProduct->setTotalDiaWt($totalDiaWt);
            $simProduct->setStyle($engagement);
            $simProduct->setStyleId($styleId);
            $simProduct->setEngagement($engagement);
            $simProduct->setCertColor($certColor);
            $simProduct->setProducingMethod($productMethod);
            $simProduct->setAssembly($assembly);
            $simProduct->setComments($comments);
            $simProduct->setStyleSuffix($styleSuffix);
            $simProduct->setClasp($clasp);
            $simProduct->setMaximumHeight($maximumHeight);
            $simProduct->setMaximumWidth($maximumWidth);
            $simProduct->setConstruction($construction);
            $simProduct->setBackType($backType);
            $simProduct->setBangle($bangle);
            $simProduct->setClosure($closure);
            $simProduct->setChainIncluded($chainIncluded);
            $simProduct->setBaleType($baleType);
            $simProduct->setStoneShape($stoneShape);
            $simProduct->setStoneQuality($stoneQuality);
            $simProduct->setStoneRange($stoneRange);
            $simProduct->setStoneCut($stoneCut);
            $simProduct->setStoneColor($stoneColor);
            $simProduct->setMeasurement($measurement);
            $simProduct->setCHeight($cHeight);
            $simProduct->setPavilionDepth($pavilionDepth);
            $simProduct->setGridle($gridle);
            $simProduct->setPolish($polish);
            $simProduct->setSymmetry($symmetry);
            $simProduct->setCulet($culet);
            $simProduct->setFluorescence($fluorescence);
            $simProduct->setWidth($width);
            $simProduct->setLength($length);
            $simProduct->setSeries($series);
            $simProduct->setStockCategory($stockCategory);
            $simProduct->setStockCategory($stockCategory);
            $simProduct->setShade($shade);
            $simProduct->setShadeReference($shadeReference);
            $simProduct->setGridlePer($gridlePer);
            $simProduct->setTablePer($tablePer);
            $simProduct->setTotalDepth($totalDepth);
            $simProduct->setCAngle($cAngle);
            $simProduct->setPavilionAngle($pavilionAngle);
            $simProduct->setSubSize($subSize);
            $simProduct->setHeightMm($heightMm);
            $simProduct->setPavilionHeight($pavilionHeight);
            $simProduct->setFamilyColor($familyColor);
            $simProduct->setRatioLW($ratioLW);
            $simProduct->setPostShade($postShade);
            $simProduct->setRoughShape($roughShape);
            $simProduct->setRoughPurity($roughPurity);
            $simProduct->setRoughScShade($roughScShade);
            $simProduct->setCertificate($certificate);
            $simProduct->setHeartAndArrow($heartAndArrow);
            $simProduct->setNativePurity($nativePurity);
            $simProduct->setSShade($sShade);
            $simProduct->setSPurity($sPurity);
            $simProduct->setEducationInformation($educationInformation);
            $simProduct->setLengthXWidth($lengthXWidth);
            $simProduct->setMake($make);
            $simProduct->setBaseVariantId($baseVariantId);
            $simProduct->setStockCode($stockCode);
            $simProduct->setSrNo($srNo);
            $simProduct->setSpecialPriceFromDate($specialPriceFromDate);
            $simProduct->setSpecialPriceToDate($specialPriceToDate);
            $simProduct->setRowStatus($rowStatus);
            $simProduct->setSmryId($smryId);
            $simProduct->setCreationTime($creationTime);
            $simProduct->setCertificatePdf($certificatePdf);
            $simProduct->setVariantRemark($variantRemark);
            $simProduct->setGroupCode($groupCode);
            $simProduct->setVideo($video);
            $simProduct->setVideoHtml($videohtml);
            $simProduct->setCertificateNo($certificateno);
            $simProduct->setDiaPricePerCts($diapricepercts);
            $simProduct->setDepthPer($depthPer);
            $simProduct->setMeasurements($measurements);
            $simProduct->setLwRatio($lwRatio);


            // Set all Dropdiwn here
            foreach($this->_dropdown as $attr){
                $code = strtolower($attr);
                $values = $value[$attr];
                if($values != "" && $values !="NA"){
                    $foundData = $this->arraysearchRecursive($values,$this->_dropdownAttributeOptionData[$code]);
                    $optionId = $this->_dropdownAttributeOptionData[$code][$foundData[0]]['value'];
                    $this->setCustomDropdownProductValue($simProduct,$code,$values,$optionId);
                }
            }
            // Set Product Jewelry Attribute
                if($productType != "" && $productType !="NA"){
                    if($productJewelry = $this->_getSelectAttribute('jewelry',$productType)){
                        $simProduct->setJewelry($productJewelry);
                    }
                    else{
                        if($this->_addSelectAttribute('jewelry',$productType)){
                            if($productJewelry = $this->_getSelectAttribute('jewelry',$productType)){
                                $simProduct->setJewelry($productJewelry);
                            }
                        }
                    }
                }

            /* Set Inventory*/
            if($value['SMRY_ITEM_TYPE'] == 'DIAMOND'){
                $simProduct->setStockData(array(
                    'use_config_manage_stock' => 1, //'Use config settings' checkbox
                    'manage_stock' => 1, //manage stock
                    'is_in_stock' => 1, //Stock Availability
                    'qty' => 1 //qty
                    )
                );
            }else{
                $simProduct->setStockData(array(
                    'use_config_manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => 0,
                    'manage_stock' => 0,
                ));
            }


           $mediaAttribute = array ('image','small_image','thumbnail');
                $imageArray = array_unique($value["IMAGES"], SORT_REGULAR);
                if(is_array($imageArray)){
                    foreach($imageArray as $image){
                        if($image['IMAGE_FILE_NAME'] != ""){
                            $name = $path = $file = "";
                            $name = basename(trim($image['IMAGE_FILE_NAME']));
                            $name = preg_replace('/\s+/', '', $name);
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                             if($ext !="mp4" && $ext !="tif"){
                                if($value['SMRY_ITEM_TYPE']=='DIAMOND'){
                                        $isFileExist = Mage::getBaseDir('media').DS.'diamondimages'.DS . $name;
                                    }else{
                                        $isFileExist = Mage::getBaseDir('media').DS.'styleimages'.DS . $name;
                                    }

                               if(file_exists($isFileExist)) {
                                    $filepath = Mage::getBaseDir('media') . DS . "styleimages" . DS . $name;
                                    if ($image['IMAGE_TYPE'] == "CATALOGUE") {
                                        $simProduct->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
                                    } else {
                                        $simProduct->addImageToMediaGallery($filepath, null, false, false);
                                    }
                                }

                            }elseif ($ext =="mp4"){
                                if($value['SMRY_ITEM_TYPE']=='STYLE'){
                                    $simProduct->setVideo($image['IMAGE_FILE_NAME']);
                                }
                            }
                        }
                    }
                }else{
                    if(!empty($value["DIA_IMAGE"])){
                            $name = $path = $file = "";
                            $name = basename(trim($value["DIA_IMAGE"]));
                            $name = preg_replace('/\s+/', '', $name);
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                             if($ext !="mp4" && $ext !="tif"){
                                $isFileExist = Mage::getBaseDir('media').DS.'diamondimages'.DS . $name;
                               if(file_exists($isFileExist)) {
                                    $filepath = Mage::getBaseDir('media') . DS . "diamondimages" . DS . $name;
                                    $simProduct->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
                                }
                            }
                    }
                }
            try {
                $simProduct->save();
                echo $productSku.' Created successfully'."\n";
                $productId = $simProduct->getId();
                $this->_insertArray['A'][$productSku] = $varientName;
                $this->inserWidzetRelation($simProduct,$value);
                $this->inserWidzetMaster($simProduct,$value);
            }
            catch (Mage_Core_Exception $e) {
                Mage::log("Error occured while create or updating the product(".$simProduct->getSku()."). Error: ", null, 'cron-import.log');
                Mage::log($e->getMessage(), null, 'cron-import.log');
            }
            //$n++;
        }
        return $this->_insertArray;
    }
    protected function setCustomDropdownProductValue($simProduct,$code,$value,$optionId){
        if($optionId){
            $simProduct->setData($code,$optionId);
             return;
        }
        $attributeId = "$".$code;
        if($attributeId = $this->_getSelectAttribute($code,$value)){
                $simProduct->setData($code,$attributeId);
        }
        else{
            if($this->_addSelectAttribute($code,$value)){
                if($attributeId = $this->_getSelectAttribute($code,$value)){
                    $simProduct->setData($code,$attributeId);
                }
            }
        }
    }


    protected function deleteWidzetRelation($sku){
         $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        // Delete from wizardrelation
        try {
            $sql = 'DELETE FROM wizardrelation WHERE variant_id='.$sku;
            $conn->query($sql);
        } catch (Exception $e){
            echo $e->getMessage();
        }

         // Delete from wizardmaster
        /*try {
            $sql = 'DELETE FROM wizardmaster WHERE pid='.$productId;
            $conn->query($sql);
        } catch (Exception $e){
            echo $e->getMessage();
        }*/

    }
    //protected function inserWidzetRelation($simProduct,$childAttribute,$sku){
    protected function inserWidzetRelation($simProduct,$childAttribute){
        $productId = $simProduct->getId();
        /*$realationModel= Mage::getModel('wizard/wizardrelation')->getCollection()->addFieldToFilter('pid', $productId);
        foreach ($realationModel as $item) {
            $item->delete();
        }*/
        $smryId =  $simProduct->getSmryId();
        $wigzetRelation = Mage::getModel("wizard/wizardrelation");
        $data = array();
        foreach($this->_childMapping as $attr => $value){
            if(array_key_exists($attr,$childAttribute)){
                foreach($childAttribute[$attr] as $mapping){
                    $specialCharacter = ($mapping["SPECIAL_CHARACTER"] && $mapping["SPECIAL_CHARACTER"]!="") ? $mapping["SPECIAL_CHARACTER"]: "";
                    try{
                        $data["pid"] = $productId;
                        $data["variant_id"] = $childAttribute["VARIANT_ID"];
                        $data["base_variant_id"] = $childAttribute["BASE_VARIANT_ID"];
                        $data["type"] = $value;
                        $data["variant_refsmryid"] = '';

                        if(isset($mapping["VARIANT_ID"])){
                            $data["variant_refsmryid"] = $mapping["VARIANT_ID"];
                        }

                        if($attr == "LD_MATERIAL"){
                            $data["variant_refsmryid"] = $mapping["STOCK_CODE"];
                        }

                        //$data["variant_refsmryid"] = $simProduct->getSku();

                        $data["special_character"] = $specialCharacter;
                        $data["group_code"] = $mapping["GROUP_CODE"];
                        $data["item_id"] = $childAttribute["ITEM_ID"];
                        $wigzetRelation->setData($data)->save();
                    }catch (Exception $e){
                        echo $e->getMessage();
                    }
                }
            }
        }
        if(isset($this->_ldData[$smryId])){
            foreach($this->_ldData[$smryId] as $mapping){
                try{
                    $data["pid"] = $productId;
                    $data["variant_id"] = $childAttribute["VARIANT_ID"];
                    $data["base_variant_id"] = $childAttribute["BASE_VARIANT_ID"];
                    $data["type"] = 'material';
                    $data["variant_refsmryid"] = $mapping["STOCK_CODE"];
                    $data["special_character"] = $mapping["SPECIAL_CHARACTER"];
                    $data["group_code"] = $mapping["GROUP_CODE"];
                    $data["item_id"] = $childAttribute["ITEM_ID"];
                    $wigzetRelation->setData($data)->save();
                }catch (Exception $e){
                    echo $e->getMessage();
                }
            }
        }

    }
    protected function updateWidzetMaster($simProduct,$childAttribute){

        $isBaseVariant = $centerDiamond = $matchpair = $cflag = $mflag = 0;
        $productType = $availableSize = $pinfo = $specialCharacter = '';
        $productId = $simProduct->getId();
        $smryId =  $simProduct->getSmryId();
        /*update stock item*/
        /*$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simProduct);
        $stockItem->setQty(1);
        $stockItem->setData('use_config_manage_stock',0);
        $stockItem->setData('manage_stock',1);
        $stockItem->setData('is_in_stock',1);
        $stockItem->save();*/

        $sku = $simProduct->getSku();
        $itemId = $simProduct->getItemId();
        $smryId = $simProduct->getSmryId();
        $variantId = $simProduct->getVariantId();
        $baseVariantId = $simProduct->getBaseVariantId();
        $defaultInd = $simProduct->getDefaultInd();
        $construction = $simProduct->getConstruction();
        $categoryIds= $simProduct->getCategoryIds();
        $categoryId = $categoryIds[0];
        if($baseVariantId == $variantId){
            $isBaseVariant = 1;
        }
        $productType = $childAttribute['PRODUCT_TYPE'];
        if(!$productType){
            //$productType = $childAttribute['SMRY_ITEM_TYPE'];
            if($childAttribute['SMRY_ITEM_TYPE'] != "DIAMOND"){
                $productType = "chain";
            }else{
                $productType = $childAttribute['SMRY_ITEM_TYPE'];
            }
        }
        $productMediaConfig = Mage::getModel('catalog/product_media_config');
        $baseImageUrl = $simProduct->getImage()?$simProduct->getImage():'';

        $variantName = $childAttribute['VARIANT_NAME'];
        $variantRemark = $childAttribute['VARIANT_REMARK'];
        //$price =  $childAttribute['PRICE'];
        $price =  $simProduct->getPrice();
        //$specialPrice =  $childAttribute['SPECIAL_PRICE'];
        $specialPrice =  $simProduct->getSpecialPrice();
        $totalDiaWt =  $childAttribute['TOTAL_DIA_WT'];
        $metalColor =  $childAttribute['METAL_COLOR'];
        $karat =  $childAttribute['KARAT'];
        $weight =  $childAttribute['WEIGHT'];
        $stoneShape =  $childAttribute['STONE_SHAPE'];
        $subCategory =  $childAttribute['SUB_CATEGORY'];
        $rowIdentity =  $childAttribute['ROW_IDENTITY'];
        $productSize =  $childAttribute['PRODUCT_SIZE'];
        $stoneQuality =  $childAttribute['STONE_QUALITY'];
        $groupCode =  $childAttribute['GROUP_CODE'];
        $stoneCut =  $childAttribute['STONE_CUT'];
        $stoneColor =  $childAttribute['STONE_COLOR'];
        $url =  $simProduct->getUrlKey();
        $sizes = $childAttribute['AVAILABLE_SIZES'];
        $ldMaterial = array();
        if(isset($this->_ldData[$smryId])){
            $ldMaterial = $this->_ldData[$smryId];
        }

        $diamondInfo = $childAttribute['DIAMOND_INFO'];
        $chainType = $childAttribute['FINDING_TYPE'];
        $chainLength = $childAttribute['FINDING_SIZE'];
        $backType = $childAttribute['BACK_TYPE'];
        $depthPer = $childAttribute['DEPTH_PER'];
        $proCollection = $childAttribute['COLLECTION'];


        if(count($sizes) > 0){
            foreach($sizes as $size){
               $availableSize .=  $size['PRODUCT_SIZE_ID'].":".$size['PRODUCT_SIZE'].',';
            }
        }
        if(count($diamondInfo) > 0){
            $pinfoArray = array();
            foreach($diamondInfo as $diamond){
                if($diamond['SPECIAL_CHARACTER'] == "P"){
                    //$pinfo .= serialize($diamond);
                    $pinfoArray[]= $diamond;
                }
                if($diamond['SPECIAL_CHARACTER'] == "C" && $centerDiamond == 0){
                    $centerDiamond = 1;
                }
                if($diamond['SPECIAL_CHARACTER'] == "M" && $matchpair == 0){
                    $matchpair = 1;
                }
            }

            if(count($pinfoArray) > 0){
                $pinfo = serialize($pinfoArray);
                //$pinfo = str_replace('"', "'", $pinfo);
            }
        }
        if(count($ldMaterial) > 0){
            foreach($ldMaterial as $material){
                if($material['SPECIAL_CHARACTER'] == "C" && $cflag ==0){
                    $specialCharacter .= $material['SPECIAL_CHARACTER'].",";
                    $cflag = 1;
                }
                if($material['SPECIAL_CHARACTER'] == "M" && $mflag ==0){
                    $specialCharacter .= $material['SPECIAL_CHARACTER'].",";
                    $mflag = 1;
                }
            }
        }

       $data = array();
        try{
            $data["pid"] = $productId;
            $data["item_id"] = $itemId;
            $data["variant_id"] = $variantId;
            $data["smry_id"] = $smryId;
            $data["base_variant_id"] = $baseVariantId;
            $data["category_id"] = $categoryId;
            $data["construction"] = $construction;
            $data["product_type"] = $productType;
            $data["is_default"] = $defaultInd;
            $data["is_basevariant"] = $isBaseVariant;
            $data["image"] = $baseImageUrl;

            $data["variant_name"] = $variantName;
            $data["variant_remark"] = $variantRemark;
            $data["price"] = $price;
            $data["special_price"] = $specialPrice;
            $data["total_dia_wt"] = $totalDiaWt;
            $data["metal_color"] = $metalColor;
            $data["karat"] = $karat;
            $data["weight"] = $weight;
            $data["stone_shape"] = $stoneShape;
            $data["sub_category"] = $subCategory;
            $data["sku"] = $sku;

            $data["row_identity"] = $rowIdentity;
            $data["product_size"] = $productSize;
            $data["stone_quality"] = $stoneQuality;
            $data["group_code"] = $groupCode;
            $data["stone_cut"] = $stoneCut;
            $data["stone_color"] = $stoneColor;
            $data["url"] = $url;
            $data["available_size"] = $availableSize;
            $data["pinfo"] = $pinfo;
            $data['special_character'] = $specialCharacter;
            $data["center_diamond"] = $centerDiamond;
            $data["matchpair"] = $matchpair;
            $data['polish'] = $childAttribute['POLISH'];
            $data['symmetry'] = $childAttribute['SYMMETRY'];
            $data['fluorescence'] = $childAttribute['FLUORESCENCE'];
            $data['depth_mm'] = $childAttribute['DEPTH_MM'];
            $data['table_per'] = $childAttribute['TABLE_PER'];
            $data['band_width'] = $childAttribute['BAND_WIDTH'];
            $data['metal_type'] = $childAttribute['METAL_TYPE'];
            $data['is_ldmaterial'] = isset($ldMaterial)&& !empty($ldMaterial)?1:0;
            $data['chain_type'] = $chainType;
            $data['chain_length'] = $chainLength;
            $data['back_type'] = $backType;
            $data['depth_per'] = $depthPer;
            $data['status'] = 1;
            $data['collection'] = $proCollection;

            $dbConnection = $this->_getConnection('core_write');
            $setString = array();
            foreach ($data as $key=>$value) {
                //$setString[] = $key.'="'.$value.'"';
                $setString[] = $key."='".$value."'";
            }

            if(!empty($setString)){
                $setString = implode(",", $setString);
                $sqlquery = "UPDATE {$this->_getTableName('wizardmaster')} set ".$setString." WHERE sku={$sku}";
                $dbConnection->query($sqlquery);
            }

            if(isset($sizes)){
            $sizeData = array();
            $k = 0;
                foreach ($sizes as $key => $sizeArray) {
                    $sizeData[$k]['variant_id'] = $variantId;
                    $sizeData[$k]['variant_size_id'] = $sizeArray['PRODUCT_SIZE_ID'];
                    $sizeData[$k]['product_size'] = $sizeArray['PRODUCT_SIZE'];
                    $k++;
                }
                $dbConnection = $this->_getConnection('core_write');
                $sqlquery = "DELETE FROM {$this->_getTableName('wizardsize')} WHERE variant_id={$variantId};";
                $dbConnection->query($sqlquery);
                $dbConnection->insertMultiple($this->_getTableName('wizardsize'), $sizeData);
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }



    protected function inserWidzetMaster($simProduct,$childAttribute){
        $isBaseVariant = $centerDiamond = $matchpair = $cflag = $mflag = 0;
        $productType = $availableSize = $pinfo = $specialCharacter = '';
        $productId = $simProduct->getId();
        $smryId =  $simProduct->getSmryId();
        $sku = $simProduct->getSku();
        $itemId = $simProduct->getItemId();
        $smryId = $simProduct->getSmryId();
        $variantId = $simProduct->getVariantId();
        $baseVariantId = $simProduct->getBaseVariantId();
        $defaultInd = $simProduct->getDefaultInd();
        $construction = $simProduct->getConstruction();
        $categoryIds= $simProduct->getCategoryIds();
        $categoryId = $categoryIds[0];
        if($baseVariantId == $variantId){
            $isBaseVariant = 1;
        }
        $productType = $childAttribute['PRODUCT_TYPE'];
        if(!$productType){
            //$productType = $childAttribute['SMRY_ITEM_TYPE'];
            if($childAttribute['SMRY_ITEM_TYPE'] != "DIAMOND"){
                $productType = "chain";
            }else{
                $productType = $childAttribute['SMRY_ITEM_TYPE'];
            }
        }
        $productMediaConfig = Mage::getModel('catalog/product_media_config');
        $baseImageUrl = $simProduct->getImage()?$simProduct->getImage():'';

        $variantName = $childAttribute['VARIANT_NAME'];
        $variantRemark = $childAttribute['VARIANT_REMARK'];
        //$price =  $childAttribute['PRICE'];
        $price =  $simProduct->getPrice();
        //$specialPrice =  $childAttribute['SPECIAL_PRICE'];
        $specialPrice =  $simProduct->getSpecialPrice();
        $totalDiaWt =  $childAttribute['TOTAL_DIA_WT'];
        $metalColor =  $childAttribute['METAL_COLOR'];
        $karat =  $childAttribute['KARAT'];
        $weight =  $childAttribute['WEIGHT'];
        $stoneShape =  $childAttribute['STONE_SHAPE'];
        $subCategory =  $childAttribute['SUB_CATEGORY'];
        $rowIdentity =  $childAttribute['ROW_IDENTITY'];
        $productSize =  $childAttribute['PRODUCT_SIZE'];
        $stoneQuality =  $childAttribute['STONE_QUALITY'];
        $groupCode =  $childAttribute['GROUP_CODE'];
        $stoneCut =  $childAttribute['STONE_CUT'];
        $stoneColor =  $childAttribute['STONE_COLOR'];
        $url =  $simProduct->getUrlKey();
        $sizes = $childAttribute['AVAILABLE_SIZES'];
        $ldMaterial = array();
        if(isset($this->_ldData[$smryId])){
            $ldMaterial = $this->_ldData[$smryId];
        }        $diamondInfo = $childAttribute['DIAMOND_INFO'];
        $chainType = $childAttribute['FINDING_TYPE'];
        $chainLength = $childAttribute['FINDING_SIZE'];
        $backType = $childAttribute['BACK_TYPE'];
        $depthPer = $childAttribute['DEPTH_PER'];
        $proCollection = $childAttribute['COLLECTION'];


        if(count($sizes) > 0){
            foreach($sizes as $size){
               $availableSize .=  $size['PRODUCT_SIZE_ID'].":".$size['PRODUCT_SIZE'].',';
            }
        }
        if(count($diamondInfo) > 0){
            $pinfoArray = array();
            foreach($diamondInfo as $diamond){
                if($diamond['SPECIAL_CHARACTER'] == "P"){
                    //$pinfo .= serialize($diamond);
                    $pinfoArray[]= $diamond;
                }
                if($diamond['SPECIAL_CHARACTER'] == "C" && $centerDiamond == 0){
                    $centerDiamond = 1;
                }
                if($diamond['SPECIAL_CHARACTER'] == "M" && $matchpair == 0){
                    $matchpair = 1;
                }
            }
            if(count($pinfoArray) > 0){
                $pinfo = serialize($pinfoArray);
            }
        }
        if(count($ldMaterial) > 0){
            foreach($ldMaterial as $material){
                if($material['SPECIAL_CHARACTER'] == "C" && $cflag ==0){
                    $specialCharacter .= $material['SPECIAL_CHARACTER'].",";
                    $cflag = 1;
                }
                if($material['SPECIAL_CHARACTER'] == "M" && $mflag ==0){
                    $specialCharacter .= $material['SPECIAL_CHARACTER'].",";
                    $mflag = 1;
                }
            }
        }
        Mage::getModel('wizard/wizardmaster')->load($productId,'pid')->delete();
       $wigzetMaster = Mage::getModel("wizard/wizardmaster");
       $data = array();
        try{
            $data["pid"] = $productId;
            $data["item_id"] = $itemId;
            $data["variant_id"] = $variantId;
            $data["smry_id"] = $smryId;
            $data["base_variant_id"] = $baseVariantId;
            $data["category_id"] = $categoryId;
            $data["construction"] = $construction;
            $data["product_type"] = $productType;
            $data["is_default"] = $defaultInd;
            $data["is_basevariant"] = $isBaseVariant;
            $data["image"] = $baseImageUrl;

            $data["variant_name"] = $variantName;
            $data["variant_remark"] = $variantRemark;
            $data["price"] = $price;
            $data["special_price"] = $specialPrice;
            $data["total_dia_wt"] = $totalDiaWt;
            $data["metal_color"] = $metalColor;
            $data["karat"] = $karat;
            $data["weight"] = $weight;
            $data["stone_shape"] = $stoneShape;
            $data["sub_category"] = $subCategory;
            $data["sku"] = $sku;

            $data["row_identity"] = $rowIdentity;
            $data["product_size"] = $productSize;
            $data["stone_quality"] = $stoneQuality;
            $data["group_code"] = $groupCode;
            $data["stone_cut"] = $stoneCut;
            $data["stone_color"] = $stoneColor;
            $data["url"] = $url;
            $data["available_size"] = $availableSize;
            $data["pinfo"] = $pinfo;
            $data['special_character'] = $specialCharacter;
            $data["center_diamond"] = $centerDiamond;
            $data["matchpair"] = $matchpair;
            $data['polish'] = $childAttribute['POLISH'];
            $data['symmetry'] = $childAttribute['SYMMETRY'];
            $data['fluorescence'] = $childAttribute['FLUORESCENCE'];
            $data['depth_mm'] = $childAttribute['DEPTH_MM'];
            $data['table_per'] = $childAttribute['TABLE_PER'];
            $data['band_width'] = $childAttribute['BAND_WIDTH'];
            $data['metal_type'] = $childAttribute['METAL_TYPE'];
            $data['is_ldmaterial'] = isset($ldMaterial)&& !empty($ldMaterial)?1:0;
            $data['chain_type'] = $chainType;
            $data['chain_length'] = $chainLength;
            $data['back_type'] = $backType;
            $data['depth_per'] = $depthPer;
            $data['status'] = 1;
            $data['collection'] = $proCollection;

            $wigzetMaster->setData($data)->save();
            if(isset($sizes)){
            $sizeData = array();
            $k = 0;
                foreach ($sizes as $key => $sizeArray) {
                    $sizeData[$k]['variant_id'] = $variantId;
                    $sizeData[$k]['variant_size_id'] = $sizeArray['PRODUCT_SIZE_ID'];
                    $sizeData[$k]['product_size'] = $sizeArray['PRODUCT_SIZE'];
                    $k++;
                }
                $dbConnection = $this->_getConnection('core_write');
                $sqlquery = "DELETE FROM {$this->_getTableName('wizardsize')} WHERE variant_id={$variantId};";
                $dbConnection->query($sqlquery);
                $dbConnection->insertMultiple($this->_getTableName('wizardsize'), $sizeData);
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }


    protected function getWidzetAttributeValue(){
        $wigzetAttribute = Mage::getModel("wizard/wizardattribute")->getCollection();
        if($wigzetAttribute){
            foreach($wigzetAttribute as $attr){
                $this->_widzetAttributeList[$attr["id"]] = $attr["code"]."-".$attr["type"];
                $wigzetAttributeOption = Mage::getModel("wizard/wizardoptions")->getCollection()->addFieldToFilter('attr_id',$attr["id"]);
                foreach($wigzetAttributeOption as $option){
                    $this->_widzetAttributeValue[$attr["code"]."-".$attr["type"]][$attr["id"]][$option["id"]] = $option["value"];
                }
            }
        }
       }
    protected function _addSelectAttribute($code,$optionLb){
        $attrModel = Mage::getModel('catalog/resource_eav_attribute');
        $attr = $attrModel->loadByCode('catalog_product', $code);
        if((count($attr) > 0) && $optionLb !=""){
            $attrId = $attr->getAttributeId();
            $option['attribute_id'] = $attrId;
            $option['value']['option'][0] = $optionLb;
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $setup->addAttributeOption($option);
            return $this->_getSelectAttribute($code,$optionLb);
        }
        return false;
    }
    protected function _getSelectAttribute($code,$optionLb){
        $sql = "SELECT EAO.option_id
                FROM " . $this->_getTableName('eav_attribute') . " AS EA
                LEFT JOIN ".$this->_getTableName('eav_attribute_option')." EAO ON EAO.attribute_id = EA.attribute_id
                LEFT JOIN ".$this->_getTableName('eav_attribute_option_value')." EAOV ON EAOV.option_id = EAO.option_id
                WHERE EA.attribute_code = ?
                AND EAOV.value = ?
                AND EAOV.store_id = 0";
        return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($sql, array($code,$optionLb));
    }
    protected  function _getAttributeIdAndType($attributeCode){
        $sql = "SELECT attribute_id,backend_type
                FROM " . $this->_getTableName('eav_attribute') . "
                WHERE
                    entity_type_id = ?
                    AND attribute_code = ?";
        return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($sql, array($this->_entityTypeId, $attributeCode));
    }
    protected function _updateAttribute($productId,$attributeCode,$attributeValue){
        $attributeIdAndType = $this->_getAttributeIdAndType($attributeCode);
        $attributeId = $attributeIdAndType['attribute_id'];
        $attributeType = $attributeIdAndType['backend_type'];

        $sql        = "SELECT * FROM ".$this->_getTableName('eav_attribute')." WHERE attribute_code LIKE ?";
         $res = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($sql, array($attributeCode));
        if($res){
            $sql        = "SELECT value_id FROM ".$this->_getTableName('catalog_product_entity_'.$attributeType)." cped
                            WHERE cped.entity_type_id = ?
                            AND cped.attribute_id = ?
                            AND cped.entity_id = ?
                            AND cped.store_id = 0 ";
            $res = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($sql, array($this->_entityTypeId,$attributeId,$productId));
            if($res){
                $sql = "UPDATE " . $this->_getTableName('catalog_product_entity_'.$attributeType) . " cped
                        SET  cped.value = ?
                        WHERE  cped.attribute_id = ?
                        AND cped.entity_id = ?";
                Mage::getSingleton('core/resource')->getConnection('core_write')->query($sql, array($attributeValue, $attributeId, $productId));
            }
            else{
                $sql = "INSERT INTO ".$this->_getTableName('catalog_product_entity_'.$attributeType)."
                        (`entity_type_id` ,`attribute_id` ,`store_id` ,`entity_id` ,`value`)
                        VALUES (".$this->_entityTypeId.", ".$attributeId.", ".'0'.", ".$productId.", '".$attributeValue."')";
                Mage::getSingleton('core/resource')->getConnection('core_write')->query($sql);
            }
        }
    }
    protected function _removeProductImage($_product){
        $query = "select *  FROM `catalog_product_entity_media_gallery` where entity_id = '".$_product->getId()."'";
        $alldata = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
        foreach ($alldata as $item) {
            @unlink(Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS . $item['value']);
        }
        $q = "DELETE FROM `catalog_product_entity_media_gallery` where entity_id = '".$_product->getId()."'";
        Mage::getSingleton('core/resource')->getConnection('core_write')->query($q);
      /*$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
        $mediaApiItems = $mediaApi->items($_product->getId());
        foreach($mediaApiItems as $item) {
            $datatemp=$mediaApi->remove($_product->getId(), $item['file']);
            @unlink(Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS . $item['file']);
        }*/
    }
    public function importCategories(){
        $categories = array();
        foreach($this->_webCategories as $sku => $catJson ){
            foreach ($catJson as $key => $value) {
                $categories[$sku][] = array(
                    "name" => $value['name'],
                    "path"=> $value['path']
                );
            }
        }
        foreach($categories as $id => $category){
            foreach ($category as $key => $value) {
                $perents = explode('/',$value['path']) ;
                $parentId = '';
                $level = 2;
                $path = $this->rootCategoryPath;
                foreach($perents as $ind => $parent){
                    $parent = str_replace('-',' ',$parent);
                    $parent = ucwords($parent);
                    if($temp = $this->_dbCategories[$path][trim(strtolower(str_replace(" ",'',$parent)))]){
                        $parentId = $temp;
                    }
                    else{
                        $parentId = $this->createNewCategory($parent ,$path);
                    }
                    $path .= '/'.$parentId;
                    $level++;

                }
                $this->_categoryIds[$id][] = $parentId;
            }
        }
    }
    public function collectCetegories(){
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addNameToResult()
            ->addIsActiveFilter()
            ->addOrderField('path');
        foreach($collection as $category){
            if($category->getId() > 2){

                $allPathCat = explode('/',$category->getPath());
                array_pop($allPathCat);
                $allPathCat = implode('/',$allPathCat);

                $this->_dbCategories[$allPathCat][strtolower(str_replace(" ",'',$category->getName()))]   = $category->getId();
            }
        }
        return $this->_dbCategories;
    }
    public function clearPrdctCategoryName($myCatName2){
        $clearedCatName = str_replace(' ', '-', $myCatName2);
        $clearedCatName2 = strtolower($clearedCatName);
        return $clearedCatName2;
    }
    public function createNewCategory($cName, $catPath = false){
        if($cName ==''){
            return true;
        }
        $cUrlKey =  $this->clearPrdctCategoryName($cName);
        $category = Mage::getModel('catalog/category');
        $category->setName($cName);
        $category->setIsActive(1);
        $category->setUrlKey($cUrlKey);
        $category->setDescription($cName);
        $category->setDisplayMode('PRODUCTS');
        $category->setIsAnchor(1);
        $category->setPath($catPath);
        $category->setIncludeInMenu("1");
        try {
            if($category->save()){
                $allPathCat = explode('/',$category->getPath());
                array_pop($allPathCat);
                $allPathCat = implode('/',$allPathCat);
                $this->_dbCategories[$allPathCat][strtolower(str_replace(" ",'',$category->getName()))]   = $category->getId();
            }
        } catch (Mage_Core_Exception $e) {
            Mage::log("Error occured while trying to save the category($cName). Error: ", null, 'cron-import.log');
            Mage::log($e->getMessage(), null, 'cron-import.log');
        }

        return $category->getId();
    }
    public function json_validator($data=NULL) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }
    public function createUpdateSimpleProduct($updateSkus,$data){
        $updateproduct = '';
        $u = 0;
        foreach($updateSkus as $sku){
            //if($u > 9): exit('stop'); endif;
            $category = array();
            $value = $data[$sku];
            $simProduct = Mage::getModel("catalog/product")->loadByAttribute('sku',$sku);
            if($simProduct->getId()){
                $transId = ($value["TRANS_ID"] !="NA" && $value["TRANS_ID"]) ? $value["TRANS_ID"] : '';
                $transItemId = ($value["TRANS_ITEM_ID"] !="NA" && $value["TRANS_ITEM_ID"]) ? $value["TRANS_ITEM_ID"] : '';
                $itemId = ($value["ITEM_ID"] !="NA" && $value["ITEM_ID"]) ? $value["ITEM_ID"] : '';
                $itemName = ($value["ITEM_NAME"] !="NA" && $value["ITEM_NAME"]) ? $value["ITEM_NAME"] : '';
                $smryItemType = ($value["SMRY_ITEM_TYPE"] !="NA" && $value["SMRY_ITEM_TYPE"]) ? strtolower($value["SMRY_ITEM_TYPE"]) : '';
                $variantId = ($value["VARIANT_ID"] !="NA" && $value["VARIANT_ID"])  ? $value["VARIANT_ID"] : '';
                $pieces = ($value["PIECES"] !="NA" && $value["PIECES"])  ? $value["PIECES"] : '';
                $carat = ($value["WEIGHT"] !="NA" && $value["WEIGHT"]) ? $value["WEIGHT"] : '';
                $weight = ($value["WEIGHT"] !="NA" && $value["WEIGHT"])  ? $value["WEIGHT"] : '';
                $parentChildMapping = ($value["PARENT_CHILD_MAPPING"] !="NA" && $value["PARENT_CHILD_MAPPING"]) ? $value["PARENT_CHILD_MAPPING"] : '';
                $varientName = ($value["VARIANT_NAME"] !="NA" && $value["VARIANT_NAME"]) ? $value["VARIANT_NAME"] : '';
                $rowIdentity = ($value["ROW_IDENTITY"] !="NA" && $value["ROW_IDENTITY"]) ? $value["ROW_IDENTITY"] : '';
                $oldVarientName = ($value["OLD_VARIANT_NAME"] !="NA" && $value["OLD_VARIANT_NAME"]) ? $value["OLD_VARIANT_NAME"] : '';
                $designer = ($value["DESIGNER"] !="NA" && $value["DESIGNER"])  ? $value["DESIGNER"] : '';
                $defaultInd = ($value["DEFAULT_IND"] !="NA" && $value["DEFAULT_IND"])  ? $value["DEFAULT_IND"] : '';
                $brand = ($value["BRAND"] !="NA" && $value["BRAND"]) ? $value["BRAND"] : '';
                $shape = ($value["SHAPE"] !="NA" && $value["SHAPE"]) ? $value["SHAPE"] : '';
                $metalColor = ($value["METAL_COLOR"] !="NA" && $value["METAL_COLOR"]) ? $value["METAL_COLOR"] : '';
                $karat = ($value["KARAT"] !="NA" && $value["KARAT"]) ? $value["KARAT"] : '';
                $quality = ($value["QUALITY"] !="NA" && $value["QUALITY"]) ? $value["QUALITY"] : '';
                $metalType = ($value["METAL_TYPE"] !="NA" && $value["METAL_TYPE"]) ? $value["METAL_TYPE"] : '';
                $finish = ($value["FINISH"] !="NA" && $value["FINISH"]) ? $value["FINISH"] : '';
                $totalDiaWt = ($value["TOTAL_DIA_WT"] !="NA" && $value["TOTAL_DIA_WT"]) ? $value["TOTAL_DIA_WT"] : '';
                $gender = ($value["GENDER"] !="NA" && $value["GENDER"]) ? $value["GENDER"] : '';
                $productType = ($value["PRODUCT_TYPE"] !="NA" && $value["PRODUCT_TYPE"]) ? $value["PRODUCT_TYPE"] : '';
                $productSize = ($value["PRODUCT_SIZE"] !="NA" && $value["PRODUCT_SIZE"]) ? $value["PRODUCT_SIZE"] : '';
                $finishType = ($value["FINISH_TYPE"] !="NA" && $value["FINISH_TYPE"]) ? $value["FINISH_TYPE"] : '';
                $chainLength = ($value["CHAIN_LENGTH"] !="NA" && $value["CHAIN_LENGTH"]) ? $value["CHAIN_LENGTH"] : '';
                $style = ($value["STYLE"] !="NA" && $value["STYLE"]) ? $value["STYLE"] : '';
                $styleId = ($value["STYLE"] !="NA" && $value["STYLE"]) ? $value["STYLE"] : '';
                $engagement = ($value["ENGAGEMENT"] !="NA" && $value["ENGAGEMENT"]) ? $value["ENGAGEMENT"] : '';
                $chainType = ($value["CHAIN_TYPE"] !="NA" && $value["CHAIN_TYPE"]) ? $value["CHAIN_TYPE"] : '';
                $certColor = ($value["CERT_COLOR"] !="NA" && $value["CERT_COLOR"]) ? $value["CERT_COLOR"] : '';
                $productMethod = ($value["PRODUCING_METHOD"] !="NA" && $value["PRODUCING_METHOD"]) ? $value["PRODUCING_METHOD"] : '';
                $assembly= ($value["ASSEMBLY"] !="NA" && $value["ASSEMBLY"]) ? $value["ASSEMBLY"] : '';
                $description = ($value["DESCRIPTION"] !="NA" && $value["DESCRIPTION"]) ? $value["DESCRIPTION"] : '';
                $comments = ($value["COMMENTS"] !="NA" && $value["COMMENTS"]) ? $value["COMMENTS"] : '';
                $styleSuffix = ($value["STYLE_SUFFIX"] !="NA" && $value["STYLE_SUFFIX"]) ? $value["STYLE_SUFFIX"] : '';
                $bandWidth = ($value["BAND_WIDTH"] !="NA" && $value["BAND_WIDTH"]) ? $value["BAND_WIDTH"] : '';
                $clasp = ($value["CLASP"] !="NA" && $value["CLASP"]) ? $value["CLASP"] : '';
                $maximumHeight = ($value["MAXIMUM_HEIGHT"] !="NA" && $value["MAXIMUM_HEIGHT"]) ? $value["MAXIMUM_HEIGHT"] : '';
                $maximumWidth = ($value["MAXIMUM_WIDTH"] !="NA" && $value["MAXIMUM_WIDTH"]) ? $value["MAXIMUM_WIDTH"] : '';
                $construction = ($value["CONSTRUCTION"] !="NA" && $value["CONSTRUCTION"]) ? $value["CONSTRUCTION"] : '';
                $backType = ($value["BACK_TYPE"] !="NA" && $value["BACK_TYPE"]) ? $value["BACK_TYPE"] : '';
                $bangle = ($value["BANGLE"] !="NA" && $value["BANGLE"]) ? $value["BANGLE"] : '';
                $closure = ($value["CLOSURE"] !="NA" && $value["CLOSURE"]) ? $value["CLOSURE"] : '';
                $chainIncluded = ($value["CHAIN_INCLUDED"] !="NA" && $value["CHAIN_INCLUDED"]) ? $value["CHAIN_INCLUDED"] : '';
                $baleType = ($value["BALE_TYPE"] !="NA" && $value["BALE_TYPE"]) ? $value["BALE_TYPE"] : '';
                $stoneShape = ($value["STONE_SHAPE"] !="NA" && $value["STONE_SHAPE"]) ? $value["STONE_SHAPE"] : '';
                $stoneQuality = ($value["STONE_QUALITY"] !="NA" && $value["STONE_QUALITY"]) ? $value["STONE_QUALITY"] : '';
                $stoneRange = ($value["STONE_RANGE"] !="NA" && $value["STONE_RANGE"]) ? $value["STONE_RANGE"] : '';
                $stoneCut = ($value["STONE_CUT"] !="NA" && $value["STONE_CUT"]) ? $value["STONE_CUT"] : '';
                $stoneColor = ($value["STONE_COLOR"] !="NA" && $value["STONE_COLOR"]) ? $value["STONE_COLOR"] : '';
                $measurement = ($value["MEASUREMENT"] !="NA" && $value["MEASUREMENT"]) ? $value["MEASUREMENT"] : '';
                $cHeight = ($value["C_HEIGHT"] !="NA" && $value["C_HEIGHT"]) ? $value["C_HEIGHT"] : '';
                $pavilionDepth = ($value["PAVILION_DEPTH"] !="NA" && $value["PAVILION_DEPTH"]) ? $value["PAVILION_DEPTH"] : '';
                $gridle = ($value["GRIDLE"] !="NA" && $value["GRIDLE"]) ? $value["GRIDLE"] : '';
                $polish = ($value["POLISH"] !="NA" && $value["POLISH"]) ? $value["POLISH"] : '';
                $symmetry = ($value["SYMMETRY"] !="NA" && $value["SYMMETRY"]) ? $value["SYMMETRY"] : '';
                $culet = ($value["CULET"] !="NA" && $value["CULET"]) ? $value["CULET"] : '';
                $fluorescence = ($value["FLUORESCENCE"] !="NA" && $value["FLUORESCENCE"]) ? $value["FLUORESCENCE"] : '';
                $width = ($value["WIDTH"] !="NA" && $value["WIDTH"]) ? $value["WIDTH"] : '';
                $length = ($value["LENGTH"] !="NA" && $value["LENGTH"]) ? $value["LENGTH"] : '';
                $series = ($value["SERIES"] !="NA" && $value["SERIES"]) ? $value["SERIES"] : '';
                $stockCategory = ($value["STOCK_CATEGORY"] !="NA" && $value["STOCK_CATEGORY"]) ? $value["STOCK_CATEGORY"] : '';
                $shade = ($value["SHADE"] !="NA" && $value["SHADE"]) ? $value["SHADE"] : '';
                $shadeReference = ($value["SHADE_REFERENCE"] !="NA" && $value["SHADE_REFERENCE"]) ? $value["SHADE_REFERENCE"] : '';
                $gridlePer = ($value["GRIDLE_PER"] !="NA" && $value["GRIDLE_PER"]) ? $value["GRIDLE_PER"] : '';
                $tablePer = ($value["TABLE_PER"] !="NA" && $value["TABLE_PER"]) ? $value["TABLE_PER"] : '';
                $totalDepth = ($value["TOTAL_DEPTH"] !="NA" && $value["TOTAL_DEPTH"]) ? $value["TOTAL_DEPTH"] : '';
                $cAngle = ($value["C_ANGLE"] !="NA" && $value["C_ANGLE"]) ? $value["C_ANGLE"] : '';
                $pavilionAngle = ($value["PAVILION_ANGLE"] !="NA" && $value["PAVILION_ANGLE"]) ? $value["PAVILION_ANGLE"] : '';
                $subSize = ($value["SUB_SIZE"] !="NA" && $value["SUB_SIZE"]) ? $value["SUB_SIZE"] : '';
                $heightMm = ($value["HEIGHT_MM"] !="NA" && $value["HEIGHT_MM"]) ? $value["HEIGHT_MM"] : '';
                $pavilionHeight = ($value["PAVILION_HEIGHT"] !="NA" && $value["PAVILION_HEIGHT"]) ? $value["PAVILION_HEIGHT"] : '';
                $familyColor = ($value["FAMILY_COLOR"] !="NA" && $value["FAMILY_COLOR"]) ? $value["FAMILY_COLOR"] : '';
                $ratioLW = ($value["RATIO_L_W"] !="NA" && $value["RATIO_L_W"]) ? $value["RATIO_L_W"] : '';
                $postShade = ($value["POST_SHADE"] !="NA" && $value["POST_SHADE"]) ? $value["POST_SHADE"] : '';
                $certifiedColor = ($value["CERTIFIED_COLOR"] !="NA" && $value["CERTIFIED_COLOR"]) ? $value["CERTIFIED_COLOR"] : '';
                $roughShape = ($value["ROUGH_SHAPE"] !="NA" && $value["ROUGH_SHAPE"]) ? $value["ROUGH_SHAPE"] : '';
                $roughPurity = ($value["ROUGH_PURITY"] !="NA" && $value["ROUGH_PURITY"]) ? $value["ROUGH_PURITY"] : '';
                $roughScShade = ($value["ROUGH_SC_SHADE"] !="NA" && $value["ROUGH_SC_SHADE"]) ? $value["ROUGH_SC_SHADE"] : '';
                $certificate = ($value["CERTIFICATE"] !="NA" && $value["CERTIFICATE"]) ? $value["CERTIFICATE"] : '';
                $heartAndArrow = ($value["HEART_AND_ARROW"] !="NA" && $value["HEART_AND_ARROW"]) ? $value["HEART_AND_ARROW"] : '';
                $nativePurity = ($value["NATIVE_PURITY"] !="NA" && $value["NATIVE_PURITY"]) ? $value["NATIVE_PURITY"] : '';
                $sShade = ($value["S_SHADE"] !="NA" && $value["S_SHADE"]) ? $value["S_SHADE"] : '';
                $sPurity = ($value["S_PURITY"] !="NA" && $value["S_PURITY"]) ? $value["S_PURITY"] : '';
                $educationInformation = ($value["EDUCATION_INFORMATION"] !="NA" && $value["EDUCATION_INFORMATION"]) ? $value["EDUCATION_INFORMATION"] : '';
                $lengthXWidth = ($value["LENGTH_X_WIDTH"] !="NA" && $value["LENGTH_X_WIDTH"]) ? $value["LENGTH_X_WIDTH"] : '';
                $make = ($value["MAKE"] !="NA" && $value["MAKE"]) ? $value["MAKE"] : '';
                $baseVariantId = ($value["BASE_VARIANT_ID"] !="NA" && $value["BASE_VARIANT_ID"]) ? $value["BASE_VARIANT_ID"] : '';
                $stockCode = ($value["STOCK_CODE"] !="NA" && $value["STOCK_CODE"]) ? $value["STOCK_CODE"] : '';
                $srNo = ($value["SR_NO"] !="NA" && $value["SR_NO"]) ? $value["SR_NO"] : '';
                $price = ($value["PRICE"] !="NA" && $value["PRICE"]) ? $value["PRICE"] : '0.00';
                $specialPrice = ($value["SPECIAL_PRICE"] !="NA" && $value["SPECIAL_PRICE"]) ? $value["SPECIAL_PRICE"] : '';
                $specialPriceFromDate = ($value["SPECIAL_PRICE_FROM_DATE"] !="NA" && $value["SPECIAL_PRICE_FROM_DATE"]) ? $value["SPECIAL_PRICE_FROM_DATE"] : '';
                $specialPriceToDate = ($value["SPECIAL_PRICE_TO_DATE"] !="NA" && $value["SPECIAL_PRICE_TO_DATE"]) ? $value["SPECIAL_PRICE_TO_DATE"] : '';
                $rowStatus = ($value["ROW_STATUS"] !="NA" && $value["ROW_STATUS"]) ? $value["ROW_STATUS"] : '';
                $smryId = ($value["SMRY_ID"] !="NA" && $value["SMRY_ID"]) ? $value["SMRY_ID"] : '';
                $creationTime = ($value["CREATION_TIME"] !="NA" && $value["CREATION_TIME"]) ? $value["CREATION_TIME"] : '';
                $certificatePdf = ($value["CERTIFICATE_PDF"] !="NA" && $value["CERTIFICATE_PDF"]) ? $value["CERTIFICATE_PDF"] : '';
                $variantRemark = ($value["VARIANT_REMARK"] !="NA" && $value["VARIANT_REMARK"]) ? $value["VARIANT_REMARK"] : '';
                $groupCode = ($value["GROUP_CODE"] !="NA" && $value["GROUP_CODE"]) ? $value["GROUP_CODE"] : '';
                $video = ($value["VIDEO_FILE_NAME"] !="NA" && $value["VIDEO_FILE_NAME"]) ? $value["VIDEO_FILE_NAME"] : '';
                $videohtml = ($value["VIDEO_HTML"] !="NA" && $value["VIDEO_HTML"]) ? $value["VIDEO_HTML"] : '';
                $certificateno = ($value["CERTIFICATE_NO"] !="NA" && $value["CERTIFICATE_NO"]) ? $value["CERTIFICATE_NO"] : '';
                $diapricepercts = ($value["DIA_PRICE_PER_CTS"] !="NA" && $value["DIA_PRICE_PER_CTS"]) ? $value["DIA_PRICE_PER_CTS"] : '';
                $depthPer = ($value["DEPTH_PER"] !="NA" && $value["DEPTH_PER"]) ? $value["DEPTH_PER"] : '';
                $measurements = ($value["MEASUREMENTS"] !="NA" && $value["MEASUREMENTS"]) ? $value["MEASUREMENTS"] : '';
                $lwRatio = ($value["LW_RATIO"] !="NA" && $value["LW_RATIO"]) ? $value["LW_RATIO"] : '';
                if($value['SMRY_ITEM_TYPE']=='DIAMOND'){
                    $productSku = $value["STOCK_CODE"];
                }else{
                    $productSku = $value["VARIANT_ID"];
                }

                $simProduct->setName($itemName);
                $simProduct->setSku($productSku);
                $simProduct->setUrlKey(str_replace(" ", "_",strtolower($itemName)).'-'.$productSku);
                $simProduct->setTypeId('simple');
                $simProduct->setAttributeSetId(4);     // Default
                $simProduct->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                if($description){
                    $simProduct->setDescription($description);
                    $simProduct->setShortDescription($description);
                }else{
                    $simProduct->setDescription($varientName);
                    $simProduct->setShortDescription($varientName);
                }
                $simProduct->setStatus(1);     // Enabled
                $simProduct->setVisibility(4);
                $simProduct->setPrice($price);
                if($price > $specialPrice){
                    $simProduct->setSpecialPrice($specialPrice);
                }
                $simProduct->setTaxClassId(2);
                $simProduct->setCarat($carat);
                $simProduct->setWeight($weight);
                $simProduct->setTransId($transId);
                $simProduct->setTransItemId($transItemId);
                $simProduct->setItemId($itemId);
                $simProduct->setItemName($itemName);
                $simProduct->setVariantId($variantId);
                $simProduct->setVariantName($varientName);
                $simProduct->setRowIdentity($rowIdentity);
                $simProduct->setPieces($pieces);
                $simProduct->setParentChildMapping($parentChildMapping);
                $simProduct->setOldVariantName($oldVarientName);
                $simProduct->setDefaultInd($defaultInd);
                $simProduct->setQuality($quality);
                $simProduct->setDesigner($designer);
                $simProduct->setFinish($finish);
                $simProduct->setSubCategory($subCategory);
                $simProduct->setTotalDiaWt($totalDiaWt);
                $simProduct->setStyle($engagement);
                $simProduct->setStyleId($style);
                $simProduct->setEngagement($engagement);
                $simProduct->setCertColor($certColor);
                $simProduct->setProducingMethod($productMethod);
                $simProduct->setAssebly($assembly);
                $simProduct->setComments($comments);
                $simProduct->setStyleSuffix($styleSuffix);
                $simProduct->setClasp($clasp);
                $simProduct->setMaximumHeight($maximumHeight);
                $simProduct->setMaximumWidth($maximumWidth);
                $simProduct->setConstruction($construction);
                $simProduct->setBackType($backType);
                $simProduct->setBangle($bangle);
                $simProduct->setClosure($closure);
                $simProduct->setChainIncluded($chainIncluded);
                $simProduct->setBaleType($baleType);
                $simProduct->setStoneShape($stoneShape);
                $simProduct->setStoneQuality($stoneQuality);
                $simProduct->setStoneRange($stoneRange);
                $simProduct->setStoneCut($stoneCut);
                $simProduct->setStoneColor($stoneColor);
                $simProduct->setMeasurement($measurement);
                $simProduct->setCHeight($cHeight);
                $simProduct->setPavilionDepth($pavilionDepth);
                $simProduct->setGridle($gridle);
                $simProduct->setPolish($polish);
                $simProduct->setSymmetry($symmetry);
                $simProduct->setCulet($culet);
                $simProduct->setFluorescence($fluorescence);
                $simProduct->setWidth($width);
                $simProduct->setLength($length);
                $simProduct->setSeries($series);
                $simProduct->setStockCategory($stockCategory);
                $simProduct->setChainLength($chainLength);
                $simProduct->setStockCategory($stockCategory);
                $simProduct->setShade($shade);
                $simProduct->setShadeReference($shadeReference);
                $simProduct->setGridlePer($gridlePer);
                $simProduct->setTablePer($tablePer);
                $simProduct->setTotalDepth($totalDepth);
                $simProduct->setCAngle($cAngle);
                $simProduct->setPavilionAngle($pavilionAngle);
                $simProduct->setSubSize($subSize);
                $simProduct->setHeightMm($heightMm);
                $simProduct->setPavilionHeight($pavilionHeight);
                $simProduct->setFamilyColor($familyColor);
                $simProduct->setRatioLW($ratioLW);
                $simProduct->setPostShade($postShade);
                $simProduct->setRoughShape($roughShape);
                $simProduct->setRoughPurity($roughPurity);
                $simProduct->setRoughScShade($roughScShade);
                $simProduct->setCertificate($certificate);
                $simProduct->setHeartAndArrow($heartAndArrow);
                $simProduct->setNativePurity($nativePurity);
                $simProduct->setSShade($sShade);
                $simProduct->setSPurity($sPurity);
                $simProduct->setEducationInformation($educationInformation);
                $simProduct->setLengthXWidth($lengthXWidth);
                $simProduct->setMake($make);
                $simProduct->setBaseVariantId($baseVariantId);
                $simProduct->setStockCode($stockCode);
                $simProduct->setSrNo($srNo);
                $simProduct->setSpecialPriceFromDate($specialPriceFromDate);
                $simProduct->setSpecialPriceToDate($specialPriceToDate);
                $simProduct->setRowStatus($rowStatus);
                $simProduct->setSmryId($smryId);
                $simProduct->setCreationTime($creationTime);
                $simProduct->setCertificatePdf($certificatePdf);
                $simProduct->setVariantRemark($variantRemark);
                $simProduct->setGroupCode($groupCode);
                $simProduct->setVideo($video);
                $simProduct->setVideoHtml($videohtml);
                $simProduct->setCertificateNo($certificateno);
                $simProduct->setDiaPricePerCts($diapricepercts);
                $simProduct->setDepthPer($depthPer);
                $simProduct->setMeasurements($measurements);
                $simProduct->setLwRatio($lwRatio);

                /* Update Inventory*/
                if($value['SMRY_ITEM_TYPE'] == 'DIAMOND'){
                   $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simProduct->getId());
                    if ($stockItem->getId() > 0) {
                        $qty = 1;
                        $stockItem->setQty($qty);
                        $stockItem->setUseConfigManageStock(0);
                        $stockItem->setIsInStock((int)($qty > 0));
                        $stockItem->save();
                    }
                }
                /* End Update Inventory*/
                /* Set Inventory*/
                /*if($value['SMRY_ITEM_TYPE'] == 'DIAMOND'){
                    $stockData = $simProduct->getStockData();
                    $stockData = array(
                        'use_config_manage_stock' => 1, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => 1 //qty
                    );
                    $simProduct->setStockData($stockData);
                }*/

                foreach($this->_dropdown as $attr){
                    $code = strtolower($attr);
                    $values = $value[$attr];
                    if($value[$attr] != "" && $value[$attr] !="NA"){
                        $foundData = $this->arraysearchRecursive($values,$this->_dropdownAttributeOptionData[$code]);
                        $optionId = $this->_dropdownAttributeOptionData[$code][$foundData[0]]['value'];
                        $this->setCustomDropdownProductValue($simProduct,$code,$values,$optionId);
                    }
                }
                 // Set Product Jewelry Attribute
                     if($productType != "" && $productType !="NA"){
                        if($productJewelry = $this->_getSelectAttribute('jewelry',$productType)){
                            $simProduct->setJewelry($productJewelry);
                        }
                        else{
                            if($this->_addSelectAttribute('jewelry',$productType)){
                                if($productJewelry = $this->_getSelectAttribute('jewelry',$productType)){
                                    $simProduct->setJewelry($productJewelry);
                                }
                            }
                        }
                    }

                $simProduct->setCategoryIds($this->_categoryIds[$smryId]);
                // set Image Gallery
                $this->_removeProductImage($simProduct);
                $mediaAttribute = array ('image','small_image','thumbnail');
                $imageArray = array_unique($value["IMAGES"], SORT_REGULAR);
                if(is_array($imageArray)){
                    foreach($imageArray as $image){
                        if($image['IMAGE_FILE_NAME'] != ""){
                            $name = $path = $file = "";
                            $name = basename(trim($image['IMAGE_FILE_NAME']));
                            $name = preg_replace('/\s+/', '', $name);
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                             if($ext !="mp4"  && $ext !="tif"){
                                 if($value['SMRY_ITEM_TYPE']=='DIAMOND'){
                                        $isFileExist = Mage::getBaseDir('media').DS.'diamondimages'.DS . $name;
                                    }else{
                                        $isFileExist = Mage::getBaseDir('media').DS.'styleimages'.DS . $name;
                                    }
                               if(file_exists($isFileExist)) {
                                    $filepath = Mage::getBaseDir('media') . DS . "styleimages" . DS . $name;
                                    if ($image['IMAGE_TYPE'] == "CATALOGUE") {
                                        $simProduct->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
                                    } else {
                                        $simProduct->addImageToMediaGallery($filepath, null, false, false);
                                    }
                                }
                            }elseif ($ext =="mp4"){
                                if($value['SMRY_ITEM_TYPE']=='STYLE'){
                                    $simProduct->setVideo($image['IMAGE_FILE_NAME']);
                                }
                            }
                        }
                    }
                }else{
                    if(!empty($value["DIA_IMAGE"])){
                            $name = $path = $file = "";
                            $name = basename(trim($value["DIA_IMAGE"]));
                            $name = preg_replace('/\s+/', '', $name);
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                             if($ext !="mp4"  && $ext !="tif"){
                                $isFileExist = Mage::getBaseDir('media').DS.'diamondimages'.DS . $name;
                               if(file_exists($isFileExist)) {
                                    $filepath = Mage::getBaseDir('media') . DS . "diamondimages" . DS . $name;
                                    $simProduct->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
                                }
                            }
                    }
                }
                try {
                    $simProduct->save();
                    $diaData = $data[$simProduct->getData('sku')];
                    echo $productSku.' Updated successfully';
                    echo "\r\n";
                    $productId = $simProduct->getId();
                    $this->_updateArray["U"][$productSku] = $varientName;

                    if($diaData['SMRY_ITEM_TYPE'] == 'DIAMOND'){
                        $this->updateWidzetMaster($simProduct,$value);
                    }else{
                        //die($diaData['SMRY_ITEM_TYPE'].' for update only diamond');
                        $this->deleteWidzetRelation($sku);
                        //$this->inserWidzetRelation($simProduct,$value,$sku);
                        $this->inserWidzetRelation($simProduct,$value);
                        $this->updateWidzetMaster($simProduct,$value);
                        //$this->inserWidzetMaster($simProduct,$value);
                    }



                    Mage::log($varientName . " has been updated". "<br/>", null, 'updateproduct.log');
                    //exit;
                }
                catch (Mage_Core_Exception $e) {
                    Mage::log("Error occured while updating the product(".$simProduct->getSku()."). Error: ", null, 'update-product-error.log');
                    Mage::log($e->getMessage(), null, 'update-product-error.log');
                }
            }else{
                Mage::log($sku, null, 'missingupdateproduct.log');
                return   "This Product Doesn't exist" . $sku;
            }
            $u++;
        }
        return $this->_updateArray;
    }
    protected function deleteProduct($productSku){
        $deleteproduct = '';
        foreach($productSku as $sku){
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $varianName = $product->getVariantName();
            $productId = $product->getId();
            if($product) {
                try {
                    $product->delete();
                    $this->deleteWidzetRelation($productId);
                    $this->_deleteArray["D"][$sku] =  $varianName;
                    Mage::log($sku . " Deleted", null, 'delete-product.log');
                } catch (Exception $e) {
                    Mage::log($sku . " Not Deleted", null, 'not-delete-product.log');
                }
            } else {
                Mage::log($sku . " Not Available", null, 'not-available-product.log');
            }
        }
        return $this->_deleteArray;
    }
    public function addAttributeValue($arg_attribute, $arg_value){
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);
        if(!$this->attributeValueExists($arg_attribute, $arg_value))
        {
            $value['option'] = array($arg_value,$arg_value);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);
        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }
       return false;
    }
    public function attributeValueExists($arg_attribute, $arg_value){
        $attribute_model        = Mage::getModel('eav/entity_attribute');
        $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute              = $attribute_model->load($attribute_code);

        $attribute_table        = $attribute_options_model->setAttribute($attribute);
        $options                = $attribute_options_model->getAllOptions(false);

        foreach($options as $option)
        {
            if ($option['label'] == $arg_value)
            {
                return $option['value'];
            }
        }
        return false;
    }
    public function getTradeValentineCategoryIds(){
        $category = Mage::getModel('catalog/category')->loadByAttribute('name', 'Trade');
        $this->_tradeId = $category->getId();
        $category = Mage::getModel('catalog/category')->loadByAttribute('name', 'Valentine');
        $this->_valentineId = $category->getId();
    }

    public function getAllDropdownValueArray(){
        $attrib_data = array(); $allAttributeCodes = array();
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        foreach ($attributes as $attribute){
            if($attribute->getFrontendInput()=='select'){
            $this->_dropdownAttributeOptionData[$attribute->getAttributeCode()] = $attribute->getSource()->getAllOptions(true, false);
           //echo "<pre/>";print_r($attrib_data);
            }
        }
        return $this->_dropdownAttributeOptionData;
        //echo "<pre/>";print_r($this->arraysearchRecursive('BRILLIANT YELLOW',$this->_dropdownAttributeOptionData['certified_color']));
        //$attrib_data[]
        //exit;


    }
    public function arraysearchRecursive( $needle, $haystack, $strict=false, $path=array() )
{
    if( !is_array($haystack) ) {
        return false;
    }
    foreach( $haystack as $key => $val ) {
        if( is_array($val) && $subPath = $this->arraysearchRecursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
}
}
