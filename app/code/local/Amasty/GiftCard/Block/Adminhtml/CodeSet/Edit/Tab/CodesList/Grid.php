<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_CodeSet_Edit_Tab_CodesList_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('codesListGrid');
		/*$this->setDefaultSort('pos');*/
		$this->setSaveParametersInSession(false);
		//$this->setVarNameFilter('filter_orders');
		$this->setUseAjax(true);
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/gridCode', array('_current'=>true));
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amgiftcard/code')->getCollection()->addFieldToFilter('code_set_id', Mage::registry('amgiftcard_codeSet')->getCodeSetId());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		/*$this->addColumn('code_set_id', array(
			'header'    => $_helper->__('ID'),
			'align'     => 'right',
			'width'     => '50px',
			'index'     => 'code_set_id',
		));*/

		$this->addColumn('code', array(
			'header'    => $_helper->__('Code'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'code',
		));

		$this->addColumn('used', array(
			'header'    => $_helper->__('Used'),
			'align'     => 'left',
			'type'      => 'options',
			'options'	=> array(
				0 => 'No',
				1 => 'Yes',
			),
			'index'     => 'used',
		));
		if(!$this->_isExport) {
			$this->addColumn(
				'action', array(
					'header'   => $_helper->__('Action'),
					'width'    => '50px',
					'type'     => 'action',
					'getter'   => 'getCodeId',
					//'isSystem'	=> true,
					'actions'  => array(
						array(
							'caption' => $_helper->__('Delete'),
							'url'     => array(
								'base' => '*/*/deleteCode',
							),
							'field'   => 'code_id',
							'confirm' => Mage::helper('amgiftcard')->__(
								'Are you sure?'
							),
						)
					),
					'filter'   => false,
					'sortable' => false,
					//'index'     => 'stores',
				)
			);
			$this->getColumn('action')->setFrameCallback(array($this, 'renderAction'));
		}

		$this->addExportType('*/*/exportCodesCsv', Mage::helper('amgiftcard')->__('CSV'));
		$this->addExportType('*/*/exportCodesXml', Mage::helper('amgiftcard')->__('XML'));
		return parent::_prepareColumns();
	}

	public function renderAction($renderedValue, $row, $column, $isExport)
	{
		return $row->isUsed() ? '' : $renderedValue;
	}

	protected function _afterLoadCollection()
	{
		$this->getCollection()->walk('afterLoad');
		return parent::_afterLoadCollection();
	}
}