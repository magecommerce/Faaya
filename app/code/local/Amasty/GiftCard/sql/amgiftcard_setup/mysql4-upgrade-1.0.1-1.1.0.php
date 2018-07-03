<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


$installer = $this;
$installer->startSetup();

$field = 'tax_class_id';
$applyTo = $installer->getAttribute('catalog_product', $field, 'apply_to');
if ($applyTo) {
	$applyTo = explode(',', $applyTo);
	if (!in_array(Amasty_GiftCard_Model_Catalog_Product_Type_GiftCard::TYPE_GIFTCARD_PRODUCT, $applyTo)) {
		$applyTo[] = Amasty_GiftCard_Model_Catalog_Product_Type_GiftCard::TYPE_GIFTCARD_PRODUCT;
		$installer->updateAttribute('catalog_product', $field, 'apply_to', join(',', $applyTo));
	}
}

$installer->endSetup();