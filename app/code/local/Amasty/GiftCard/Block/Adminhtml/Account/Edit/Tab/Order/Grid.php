<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Edit_Tab_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('orderGrid');
		$this->setDefaultSort('pos');
		$this->setSaveParametersInSession(false);
		//$this->setVarNameFilter('filter_orders');
		$this->setUseAjax(true);
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/gridOrders', array('_current'=>true));
	}


	/**
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareCollection()
	{

		$collection = Mage::getResourceModel('amgiftcard/accountOrder_collection')
			->joinOrder('*')
			->addFieldToFilter('main_table.account_id', Mage::registry('amgiftcard_account')->getId());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * @return $this
	 * @throws Exception
	 */
	protected function _prepareColumns()
	{

		$this->addColumn('real_order_id', array(
			'header'=> Mage::helper('sales')->__('Order #'),
			'width' => '80px',
			'type'  => 'text',
			'index' => 'increment_id',
		));



		$this->addColumn('created_at', array(
			'header' => Mage::helper('sales')->__('Purchased On'),
			'index' => 'created_at',
			'type' => 'datetime',
			'width' => '100px',
			'filter_condition_callback'
					 => array($this, '_filterCreatedCondition'),
		));

		$this->addColumn('billing_name', array(
			'header' => Mage::helper('sales')->__('Bill to Name'),
			'index' => 'billing_name',
		));

		$this->addColumn('shipping_name', array(
			'header' => Mage::helper('sales')->__('Shipped to Name'),
			'index' => 'shipping_name',
		));

		/*$this->addColumn('base_grand_total', array(
			'header' => Mage::helper('sales')->__('G.T. (Base)'),
			'index' => 'base_grand_total',
			'type'  => 'currency',
			'currency' => 'base_currency_code',
		));*/

		$this->addColumn('grand_total', array(
			'header' => Mage::helper('sales')->__('Order Total'),
			'index' => 'grand_total',
			'type'  => 'currency',
			'currency' => 'order_currency_code',
		));

		/*$this->addColumn('status', array(
			'header' => Mage::helper('sales')->__('Status'),
			'index' => 'status',
			'type'  => 'options',
			'width' => '70px',
			'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
		));*/

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('order_store_id', array(
				'header'    => Mage::helper('sales')->__('Bought From'),
				'index'     => 'store_id',
				'type'      => 'store',
				'store_view'=> true,
				'display_deleted' => true,
				'filter_condition_callback'
				=> array($this, '_filterStoreCondition'),
			));
		}

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			$this->addColumn('action',
				array(
					'header'    => Mage::helper('sales')->__('Action'),
					'width'     => '50px',
					'type'      => 'action',
					'getter'     => 'getOrderId',
					'actions'   => array(
						array(
							'caption' => Mage::helper('sales')->__('View'),
							'url'     => array('base'=>'*/sales_order/view'),
							'field'   => 'order_id'
						)
					),
					'filter'    => false,
					'sortable'  => false,
					'index'     => 'stores',
					'is_system' => true,
					'data-column' => 'action',
				));
		}

		return parent::_prepareColumns();
	}

	/**
	 * @param $row
	 *
	 * @return string
	 */
	public function getRowUrl($row)
	{
		return $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()));
	}

	/**
	 * @return $this
	 */
	protected function _afterLoadCollection()
	{
		$this->getCollection()->walk('afterLoad');
		return parent::_afterLoadCollection();
	}

	protected function _filterStoreCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addFieldToFilter('order.store_id',$value);
	}

	/**
	 * @param $collection
	 * @param $column
	 */
	protected function _filterCreatedCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addFieldToFilter('order.created_at', $value);
	}
}