<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('wizardmaster')} ADD  `url` TEXT NOT NULL AFTER `image`");
$installer->endSetup();