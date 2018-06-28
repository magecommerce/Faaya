<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `ref_id` VARCHAR( 50 ) NOT NULL AFTER `am_gift_cards_refunded`");
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order_grid')} ADD  `ref_id`  VARCHAR( 50 ) NOT NULL AFTER `updated_at`");
$installer->endSetup();