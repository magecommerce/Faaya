<?php

class Cminds_Multiwishlist_Block_Adminhtml_Customer_Edit_Tab_Wishlists
    extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Parent template name
     *
     * @var string
     */
    protected $parentTemplate;


    /**
     * @return mixed
     */
    public function _getCustomer()
    {
        return Mage::registry('current_customer');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Multi Wishlists');
    }

    /**
     * Return Tab title.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Multi Wishlists');
    }

    /**
     * Can show tab in tabs.
     *
     * @return boolean
     */
    public function canShowTab()
    {
        if (!Mage::helper('cminds_multiwishlist')->isEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Tab is hidden.
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_edit_tab_wishlists');
        $this->parentTemplate = $this->getTemplate();
        $this->setTemplate('cminds_multiwishlist/customer/tab/wishlist.phtml');
        $this->setUseAjax(true);
        $this->setDefaultSort('wishlist_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    /**
     * Create customer wishlist item collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _createCollection()
    {
        return Mage::getModel('cminds_multiwishlist/item')->getCollection()
            ->setWebsiteId($this->_getCustomer()->getWebsiteId())
            ->setCustomerGroupId($this->_getCustomer()->getGroupId());
    }

    /**
     * Prepare customer wishlist product collection
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _prepareCollection()
    {
        $collection = $this->_createCollection()->addCustomerIdFilter($this->_getCustomer()->getId())
            ->resetSortOrder()
            ->addDaysInWishlist()
            ->addStoreData();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _prepareColumns()
    {
        $this->addColumn('wishlist_id', array(
            'header'    => Mage::helper('catalog')->__('Wishlist Id'),
            'index'     => 'wishlist_id',
            'type'      => 'number',
            'filter_index' => 'main_table.wishlist_id'
        ));

        $this->addColumn('wishlist_name', array(
            'header'    => Mage::helper('catalog')->__('Wishlist Name'),
            'index'     => 'wishlist_name',
            'filter_index' => 'multiwishlist.name'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('catalog')->__('Product Name'),
            'index'     => 'product_name',
            'renderer'  => 'cminds_multiwishlist/adminhtml_customer_edit_tab_view_grid_renderer_item'
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('wishlist')->__('User Description'),
            'index'     => 'description',
            'renderer'  => 'adminhtml/customer_edit_tab_wishlist_grid_renderer_description'
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('catalog')->__('Qty'),
            'index'     => 'qty',
            'type'      => 'number'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store', array(
                'header'    => Mage::helper('wishlist')->__('Added From'),
                'index'     => 'store_id',
                'type'      => 'store'
            ));
        }

        $this->addColumn('added_at', array(
            'header'    => Mage::helper('wishlist')->__('Date Added'),
            'index'     => 'added_at',
            'gmtoffset' => true,
            'type'      => 'date'
        ));

        $this->addColumn('days', array(
            'header'    => Mage::helper('wishlist')->__('Days in Wishlist'),
            'index'     => 'days_in_wishlist',
            'type'      => 'number'
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('customer')->__('Action'),
            'index'     => 'wishlist_item_id',
            'renderer'  => 'adminhtml/customer_grid_renderer_multiaction',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('customer')->__('Configure'),
                    'url'       => 'javascript:void(0)',
                    'process'   => 'configurable',
                    'control_object' => 'multiwishlistControl'
                ),
                array(
                    'caption'   => Mage::helper('customer')->__('Delete'),
                    'url'       => '#',
                    'onclick'   => 'return multiwishlistControl.removeItem($wishlist_item_id);'
                )
            )
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/multiwishlist/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Add column filter to collection
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Cminds_Multiwishlist_Block_Adminhtml_Customer_Edit_Tab_Wishlists
     */
    protected function _addColumnFilterToCollection($column)
    {
        /* @var $collection Cminds_Multiwishlist_Model_Resource_Item_Collection */
        $collection = $this->getCollection();
        $value = $column->getFilter()->getValue();
        if ($collection && $value) {
            switch ($column->getId()) {
                case 'product_name':
                    $collection->addProductNameFilter($value);
                    break;
                case 'store':
                    $collection->addStoreFilter($value);
                    break;
                case 'days':
                    $collection->addDaysFilter($value);
                    break;
                default:
                    $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
                    $collection->addFieldToFilter($field, $column->getFilter()->getCondition());
                    break;
            }
        }
        return $this;
    }

    /**
     * Retrieve Grid Parent Block HTML
     *
     * @return string
     */
    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
}
