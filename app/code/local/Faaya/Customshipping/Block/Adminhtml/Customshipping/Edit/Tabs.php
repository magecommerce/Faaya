<?php
class Faaya_Customshipping_Block_Adminhtml_Customshipping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customshipping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('customshipping')->__('customshipping Item Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('customshipping')->__('customshipping Item'),
            'title'     => Mage::helper('customshipping')->__('customshipping Item'),
            'content'   => $this->getLayout()->createBlock('customshipping/adminhtml_customshipping_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}