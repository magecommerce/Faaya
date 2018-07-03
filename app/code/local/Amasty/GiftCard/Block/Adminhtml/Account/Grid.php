<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amgiftcard/account')->getCollection()->joinOrder()->joinCode();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		$this->addColumn('account_id', array(
			'header'    => $_helper->__('ID'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'account_id',
		));

		$this->addColumn('gift_code', array(
			'header'    => $_helper->__('Gift Code'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'code',
		));


		$store = $this->_getStore();
		$this->addColumn('initial_value', array(
			'header'    => $_helper->__('Initial Value'),
			'align'     => 'left',
			'index'     => 'initial_value',
			'type'		=> 'price',
			'currency_code' => $store->getBaseCurrency()->getCode(),
		));

		$this->addColumn('current_value', array(
			'header'    => $_helper->__('Current Balance'),
			'align'     => 'left',
			'index'     => 'current_value',
			'type'		=> 'price',
			'currency_code' => $store->getBaseCurrency()->getCode(),
		));

		$this->addColumn('status_id', array(
			'header'    => $_helper->__('Status'),
			'align'     => 'left',
			'type'      => 'options',
			'options'	=> Mage::getModel('amgiftcard/account')->getListStatuses(),
			'index'     => 'status_id',
		));

		$this->addColumn('order_id', array(
			'header'    => $_helper->__('Order'),
			'align'     => 'left',
			'index'     => 'order_number',
			'filter_index' => 'order.increment_id'
		));

		$this->addColumn('expired_date', array(
			'header'    => $_helper->__('Expiry Date'),
			'align'     => 'left',
			'index'     => 'expired_date',
			'type'		=> 'datetime',
			'time'		=> true,
			'filter_condition_callback'
			=> array($this, '_filterDateCondition'),
		));

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
				'index'     => 'store_id',
				'type'      => 'store',
				'store_view'=> true,
				'display_deleted' => true,
				'filter_index' => 'order.store_id',
			));
		}



		$this->addColumn('action',array(
			'header'    => $_helper->__('Action'),
			'width'     => '50px',
			'type'      => 'action',
			'getter'     => 'getId',
			'actions'   => array(
				array(
					'caption' => $_helper->__('Edit'),
					'url'     => array(
						'base'=>'*/*/edit',
					),
					'field'   => 'id'
				)
			),
			'filter'    => false,
			'sortable'  => false,
			//'index'     => 'stores',
		));

		//$this->addExportType('*/*/exportCsv', Mage::helper('amgiftcard')->__('CSV'));
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}



	protected function _filterDateCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		if(is_array($value)) {
			foreach($value as &$item) {
				$item = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', $item);
			}
		} else {
			$value = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', $value);
		}
		$this->getCollection()->addFieldToFilter($column->getIndex(),$value);
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('account_id');
		$this->getMassactionBlock()->setFormFieldName('code_accs');

		$actions = array(
			'massDelete'     => 'Delete',
		);
		foreach ($actions as $code => $label){
			$this->getMassactionBlock()->addItem($code, array(
				'label'    => Mage::helper('amgiftcard')->__($label),
				'url'      => $this->getUrl('*/*/' . $code),
				'confirm'  => ($code == 'massDelete' ? Mage::helper('amgiftcard')->__('Are you sure?') : null),
			));
		}
		return $this;
	}


	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
}