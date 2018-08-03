<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `certificate_no` VARCHAR(80) NOT NULL");
$installer->endSetup();