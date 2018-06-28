<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('skumapping')} ADD  `quoteid` INT(11) NOT NULL");
$installer->endSetup();