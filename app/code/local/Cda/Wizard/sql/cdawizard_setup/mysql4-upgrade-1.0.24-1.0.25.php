<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `description` LONGTEXT NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `gender` VARCHAR(80) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `finish_type` VARCHAR(80) NOT NULL");
$installer->endSetup();