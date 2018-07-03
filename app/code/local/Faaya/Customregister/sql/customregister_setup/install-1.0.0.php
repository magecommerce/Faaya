<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `special_instruction` VARCHAR( 255 ) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `delivery_call` VARCHAR( 3 ) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('sales_flat_quote')} ADD  `special_instruction`  VARCHAR( 255 ) NOT NULL");
$installer->run("ALTER TABLE {$this->getTable('sales_flat_quote')} ADD  `delivery_call`  VARCHAR( 3 ) NOT NULL");
$installer->endSetup();