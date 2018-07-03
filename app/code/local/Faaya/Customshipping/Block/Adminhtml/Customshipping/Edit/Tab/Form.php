<?php
class Faaya_Customshipping_Block_Adminhtml_Customshipping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('customshipping_data');
        $form  = new Varien_Data_Form();
        $this->setForm($form);
        $form->setHtmlIdPrefix('customshipping_');
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false, 'add_widgets' => false,'files_browser_window_url'=>$this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));

	   $fieldset = $form->addFieldset('customshipping_form', array('legend'=>Mage::helper('customshipping')->__('customshipping information')));

       $fieldset->addField('jewelery', 'select', array(
            'label'     => Mage::helper('customshipping')->__('Jewelery'),
            'name'      => 'jewelery',
            'required'  => true,
            'value' =>'',
            'values'    => Mage::getModel('customshipping/adminhtml_system_config_source_jewelery')->toOptionArray()
        ));
        $fieldset->addField('order_time', 'select', array(
            'label'     => Mage::helper('customshipping')->__('Order Time'),
            'name'      => 'order_time',
            'values'    => array(
               array(
                    'value'     => '',
                    'label'     => Mage::helper('customshipping')->__('Please select'),
                ),
                array(
                    'value'     => '3-a',
                    'label'     => Mage::helper('customshipping')->__('After 3 PM'),
                ),
                array(
                    'value'     => '3-b',
                    'label'     => Mage::helper('customshipping')->__('Before 3 PM'),
                ),
            ),
        ));
        $fieldset->addField('jewelery_style', 'select', array(
            'label'     => Mage::helper('customshipping')->__('Jewelery style'),
            'name'      => 'jewelery_style',
            'required'  => true,
            'values'    => Mage::getModel('customshipping/adminhtml_system_config_source_subcategory')->toOptionArray()
        ));
        
       $fieldset->addField('days', 'text', array(
            'label'     => Mage::helper('customshipping')->__('Days'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'days',
        ));

    /* 
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('banner')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('banner')->__('Enabled'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('banner')->__('Disabled'),
                ),
            ),
        ));*/

        if(Mage::getSingleton('adminhtml/session')->getCustomshippingData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomshippingData());
            Mage::getSingleton('adminhtml/session')->setCustomshippingData(null);
        }
	    elseif(Mage::registry('customshipping_data'))
	    {
		    $data = Mage::registry('customshipping_data')->getData();
		   // $dataGroup = explode(',', $data['customshipping_group']);
		   // $data['banner_group'] = $dataGroup;
            $form->setValues($data);
        }

        return parent::_prepareForm();
    }
}