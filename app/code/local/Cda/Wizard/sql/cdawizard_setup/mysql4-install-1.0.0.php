<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('wizardattribute')};
CREATE TABLE {$this->getTable('wizardattribute')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `tooltip` TEXT NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('wizardoptions')};
CREATE TABLE {$this->getTable('wizardoptions')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `attr_id` int(11),
  `value` TEXT NOT NULL default '',
  `image` TEXT NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- DROP TABLE IF EXISTS {$this->getTable('wizardoptionsmapping')};
CREATE TABLE {$this->getTable('wizardoptionsmapping')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11),
  `attr_id` int(11),
  `option_id` int(11),
  `value` TEXT NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$installer->endSetup();