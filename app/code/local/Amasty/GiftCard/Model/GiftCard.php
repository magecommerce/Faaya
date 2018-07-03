<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_GiftCard extends Mage_Core_Model_Abstract
{
	const OPEN_AMOUNT_NO 			= 0;
	const OPEN_AMOUNT_YES 			= 1;

	const TYPE_VIRTUAL 				= 1;
	const TYPE_PRINTED				= 2;
	const TYPE_COMBINED 			= 3;

	const PRICE_TYPE_EQUAL 			= 0;
	const PRICE_TYPE_PERCENT 		= 1;

	const ALLOW_MESSAGE_NO 			= 0;
	const ALLOW_MESSAGE_YES 		= 1;


	const XML_PATH_LIFETIME 		= 'amgiftcard/card/lifetime';
	const XML_PATH_ALLOW_MESSAGE 	= 'amgiftcard/card/allow_message';
	const XML_PATH_EMAIL_TEMPLATE 	= 'amgiftcard/email/email_template';

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function replaceRendererPrices(Varien_Event_Observer $observer)
	{
		$form = $observer->getEvent()->getForm();
		$priceElement = $form->getElement('am_giftcard_prices');
		if ($priceElement) {
			$priceElement->setRenderer(Mage::app()->getLayout()->createBlock('amgiftcard/adminhtml_renderer_price'));
		}

	}
}