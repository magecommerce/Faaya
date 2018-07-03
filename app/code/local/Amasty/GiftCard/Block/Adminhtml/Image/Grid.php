<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('amgiftcard/image')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		$this->addColumn('thumbnail', array(
			'header'    => $_helper->__('Image Thumbnail'),
			'align'     => 'left',
			//'width'     => '50px',
			//'index'     => 'title',
			'getter'     => 'getThumbUrl',
			'renderer'  => 'amgiftcard/adminhtml_renderer_image',
			'filter'  => false
		));

		$this->addColumn('title', array(
			'header'    => $_helper->__('Image title'),
			'align'     => 'left',
			//'width'     => '50px',
			'index'     => 'title',
		));

		$this->addColumn('active', array(
			'header'    => $_helper->__('Status'),
			'align'     => 'left',
			'type'      => 'options',
			'options'	=> array(
				0 => 'Inactive',
				1 => 'Active',
			),
			'index'     => 'active',
		));

		$this->addColumn('action',array(
			'header'    => $_helper->__('Action'),
			'width'     => '50px',
			'type'      => 'action',
			'getter'     => 'getImageId',
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
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}