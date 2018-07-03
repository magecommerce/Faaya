<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('wizardsize')};
CREATE TABLE {$this->getTable('wizardsize')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `variant_id` int(50) NOT NULL default 0,
  `variant_size_id` varchar(255) NOT NULL default '',
  `product_size` varchar(255) NOT NULL default '',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



");

$installer->endSetup();