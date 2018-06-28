<?php

/**
 * Class Cminds_Multiwishlist_Block_Customer_Sharing
 */
class Cminds_Multiwishlist_Block_Customer_Sharing extends Mage_Wishlist_Block_Customer_Sharing
{

    /**
     * @return int wishlist_id
     */
    public function getWishlistId()
    {
        return Mage::registry('multiwishlist')->getId();
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        $backUrl = $this->getUrl(
            'multiwishlist/index/view',
            array('wishlist_id' => $this->getWishlistId())
        );

        return $backUrl;
    }

}
