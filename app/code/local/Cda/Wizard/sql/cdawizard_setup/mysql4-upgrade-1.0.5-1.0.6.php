<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardrelation')} ADD  `variant_refsmryid` VARCHAR( 255 ) NOT NULL AFTER `type`");
$installer->run("ALTER TABLE {$this->getTable('wizardrelation')} ADD  `special_character` VARCHAR( 255 ) NOT NULL AFTER `variant_refsmryid`");
$installer->endSetup();