<?php
class Faaya_Customshipping_Block_Adminhtml_Customshipping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('customshippingGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('customshipping/customshipping')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('customshipping')->__('ID'),
			'align'  =>'right',
			'width'  => '50px',
			'index'  => 'id',
		));
		$this->addColumn('jewelery', array(
			'header'  => Mage::helper('customshipping')->__('Jewelery'),
			'align'   => 'left',
			'width'   => '80px',
			'index'   => 'jewelery',
			'type'    => 'options',
			'options' => Mage::getModel('customshipping/adminhtml_system_config_source_jewelery')->getcatArray()
		));
        $this->addColumn('order_time', array(
            'header'  => Mage::helper('customshipping')->__('Order Time'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'order_time',
            'type'    => 'options',
            'options' => array(
                '3-a' => 'After 3 PM',
                '3-b' => 'Before 3 PM',
            ),
            'value' => '3-b',
        ));
         $this->addColumn('jewelery_style', array(
            'header'  => Mage::helper('customshipping')->__('Jewelery Style'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'jewelery_style',
            'type'    => 'options',
            'options' => Mage::getModel('customshipping/adminhtml_system_config_source_subcategory')->toGetSubcategoryArray()
        ));
        $this->addColumn('days', array(
            'header' => Mage::helper('customshipping')->__('Days'),
            'align'  =>'right',
            'width'  => '50px',
            'index'  => 'days',
        ));

		$this->addColumn('action',
			array(
				'header'  =>  Mage::helper('customshipping')->__('Action'),
				'width'   => '100',
				'type'    => 'action',
				'getter'  => 'getId',
				'actions' => array(
					array(
						'caption' => Mage::helper('customshipping')->__('Edit'),
						'url'     => array('base'=> '*/*/edit'),
						'field'   => 'id'
					 )
				  ),
				  'filter'    => false,
				  'sortable'  => false,
				  'index'     => 'stores',
				  'is_system' => true,
		  	)
		);

		$this->addExportType('*/*/exportCsv', Mage::helper('customshipping')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customshipping')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('customshipping');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'   => Mage::helper('customshipping')->__('Delete'),
			'url'     => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('customshipping')->__('Are you sure?')
		));
		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}