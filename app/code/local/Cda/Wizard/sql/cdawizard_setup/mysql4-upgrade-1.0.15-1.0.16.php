<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `chain_type` VARCHAR(30) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `chain_length` FLOAT(2) NOT NULL");
$installer->endSetup();