<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardedit')} ADD  `construction` VARCHAR(50) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('wizardedit')} ADD  `params` LONGTEXT NOT NULL");
$installer->endSetup();