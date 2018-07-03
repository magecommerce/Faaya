<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `polish` VARCHAR( 255 ) NOT NULL AFTER `group_code`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `symmetry` VARCHAR( 255 ) NOT NULL AFTER `polish`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `fluorescence` VARCHAR( 255 ) NOT NULL AFTER `symmetry`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `depth_mm` VARCHAR( 255 ) NOT NULL AFTER `fluorescence`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `table_per` VARCHAR( 255 ) NOT NULL AFTER `depth_mm`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `band_width` VARCHAR( 255 ) NOT NULL AFTER `table_per`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `metal_type` VARCHAR( 255 ) NOT NULL AFTER `band_width`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `is_ldmaterial` int(1) NOT NULL Default 0 AFTER `metal_type`");
$installer->endSetup();