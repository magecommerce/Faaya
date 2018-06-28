<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->removeAttribute('catalog_product', 'size_guide');
// the attribute added will be displayed under the group/tab Special Attributes in product edit page

$setup->addAttribute('catalog_product', 'size_guide', array(
    'group'         => '',
    'input'         => 'textarea',
    'type'          => 'text',
    'label'         => 'Size Guide',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => true,
    'is_html_allowed_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$setup->updateAttribute('catalog_product', 'size_guide', 'is_wysiwyg_enabled', 1);
$setup->updateAttribute('catalog_product', 'size_guide', 'is_html_allowed_on_front', 1);
$installer->endSetup();