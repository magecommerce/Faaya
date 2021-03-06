<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Review_Add_Form extends Mage_Adminhtml_Block_Review_Add_Form
{
    /**
     * @inherit
     */
    protected function _prepareForm()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_prepareForm();
        }
        $helper = Mage::helper('detailedreview');
        $validator = $this->helper('detailedreview/validate');

        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        $form = new Varien_Data_Form(array('enctype' => 'multipart/form-data'));

        $fieldSet = $form->addFieldset('add_review_form', array('legend' => Mage::helper('review')->__('Review Details')));

        $fieldSet->addField('product_name', 'note', array(
            'label'     => Mage::helper('review')->__('Product'),
            'text'      => 'product_name',
        ));

        $fieldSet->addField('detailed_rating', 'note', array(
            'label'     => Mage::helper('review')->__('Product Rating'),
            'required'  => true,
            'text'      => '<div id="rating_detail">' . $this->getLayout()->createBlock('adminhtml/review_rating_detailed')->toHtml() . '</div>',
        ));

        $fieldSet->addField('status_id', 'select', array(
            'label'     => Mage::helper('review')->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => $statuses,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('select_stores', 'multiselect', array(
                'label'     => Mage::helper('review')->__('Visible In'),
                'required'  => true,
                'name'      => 'select_stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        }

        $fieldSet->addField('nickname', 'text', array(
            'name'      => 'nickname',
            'title'     => Mage::helper('review')->__('Nickname'),
            'label'     => Mage::helper('review')->__('Nickname'),
            'class'     => $validator->getValidationRules('nickname'),
            'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getNicknameMax(),
            'required'  => true,
        ));


        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_image')) {
            $element = $fieldSet->addField('image', 'image', array(
                'label'     => $helper->__('Image'),
                'required'  => true,
                'name'      => 'image'
            ));
            /** @var MageWorkshop_DetailedReview_Block_Adminhtml_Renderer_MultiImage $renderer */
            $renderer = Mage::app()->getLayout()->createBlock('detailedreview/adminhtml_renderer_multiImage');
            $element->setRenderer($renderer);
        }

        if (Mage::getStoreConfig('detailedreview/social_share_optios/recommended_product')) {
            $fieldSet->addField('recommend_to', 'select', array(
                'name'      => 'recommend_to',
                'label'     => $helper->__('Recommend this product to a friend'),
                'options' => Mage::getSingleton('detailedreview/review_recommendProduct')->getOptionArray(),
            ));
        }

        $fieldSet->addField('title', 'text', array(
            'name'      => 'title',
            'title'     => Mage::helper('review')->__('Review Title:'),
            'label'     => Mage::helper('review')->__('Review Title:'),
            'class'     => $validator->getValidationRules('title'),
            'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getTitleMax(),
            'required'  => true,
        ));
        
        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_video')) {
            $fieldSet->addField('video', 'text', array(
                'name'      => 'video',
                'class'     => 'validate-email',
                'title'     => $helper->__('Attached Video'),
                'label'     => $helper->__('Attached Video'),
                'class'     => $validator->getValidationRules('video'),
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_sizing')) {
            $fieldSet->addField('sizing', 'select', array(
                'name'    => 'sizing',
                'title'   => $helper->__('Sizing'),
                'label'   => $helper->__('Sizing'),
                'options' => Mage::getSingleton('detailedreview/review_sizing')->getOptionArray(),
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_about_you')) {
            if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_body_type')) {
                $fieldSet->addField('body_type', 'select', array(
                    'name' => 'body_type',
                    'title' => $helper->__('Body Type'),
                    'label' => $helper->__('Body Type'),
                    'options' => Mage::getSingleton('detailedreview/review_bodyType')->toOptionArray(),
                ));
            }
            $fieldSet->addField('location', 'text', array(
                'label'     => $helper->__('Location'),
                'name'      => 'location',
                'class'     => $validator->getValidationRules('location'),
                'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getLocationMax(),
            ));
            $fieldSet->addField('age', 'text', array(
                'label'     => $helper->__('Age'),
                'name'      => 'age',
                'class'     => $validator->getValidationRules('age'),
                'maxlength' => '3',
            ));
            $fieldSet->addField('height', 'text', array(
                'label'     => $helper->__('Height'),
                'name'      => 'height',
                'class'     => $validator->getValidationRules('height'),
                'maxlength' => '3',
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_good_and_bad_detail')) {
            $fieldSet->addField('good_detail', 'textarea', array(
                'name'      => 'good_detail',
                'title'     => $helper->__('What do you like about this item?'),
                'label'     => $helper->__('What do you like about this item?'),
                'style'     => 'width: 700px; height: 300px;',
                'class'     => $validator->getValidationRules('good_detail'),
                'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getGoodDetailMax(),
            ));

            $fieldSet->addField('no_good_detail', 'textarea', array(
                'name'      => 'no_good_detail',
                'title'     => $helper->__('What do you dislike about this item?'),
                'label'     => $helper->__('What do you dislike about this item?'),
                'style'     => 'width: 700px; height: 300px;',
                'class'     => $validator->getValidationRules('no_good_detail'),
                'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getNoGoodDetailMax(),
            ));
        }

        if (Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_pros_and_cons')) {
            $fieldSet->addField('pros', 'multiselect', array(
                'name'      => 'pros',
                'label'     => $helper->__('Pros'),
                'title'     => $helper->__('Pros'),
                'values'    => Mage::helper('detailedreview')->getProsConsValues(MageWorkshop_DetailedReview_Model_Source_EntityType::PROS),
                'class'     => $validator->getValidationRules('user_pros'),
                'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getUserProsMax(),
            ));

            $fieldSet->addField('cons', 'multiselect', array(
                'name'      => 'cons',
                'label'     => $helper->__('Cons'),
                'title'     => $helper->__('Cons'),
                'values'    => Mage::helper('detailedreview')->getProsConsValues(MageWorkshop_DetailedReview_Model_Source_EntityType::CONS),
                'class'     => $validator->getValidationRules('user_cons'),
                'maxlength' => MageWorkshop_DetailedReview_Helper_Config::getUserConsMax(),
            ));
        }

        $fieldSet->addField('detail', 'textarea', array(
            'name'      => 'detail',
            'title'     => Mage::helper('review')->__('Overall Review'),
            'label'     => Mage::helper('review')->__('Overall Review'),
            'style'     => 'width: 700px; height: 300px;',
            'required'  => true,
            'class'     => $validator->getValidationRules('detail'),
        ));

        if (Mage::getStoreConfig('detailedreview/show_review_info_settings/allow_response')) {
            $fieldSet->addField('response', 'textarea', array(
                'name'      => 'response',
                'title'     => $helper->__('Administration Response'),
                'label'     => $helper->__('Administration Response'),
                'style'     => 'width: 700px; height: 300px;',
            ));
        }

        $fieldSet->addField('created_at', 'date', array(
            'label'		=> Mage::helper('review')->__('Created at'),
            'required'	=> false,
            'name'		=> 'created_at',
            'time'		=> true,
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'style' => 'width: 140px;'
        ));
        $fieldSet->addField('product_id', 'hidden', array(
            'name'      => 'product_id',
        ));
        /*$gridFieldset = $form->addFieldset('add_review_grid', array('legend' => Mage::helper('review')->__('Please select a product')));
        $gridFieldset->addField('products_grid', 'note', array(
            'text' => $this->getLayout()->createBlock('adminhtml/review_product_grid')->toHtml(),
        ));*/
        Mage::dispatchEvent('detailedreview_adminhtml_review_add_prepare_form', array('form' => $form));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/post'));

        $this->setForm($form);
    }
}
