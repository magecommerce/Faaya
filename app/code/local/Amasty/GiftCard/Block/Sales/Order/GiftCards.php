<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Sales_Order_GiftCards extends Mage_Core_Block_Template
{
    public function getGiftCards()
    {
        if (!($this->getSource() instanceof Mage_Sales_Model_Order)) {
            return  array();
        }
		$cards = $this->getOrder()->getAmGiftCards();
		if($cards) {
			$cards = unserialize($cards);
		}
		if (!$cards) {
			$cards = array();
		}
        return $cards;
    }

    public function initTotals()
    {
        $total = new Varien_Object(array(
            'code'      => $this->getNameInLayout(),
            'block_name'=> $this->getNameInLayout(),
            'area'      => $this->getArea()
        ));
        $this->getParentBlock()->addTotalBefore($total, array('customerbalance', 'grand_total'));
        return $this;
    }

	public function getLabelProperties()
	{
		return $this->getParentBlock()->getLabelProperties();
	}

	public function getValueProperties()
	{
		return $this->getParentBlock()->getValueProperties();
	}

	public function getOrder()
	{
		return $this->getParentBlock()->getOrder();
	}

	public function getSource()
	{
		return $this->getParentBlock()->getSource();
	}

}
