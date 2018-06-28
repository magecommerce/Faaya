<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `available_size` VARCHAR(255) NOT NULL AFTER `url`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `pinfo` TEXT NOT NULL AFTER `available_size`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `special_character` VARCHAR(10) NOT NULL AFTER `pinfo`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `center_diamond` INT(3) NOT NULL AFTER `special_character`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `matchpair` INT(3) NOT NULL AFTER `center_diamond`");
$installer->endSetup();