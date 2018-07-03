<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Checkout_Cart_Total extends Mage_Checkout_Block_Total_Default
{
	protected $_template = 'amasty/amgiftcard/checkout/cart/total.phtml';

	public function getQuoteGiftCards()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$cards = $quote->getAmGiftCards();
		if($cards) {
			$cards = unserialize($cards);
		}
		if (!$cards) {
			$cards = array();
		}
		return $cards;
	}
}
