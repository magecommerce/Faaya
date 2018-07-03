<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('wizardrelation')};
CREATE TABLE {$this->getTable('wizardrelation')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11),
  `variant_id` int(11),
  `base_variant_id` int(11),
  `type` TEXT NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;   
");
$installer->endSetup();