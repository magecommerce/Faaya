<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardoptions')} ADD  `sort` INT(3) NOT NULL AFTER `image`");
$installer->endSetup();