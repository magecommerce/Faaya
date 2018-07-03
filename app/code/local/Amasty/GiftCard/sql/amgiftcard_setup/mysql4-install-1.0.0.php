<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/price')} (
  `price_id` int(11) NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `entity_type_id` smallint (5) unsigned NOT NULL,
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint (5) unsigned NOT NULL,
  `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY  (`price_id`),
  CONSTRAINT `FK_AMGIFTCARD_PRICE_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES {$installer->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_PRICE_WEBSITE` FOREIGN KEY (`website_id`) REFERENCES {$installer->getTable('core_website')} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_PRICE_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES {$installer->getTable('eav_attribute')} (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/code_set')} (
  `code_set_id` int(11) unsigned NOT NULL auto_increment,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `template` VARCHAR(255) NOT NULL DEFAULT '',
  `enabled` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`code_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/code')} (
  `code_id` int(11) unsigned NOT NULL auto_increment,
  `code_set_id` int(11) unsigned  NULL,
  `code` VARCHAR(255) NOT NULL DEFAULT '',
  `used` TINYINT(1) NOT NULL DEFAULT '0',
  `enabled` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`code_id`),
  KEY (`code_set_id`),
  UNIQUE KEY (`code`),
  CONSTRAINT `FK_AMGIFTCARD_CODE_CODE_SET` FOREIGN KEY (`code_set_id`) REFERENCES {$installer->getTable('amgiftcard/code_set')} (`code_set_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/image')} (
  `image_id` int(11) unsigned NOT NULL auto_increment,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `code_pos_x` VARCHAR(255) NOT NULL DEFAULT '',
  `code_pos_y` VARCHAR(255) NOT NULL DEFAULT '',
  `image_path` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY  (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
INSERT INTO {$installer->getTable('amgiftcard/image')} (`image_id`, `title`, `active`, `code_pos_x`, `code_pos_y`, `image_path`) VALUES
 (1, 'Gift Card 1', 1, '61', '148', '551d40ec2dc18_gift-card-1.png'),
 (2, 'Gift Card 2', 1, '228', '167', '551d40fc14c23_gift-card-2.png'),
 (3, 'Gift Card 3', 1, '208', '148', '551d413453017_gift-card-3.png'),
 (4, 'Gift Card 4', 1, '215', '174', '551d414d52e85_gift-card-4.png'),
 (5, 'Gift Card 5', 1, '216', '165', '551d4164a4a3a_gift-card-5.png'),
 (6, 'Gift Card 6', 1, '', '', '551d4194c3d09_gift-card-6.png'),
 (7, 'Happy Birthday Gift Card 1', 1, '233', '145', '551d41c00c6af_happy-birthday-gift-card-1.png'),
 (8, 'Happy Birthday Gift Card 2', 1, '243', '166', '551d41de12c53_happy-birthday-gift-card-2.png'),
 (9, 'Happy New Year Gift Card', 1, '157', '136', '551d41faa1694_happy-new-year-gift-card.png'),
 (10, 'Xmas Gift Card 1', 1, '166', '157', '551d421dd605e_-xmas-gift-card-1.png'),
 (11, 'Xmas Gift Card 2', 1, '77', '177', '551d4233783ef_-xmas-gift-card-2.png');
");



$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/image_to_product')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `image_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `FK_AMGIFTCARD_IMAGE_TO_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES {$installer->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_IMAGE_TO_PRODUCT_IMAGE` FOREIGN KEY (`image_id`) REFERENCES {$installer->getTable('amgiftcard/image')} (`image_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/account')} (
  `account_id` int(10) unsigned NOT NULL auto_increment,
  `code_id` int(10) unsigned NOT NULL,
  `image_id` int(10) unsigned NULL,
  `order_id` int(10) unsigned NULL DEFAULT NULL,
  `website_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NULL,
  `status_id` TINYINT(1) NOT NULL,
  `initial_value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `current_value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `expired_date` datetime NULL DEFAULT NULL,
  `comment` text NULL DEFAULT NULL,
  `sender_name`	VARCHAR(255) NULL DEFAULT NULL,
  `sender_email` VARCHAR(255) NULL DEFAULT NULL,
  `recipient_name` VARCHAR(255) NULL DEFAULT NULL,
  `recipient_email` VARCHAR(255) NULL DEFAULT NULL,
  `sender_message` text NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NULL DEFAULT NULL,
  `date_delivery` DATETIME NULL DEFAULT NULL,
  `is_sent` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`account_id`),
  CONSTRAINT `FK_AMGIFTCARD_ACCOUNT_CODE` FOREIGN KEY (`code_id`) REFERENCES {$installer->getTable('amgiftcard/code')} (`code_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_ACCOUNT_TO_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES {$installer->getTable('catalog_product_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_ACCOUNT_IMAGE` FOREIGN KEY (`image_id`) REFERENCES {$installer->getTable('amgiftcard/image')} (`image_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/account_order')} (
  `account_order_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`account_order_id`),
  CONSTRAINT `FK_AMGIFTCARD_ACCOUNT_ORDER_ACCOUNT` FOREIGN KEY (`account_id`) REFERENCES {$installer->getTable('amgiftcard/account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
CREATE TABLE {$installer->getTable('amgiftcard/customer_card')} (
  `customer_card_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`customer_card_id`),
  KEY  (`customer_id`, `account_id`),
  CONSTRAINT `FK_AMGIFTCARD_CUSTOMER_CARD_ACCOUNT` FOREIGN KEY (`account_id`) REFERENCES {$installer->getTable('amgiftcard/account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMGIFTCARD_CUSTOMER_CARD_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");



$fieldList = array(
	'weight',
);


// make these attributes applicable to downloadable products
foreach ($fieldList as $field) {
	$applyTo = explode(',', $installer->getAttribute('catalog_product', $field, 'apply_to'));
	if (!in_array('amgiftcard', $applyTo)) {
		$applyTo[] = 'amgiftcard';
		$installer->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
	}
}
$attributeGroupName = 'Gift Card Information';
foreach($installer->getAllAttributeSetIds('catalog_product') as $attributeSetId) {
	$installer->addAttributeGroup('catalog_product', $attributeSetId, $attributeGroupName, 8);
}

$installer->addAttribute('catalog_product', 'am_giftcard_prices', array(
	'group'             		=> 'Prices',
	'type'              		=> 'decimal',
	'backend'           		=> 'amgiftcard/attribute_backend_giftCard_price',
	'frontend'          		=> '',
	'label'             		=> 'Amounts',
	'input'             		=> 'price',
	'class'             		=> '',
	'source'            		=> '',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           		=> true,
	'required'          		=> false,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
	'used_in_product_listing'	=>true,
	'sort_order'        		=> -5,
));


$installer->addAttribute('catalog_product', 'am_allow_open_amount', array(
	'group'             		=> 'Prices',
	'type'              		=> 'int',
	'backend'           		=> '',
	'frontend'          		=> '',
	'label'             		=> 'Allow Open Amount',
	'input'             		=> 'select',
	'class'             		=> '',
	'source'            		=> 'eav/entity_attribute_source_boolean',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           		=> true,
	'required'          		=> true,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
	'used_in_product_listing' 	=> true,
	'sort_order'        		=> -4,
));


$installer->addAttribute('catalog_product', 'am_open_amount_min', array(
	'group'             		=> 'Prices',
	'type'              		=> 'decimal',
	'backend'           		=> 'catalog/product_attribute_backend_price',
	'frontend'          		=> '',
	'label'             		=> 'Open Amount Min Value',
	'input'             		=> 'price',
	'class'             		=> 'validate-number',
	'source'            		=> '',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           		=> true,
	'required'          		=> false,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
	'used_in_product_listing'	=> true,
	'sort_order'        		=> -3,
));
$installer->addAttribute('catalog_product', 'am_open_amount_max', array(
	'group'             		=> 'Prices',
	'type'              		=> 'decimal',
	'backend'           		=> 'catalog/product_attribute_backend_price',
	'frontend'          		=> '',
	'label'             		=> 'Open Amount Max Value',
	'input'             		=> 'price',
	'class'             		=> 'validate-number',
	'source'            		=> '',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           		=> true,
	'required'          		=> false,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
	'used_in_product_listing' 	=> true,
	'sort_order'        		=> -2,
));

$installer->addAttribute('catalog_product', 'am_giftcard_price_type', array(
	'group'             		=> 'Prices',
	'type'              		=> 'int',
	'backend'           		=> '',
	'frontend'          		=> '',
	'label'             		=> 'Price equal to',
	'input'             		=> 'select',
	'class'             		=> '',
	'source'            		=> 'amgiftcard/source_priceType',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           		=> true,
	'required'          		=> true,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
	'used_in_product_listing' 	=> true,
	'sort_order'        		=> -1,
));

$installer->addAttribute('catalog_product', 'am_giftcard_price_percent', array(
	'group'             => 'Prices',
	'type'              => 'decimal',
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Specify percent',
	'input'             => 'text',
	'class'             => 'validate-number',
	'source'            => '',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'is_configurable'   => false,
	'used_in_product_listing' => true,
	'sort_order'        => 0,
));

// Attributes to gift card tab

$installer->addAttribute('catalog_product', 'am_giftcard_type', array(
	'group'             => $attributeGroupName,
	'type'              => 'int',
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Card Type',
	'input'             => 'select',
	'class'             => '',
	'source'            => 'amgiftcard/source_giftCardType',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'           => true,
	'required'          => true,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'is_configurable'   => false
));


$installer->addAttribute('catalog_product', 'am_giftcard_lifetime', array(
	'group'             => $attributeGroupName,
	'type'              => 'int',
	'backend'           => 'catalog/product_attribute_backend_boolean',
	'frontend'          => '',
	'label'             => 'Lifetime (days)',
	'input'             => 'text',
	'class'             => '',
	'source'            => '',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'input_renderer'   => 'amgiftcard/adminhtml_catalog_product_helper_form_config_lifetime',
	'is_configurable'   => false
));

$installer->addAttribute('catalog_product', 'am_allow_message', array(
	'group'             => $attributeGroupName,
	'type'              => 'int',
	'backend'           => 'catalog/product_attribute_backend_boolean',
	'frontend'          => '',
	'label'             => 'Allow Message',
	'input'             => 'select',
	'class'             => '',
	'source'            => 'eav/entity_attribute_source_boolean',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'is_configurable'   => false,
	'input_renderer'   => 'amgiftcard/adminhtml_catalog_product_helper_form_config_allowMessage',
));

$installer->addAttribute('catalog_product', 'am_email_template', array(
	'group'             => $attributeGroupName,
	'type'              => 'varchar',
	'backend'           => 'catalog/product_attribute_backend_boolean',
	'frontend'          => '',
	'label'             => 'Email Template',
	'input'             => 'select',
	'class'             => '',
	'source'            => 'amgiftcard/source_emailTemplate',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'is_configurable'   => false,
	'input_renderer'   	=> 'amgiftcard/adminhtml_catalog_product_helper_form_config_emailTemplate',
));

$installer->addAttribute('catalog_product', 'am_giftcard_code_set', array(
	'group'             => $attributeGroupName,
	'type'              => 'int',
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Choose gift card code set',
	'input'             => 'select',
	'class'             => '',
	'source'            => 'amgiftcard/source_giftCardCodeSet',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'           => true,
	'required'          => false,
	'user_defined'      => false,
	'default'           => '',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'apply_to'          => 'amgiftcard',
	'is_configurable'   => false,
));

$installer->addAttribute('catalog_product', 'am_giftcard_code_image', array(
	'group'             		=> $attributeGroupName,
	'type'              		=> 'int',
	'backend'           		=> 'amgiftcard/attribute_backend_giftCard_image',
	'frontend'          		=> '',
	'label'             		=> 'Choose gift card images',
	'input'             		=> 'multiselect',
	'class'             		=> '',
	'source'            		=> 'amgiftcard/source_image',
	'global'            		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'           		=> true,
	'required'          		=> false,
	'user_defined'      		=> false,
	'default'           		=> '',
	'searchable'        		=> false,
	'filterable'        		=> false,
	'comparable'        		=> false,
	'visible_on_front'  		=> false,
	'unique'            		=> false,
	'apply_to'          		=> 'amgiftcard',
	'is_configurable'   		=> false,
));
$installer->endSetup();


$installerSales = new Mage_Sales_Model_Resource_Setup('core_setup');
$installerSales->startSetup();

$installerSales->addAttribute('quote', 'am_gift_cards', array('type'=>'text'));
$installerSales->addAttribute('quote', 'am_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('quote', 'am_base_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('quote', 'am_gift_cards_amount_used', array('type'=>'decimal'));
$installerSales->addAttribute('quote', 'am_base_gift_cards_amount_used', array('type'=>'decimal'));


$installerSales->addAttribute('quote_address', 'am_gift_cards', array('type'=>'text'));
$installerSales->addAttribute('quote_address', 'am_used_gift_cards', array('type'=>'text'));
$installerSales->addAttribute('quote_address', 'am_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('quote_address', 'am_base_gift_cards_amount', array('type'=>'decimal'));


$installerSales->addAttribute('order', 'am_gift_cards', array('type'=>'text'));
$installerSales->addAttribute('order', 'am_base_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('order', 'am_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('order', 'am_base_gift_cards_invoiced', array('type'=>'decimal'));
$installerSales->addAttribute('order', 'am_gift_cards_invoiced', array('type'=>'decimal'));
$installerSales->addAttribute('order', 'am_base_gift_cards_refunded', array('type'=>'decimal'));
$installerSales->addAttribute('order', 'am_gift_cards_refunded', array('type'=>'decimal'));


$installerSales->addAttribute('invoice', 'am_base_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('invoice', 'am_gift_cards_amount', array('type'=>'decimal'));


$installerSales->addAttribute('creditmemo', 'am_base_gift_cards_amount', array('type'=>'decimal'));
$installerSales->addAttribute('creditmemo', 'am_gift_cards_amount', array('type'=>'decimal'));

$installerSales->endSetup();


