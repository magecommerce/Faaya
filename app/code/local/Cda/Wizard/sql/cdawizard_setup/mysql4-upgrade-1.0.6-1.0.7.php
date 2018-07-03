<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardrelation')} ADD  `group_code` VARCHAR( 255 ) NOT NULL AFTER `variant_refsmryid`");
$installer->endSetup();