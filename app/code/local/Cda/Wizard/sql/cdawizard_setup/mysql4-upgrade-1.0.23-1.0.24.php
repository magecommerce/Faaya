<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `stock_code` VARCHAR(255) NOT NULL");
$installer->endSetup();