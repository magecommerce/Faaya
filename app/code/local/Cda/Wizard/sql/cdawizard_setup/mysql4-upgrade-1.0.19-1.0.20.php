<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('skumapping')};
CREATE TABLE {$this->getTable('skumapping')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `editid` bigint(30) NOT NULL,
  `pid` INT(11) NOT NULL,
  `sku` VARCHAR(50) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();