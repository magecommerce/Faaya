<?php
require_once 'Fishpig/Wordpress/controllers/SearchController.php';

class Cda_Magewordpress_SearchController extends Fishpig_Wordpress_SearchController {

    public function indexAction()
    {
        $this->_addCustomLayoutHandles(array(
            'wordpress_post_list',
            'wordpress_search_index',
        ));

        $this->_initLayout();

        $helper = $this->getRouterHelper();

        $searchTerm = Mage::helper('wordpress')->escapeHtml($helper->getSearchTerm());

        $this->_title($this->__("Search results for: '%s'", $searchTerm));

        $this->addCrumb('search_label', array('link' => '', 'label' => $this->__('Search results for: ')));
        $this->addCrumb('search_value', array('link' => '', 'label' => $searchTerm));

        $this->renderLayout();
    }
}