<?php

class Cminds_Multiwishlist_Block_Links extends Mage_Page_Block_Template_Links_Block
{
    /**
     * Position in link list
     * @var int
     */
    protected $_position = 30;

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper('cminds_multiwishlist')->isEnabled()) {
            $text = $this->_createLabel();
            $this->_label = $text;
            $this->_title = $text;
            $this->_url = $this->getUrl('multiwishlist');
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Define label, title and url for wishlist link
     *
     * @deprecated after 1.6.2.0
     */
    public function initLinkProperties()
    {
        $text = $this->_createLabel();
        $this->_label = $text;
        $this->_title = $text;
        $this->_url = $this->getUrl('wishlist');
    }

    /**
     * Create button label based.
     *
     * @return string
     */
    protected function _createLabel()
    {
        return $this->__('My Wishlists');

    }

}
