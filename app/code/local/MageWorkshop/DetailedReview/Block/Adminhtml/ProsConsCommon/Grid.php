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
class MageWorkshop_DetailedReview_Block_Adminhtml_ProsConsCommon_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
        $this->setId($this->_entityName.'Grid');
        $this->setDefaultSort('entity_id');
    }

    /**
     * @inherit
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()->setType($this->_entityType);
        $this->setCollection($collection);
        parent::_prepareCollection();
        $collection->addStoreData();
        return $this;
    }

    /**
     * @inherit
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('detailedreview');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('status',
            array(
                'header'  => $helper->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('detailedreview/source_common_status')->toOptionArray()
        ));

        $this->addColumn('wrote_by',
            array(
                'header'  => $helper->__('Wrote By'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'wrote_by',
                'type'    => 'options',
                'options' => Mage::getModel('detailedreview/source_common_wroteby')->toOptionArray()
        ));

        $this->addColumn('sort_order', array(
            'header'    => $helper->__('Sort Order'),
            'width'     => '50px',
            'index'     => 'sort_order',
            'type'  => 'number',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header'    => Mage::helper('review')->__('Visible In'),
                'index'     => 'store_ids',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    /**
     * @inherit
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('review_proscons');

        $helper = Mage::helper('detailedreview');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $helper->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $helper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem(
            'update_status',
            array(
                'label'      => $helper->__('Update status'),
                'url'        => $this->getUrl('*/*/massUpdateStatus'),
                'additional' => array(
                    'status' => array(
                        'name'   => 'update_status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $helper->__('What do?'),
                        'values' => array(
                            1 => $helper->__('Enable'),
                            0 => $helper->__('Disable')
                        )
                    )
                )
            )
        );
        Mage::dispatchEvent('detailedreview_adminhtml_prosconscommon_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    /**
     * @inherit
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
    }

}
