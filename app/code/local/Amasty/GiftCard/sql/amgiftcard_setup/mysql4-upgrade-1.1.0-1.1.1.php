<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


$installer = $this;
$installer->startSetup();


/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->updateAttribute('catalog_product', 'am_giftcard_code_set', 'is_required', 1);

$installer->endSetup();