<?php
$installer = $this;
$installer->startSetup();
$installer->removeAttribute('catalog_category', 'show_box_position');
$installer->endSetup();