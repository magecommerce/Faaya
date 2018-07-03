<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardrelation')} ADD  `item_id` VARCHAR( 255 ) NOT NULL AFTER `group_code`");
$installer->endSetup();