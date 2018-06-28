<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


class Amasty_Shopby_Model_Source_Brandcategoriesmode extends Varien_Object
{
    const POSITION = 'position';
    const NAME = 'name';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $hlp = Mage::helper('amshopby');
        return array(
            array('value' => 1, 'label' => $hlp->__('Label & Thumbnail')),
            array('value' => 0, 'label' => $hlp->__('Thumbnail Only'))
        );
    }
}
