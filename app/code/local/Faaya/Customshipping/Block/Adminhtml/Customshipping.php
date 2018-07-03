<?php
class Faaya_Customshipping_Block_Adminhtml_Customshipping extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_customshipping';
        $this->_blockGroup = 'customshipping';
        $this->_headerText = Mage::helper('customshipping')->__('Customshipping Manager');
        $this->_addButtonLabel = Mage::helper('customshipping')->__('Add Customshipping Item');

        parent::__construct();
    }
}