<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('customshipping')};
CREATE TABLE {$this->getTable('customshipping')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `jewelery` varchar(255) NOT NULL default '',
  `order_time` varchar(255) NOT NULL default '',
  `jewelery_style` varchar(255) NOT NULL default '',
  `days` int(5) NOT NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup(); 