<?php
class Faaya_Customshipping_Block_Adminhtml_Customshipping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        parent::_prepareLayout();
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'customshipping';
        $this->_controller = 'adminhtml_customshipping';

        $this->_updateButton('save', 'label', Mage::helper('customshipping')->__('Save Customshipping'));
        $this->_updateButton('delete', 'label', Mage::helper('customshipping')->__('Delete Customshipping'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banner_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'customshipping_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'customshipping_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('customshipping_data') && Mage::registry('customshipping_data')->getId() )
        {
            return Mage::helper('customshipping')->__("Edit Customshipping '%s'", $this->htmlEscape(Mage::registry('customshipping_data')->getJewelery()));
        }
        else
        {
            return Mage::helper('customshipping')->__('Add Customshipping Item');
        }
    }
}