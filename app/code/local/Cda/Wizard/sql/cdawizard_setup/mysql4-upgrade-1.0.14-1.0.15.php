<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `lowest_diamond_id` INT(3) NOT NULL AFTER `matchpair`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `lowest_diamond_price` decimal(12,4) NOT NULL AFTER `matchpair`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `multiprice` decimal(12,4) NOT NULL AFTER `matchpair`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `matchpair_id` INT(3) NOT NULL AFTER `matchpair`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `allmetal` LONGTEXT NOT NULL AFTER `matchpair`");
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `allcarat` LONGTEXT NOT NULL AFTER `matchpair`");