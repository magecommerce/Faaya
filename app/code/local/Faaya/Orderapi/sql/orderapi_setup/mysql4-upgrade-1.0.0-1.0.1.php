<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "party_code",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Party Code",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "",
    "frontend" => "",
    "unique"     => false,
    "note"       => ""
        ));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'party_code',
    '999'  
);
$used_in_forms = array();

$used_in_forms[]="adminhtml_customer";

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "party_code");

$attribute->setData("used_in_forms", $used_in_forms)
                ->setData("is_used_for_customer_segment", true)
                ->setData("is_system", 0)
                ->setData("is_user_defined", 1)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100)
                ;
$attribute->save();
$installer->endSetup();