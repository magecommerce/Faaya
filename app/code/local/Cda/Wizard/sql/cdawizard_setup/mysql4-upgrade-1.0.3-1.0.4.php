<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->removeAttribute('catalog_product', 'shipping_image');
$setup->addAttribute('catalog_product', "shipping_image", array(
	'group'             => 'Images',
    'type'              => 'varchar',
    'frontend'          => 'catalog/product_attribute_frontend_image',
    'label'             => 'Shipping Details',
    'input'             => 'media_image',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'default'           => '',
    'class'             => '',
    'source'            => ''	
)); 
$installer->endSetup();