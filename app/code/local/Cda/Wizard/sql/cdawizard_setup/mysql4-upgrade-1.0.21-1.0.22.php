<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `status` TinyInt(1) DEFAULT 1");
$installer->endSetup();