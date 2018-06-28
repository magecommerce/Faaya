<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('wizardmaster')};
CREATE TABLE {$this->getTable('wizardmaster')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11),
  `item_id` int(11),
  `variant_id` int(11),
  `smry_id` int(11),
  `base_variant_id` int(11),
  `category_id` int(11),
  `construction` VARCHAR(255) NOT NULL default '',
  `product_type` VARCHAR(255) NOT NULL default '',
  `variant_remark` TEXT NOT NULL default '',
  `variant_name` VARCHAR(255) NOT NULL default '',
  `price` DECIMAL(12,4) NOT NULL,
  `special_price` DECIMAL(12,4) NOT NULL,
  `total_dia_wt` VARCHAR(255) NOT NULL default '',
  `metal_color` VARCHAR(255) NOT NULL default '',
  `karat` VARCHAR(255) NOT NULL default '',
  `weight` VARCHAR(255) NOT NULL default '',
  `sub_category` VARCHAR(255) NOT NULL default '',
  `stone_shape` VARCHAR(255) NOT NULL default '',
  `row_identity` VARCHAR(255) NOT NULL default '',
  `product_size` VARCHAR(255) NOT NULL default '',
  `stone_quality` VARCHAR(255) NOT NULL default '',
  `group_code` VARCHAR(255) NOT NULL default '',
  `stone_cut` VARCHAR(255) NOT NULL default '',
  `stone_color` VARCHAR(255) NOT NULL default '',
  `sku` VARCHAR(255) NOT NULL default '',
  `is_default` int(1),
  `is_basevariant` int(1),
  `image` TEXT NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;   
");
$installer->endSetup();