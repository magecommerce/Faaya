<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_CodeSet_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amgiftcard/codeSet')->getCollection()->joinCodeQtyAndUnused();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		$this->addColumn('code_set_id', array(
			'header'    => $_helper->__('ID'),
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'code_set_id',
			'filter_index'		=> 'main_table.code_set_id',
		));

		$this->addColumn('title', array(
			'header'    => $_helper->__('Gift Code Set Title'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'title',
		));

		$this->addColumn('template', array(
			'header'    => $_helper->__('Code Set Template'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'template',
			'getter'	=> 'getTemplate',
		));


		$this->addColumn('qty', array(
			'header'    => $_helper->__('Gift Code Qty'),
			'align'     => 'left',
			//'width'     => '50px',
			'type'		=> 'range',
			'index'     => 'qty',
			'filter_condition_callback'
			=> array($this, '_filterQtyCondition'),
		));

		$this->addColumn('qty_unused', array(
			'header'    => $_helper->__('Unused Gift Codes'),
			'align'     => 'left',
			//'width'     => '50px',
			'type'		=> 'range',
			'index'     => 'qty_unused',
			'filter_condition_callback'
			=> array($this, '_filterQtyUnusedCondition'),
		));

		$this->addColumn('action',array(
			'header'    => $_helper->__('Action'),
			'width'     => '50px',
			'type'      => 'action',
			'getter'     => 'getCodeSetId',
			'actions'   => array(
				array(
					'caption' => $_helper->__('Edit'),
					'url'     => array(
						'base'=>'*/*/editCodeSet',
					),
					'field'   => 'code_set_id'
				)
			),
			'filter'    => false,
			'sortable'  => false,
			//'index'     => 'stores',
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/editCodeSet', array('code_set_id' => $row->getCodeSetId()));
	}

	protected function _afterLoadCollection()
	{
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('code_set_id');
		$this->getMassactionBlock()->setFormFieldName('code_sets');

		$actions = array(
			'massDeleteCodeSet'     => 'Delete',
		);
		foreach ($actions as $code => $label){
			$this->getMassactionBlock()->addItem($code, array(
				'label'    => Mage::helper('amgiftcard')->__($label),
				'url'      => $this->getUrl('adminhtml/amgiftcard/' . $code),
				'confirm'  => ($code == 'massDelete' ? Mage::helper('amgiftcard')->__('Are you sure?') : null),
			));
		}
		return $this;
	}

	protected function _filterQtyCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addFilterQty($value);
	}

	protected function _filterQtyUnusedCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addFilterQtyUnused($value);
	}
}