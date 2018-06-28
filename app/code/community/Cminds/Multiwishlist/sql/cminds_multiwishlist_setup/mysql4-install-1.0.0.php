<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'cminds_multiwishlist/multiwishlist'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('cminds_multiwishlist/multiwishlist'))
    ->addColumn('wishlist_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Wishlist ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Customer ID')
    ->addColumn('shared', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Sharing flag (0 or 1)')
    ->addColumn('sharing_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    ), 'Sharing encrypted code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    ), 'Sharing encrypted code')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Last updated date')
    ->addIndex($installer->getIdxName('cminds_multiwishlist/multiwishlist', 'shared'), 'shared')
    ->addForeignKey($installer->getFkName('cminds_multiwishlist/multiwishlist', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Cminds Multi Wishlist main Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'wishlist/item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('cminds_multiwishlist/item'))
    ->addColumn('wishlist_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Wishlist item ID')
    ->addColumn('wishlist_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Wishlist ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Product ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => true,
    ), 'Store ID')
    ->addColumn('added_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Add date and time')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    ), 'Short description of wish list item')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
    ), 'Qty')
    ->addIndex($installer->getIdxName('cminds_multiwishlist/item', 'wishlist_id'), 'wishlist_id')
    ->addForeignKey($installer->getFkName('cminds_multiwishlist/item', 'wishlist_id', 'cminds_multiwishlist/multiwishlist', 'wishlist_id'),
        'wishlist_id', $installer->getTable('cminds_multiwishlist/multiwishlist'), 'wishlist_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('cminds_multiwishlist/item', 'product_id'), 'product_id')
    ->addForeignKey($installer->getFkName('cminds_multiwishlist/item', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('cminds_multiwishlist/item', 'store_id'), 'store_id')
    ->addForeignKey($installer->getFkName('cminds_multiwishlist/item', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Cminds Multi Wishlist items');
$installer->getConnection()->createTable($table);

/**
 * Create table 'wishlist/item_option'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('cminds_multiwishlist/item_option'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Option Id')
    ->addColumn('wishlist_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Wishlist Item Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Product Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
    ), 'Code')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
    ), 'Value')
    ->addForeignKey(
        $installer->getFkName('cminds_multiwishlist/item_option', 'wishlist_item_id', 'cminds_multiwishlist/item', 'wishlist_item_id'),
        'wishlist_item_id', $installer->getTable('cminds_multiwishlist/item'), 'wishlist_item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Cminds Multi Wishlist Item Option Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
