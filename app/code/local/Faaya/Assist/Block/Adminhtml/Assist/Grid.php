<?php

class Faaya_Assist_Block_Adminhtml_Assist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("assistGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("assist/assist")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("assist")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("unique_id", array(
				"header" => Mage::helper("assist")->__("Unique id"),
				"index" => "unique_id",
				));
				$this->addColumn("email_id", array(
				"header" => Mage::helper("assist")->__("Email id"),
				"index" => "email_id",
				));
				$this->addColumn("contact_no", array(
				"header" => Mage::helper("assist")->__("Contact no"),
				"index" => "contact_no",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return '#';
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_assist', array(
					 'label'=> Mage::helper('assist')->__('Remove Assist'),
					 'url'  => $this->getUrl('*/adminhtml_assist/massRemove'),
					 'confirm' => Mage::helper('assist')->__('Are you sure?')
				));
			return $this;
		}
			

}