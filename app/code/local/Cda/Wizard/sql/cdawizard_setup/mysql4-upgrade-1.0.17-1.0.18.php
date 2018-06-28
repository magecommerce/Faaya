<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('wizardedit')};
CREATE TABLE {$this->getTable('wizardedit')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `editid` INT(20) NOT NULL,
  `data` LONGTEXT NOT NULL default '',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();