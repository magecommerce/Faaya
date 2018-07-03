<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `collection` VARCHAR(80) NOT NULL");
$installer->endSetup();