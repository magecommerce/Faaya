<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('customerguestuser')};
CREATE TABLE {$this->getTable('customerguestuser')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(100) NOT NULL default '',
  `party_code` varchar(100) NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;   
");
$installer->endSetup();