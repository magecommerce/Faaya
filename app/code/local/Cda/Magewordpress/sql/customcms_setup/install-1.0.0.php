<?php

$installer = $this;
$installer->startSetup();
$installer->run(' ALTER TABLE `cms_page` ADD `wordpress_page_id` int(10) NOT NULL AFTER `custom_theme_to`; ');
$installer->endSetup();