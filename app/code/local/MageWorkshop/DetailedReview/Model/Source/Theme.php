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
class MageWorkshop_DetailedReview_Model_Source_Theme
{
    /**
     * @return array
     */
    public static function toOptionArray()
    {
        $helper = Mage::helper('detailedreview');
        return array(
            'standard' => $helper->__('Standard'),
            'beige'    => $helper->__('Beige'),
        );
    }
}
