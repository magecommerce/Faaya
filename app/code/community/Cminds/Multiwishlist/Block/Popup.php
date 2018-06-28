<?php

/**
 * Class Cminds_Multiwishlist_Block_Popup
 */
class Cminds_Multiwishlist_Block_Popup extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cminds_multiwishlist/popup.phtml');
    }

    /**
     * Determine if current Page is Cart Page.
     *
     * @return bool
     * @throws Exception
     */
    public function getIsCartPage()
    {
        $request = $this->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if($module == 'checkout' && $controller == 'cart' && $action == 'index')
        {
            return true;
        }

        return false;
    }

}
