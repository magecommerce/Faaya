<?php

class Cminds_Multiwishlist_Block_Adminhtml_Customer_Edit_Tab_View_Grid_Renderer_Item
    extends Mage_Adminhtml_Block_Customer_Edit_Tab_View_Grid_Renderer_Item
{
    public function getProductHelpers()
    {
        return array(
            'bundle' => 'bundle/catalog_product_configuration',
            'default' => 'catalog/product_configuration',
            'downloadable' => 'downloadable/catalog_product_configuration'
        );
    }
}
