<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `back_type` VARCHAR(50) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `depth_per` FLOAT(4,2) NOT NULL");
$installer->endSetup();