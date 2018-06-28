<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XPATH_CONFIG_GIFT_CARD_LIFETIME = 'amgiftcard/card/lifetime';
	const XPATH_CONFIG_GIFT_CARD_EMAIL_TEMPLATE = 'amgiftcard/email/email_template';
	const XPATH_CONFIG_GIFT_CARD_ALLOW_MESSAGE = 'amgiftcard/card/allow_message';

	protected $_websites = null;

	public function getWebsitesOptions()
	{

		if (is_null($this->_websites)) {
			foreach (Mage::app()->getWebsites() as $website) {
				$this->_websites[$website->getId()] = $website->getName();
			}
		}
		return $this->_websites;
	}

	public function getCardTypes()
	{
		return array(
			Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED => array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED,
				  'label' => $this->__('Both Virtual and Printed')),
			Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED => array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED,
				  'label' => $this->__('Only Printed')),
			Amasty_GiftCard_Model_GiftCard::TYPE_VIRTUAL => array('value' => Amasty_GiftCard_Model_GiftCard::TYPE_VIRTUAL,
				  'label' => $this->__('Only Virtual')),
		);
	}

	public function getCardType($cardType)
	{
		$cardTypes = $this->getCardTypes();

		return isset($cardTypes[$cardType]) ? $cardTypes[$cardType]['label']
			: '';
	}

	public function getValueOrConfig($value, $xmlPath, $store=null)
	{
		if(is_null($value) || $value == '') {
			$value = Mage::getStoreConfig(
				$xmlPath,
				$store
			);
		}

		return $value;
	}

	public function isEnableGiftFormInCart($quote = null)
	{
		if(!$this->isModuleActive()) {
			return false;
		}

		if(is_null($quote)) {
			$items = Mage::getSingleton('checkout/cart')->getItems();
		} else {
			$items = $quote->getAllItems();
		}
		$isAllowedGiftCard = true;
		$listAllowedProductTypes = Mage::getStoreConfig('amgiftcard/general/allowed_product_types');
		if(empty($listAllowedProductTypes)) {
			return false;
		}
		$listAllowedProductTypes = explode(",", $listAllowedProductTypes);

		foreach($items as $item) {
			if($item->getParentItemId()) {
				continue;
			}
			$type = $item->getProduct()->getTypeId();
			// for grouped products
			foreach($item->getOptions() as $option) {
				if($option->getCode() == 'product_type') {
					$type = $option->getValue();
				}
			}
			if(!in_array($type, $listAllowedProductTypes)) {
				$isAllowedGiftCard = false;
				break;
			}
		}

		return $isAllowedGiftCard;
	}

	public function isModuleActive($storeId = null) {
		$storeId = Mage::app()->getStore($storeId)->getId();
		$isActive = Mage::getStoreConfig('amgiftcard/general/active',$storeId);

		return (bool) $isActive;
	}

	public function getAllowedTimezoneCodes()
	{
		$allowedCodes = Mage::getStoreConfig('amgiftcard/card/timezone');
		if(!empty($allowedCodes)) {
			$allowedCodes = explode(",", $allowedCodes);
		} else {
			$allowedCodes = array();
		}
		return $allowedCodes;
	}


	public function getImagesByProduct($product)
	{
		$imageIds = $product->getAmGiftcardCodeImage();
		if(is_null($imageIds)) {
			if ($attribute = $product->getResource()->getAttribute('am_giftcard_code_image')) {
				$attribute->getBackend()->afterLoad($product);
				$imageIds = $product->getData('am_giftcard_code_image');
			}
		}
		if($imageIds && is_array($imageIds) && count($imageIds) > 0) {
			$collection = Mage::getModel('amgiftcard/image')->getCollection()->addFieldToFilter('image_id', array('in'=>$imageIds))->addFieldToFilter('active', Amasty_GiftCard_Model_Image::STATUS_ACTIVE);
			if($collection->getSize() == 0) {
				$collection = array();
			}
		} else {
			$collection = array();
		}

		return $collection;
	}


	public function removeAllCards($quote = null)
	{
		if(is_null($quote)) {
			$quote = Mage::getSingleton('checkout/cart');
		}
		$quoteGiftCards = $quote->getAmGiftCards();
		if($quoteGiftCards) {
			$quoteGiftCards = unserialize($quoteGiftCards);
		}

		if(!$quoteGiftCards) {
			$quoteGiftCards = array();
		}

		if(count($quoteGiftCards) == 0) {
			return;
		}

		$quote->setAmGiftCards(serialize(array()));
		$quote->setAmBaseGiftCardsAmount(0);
		$quote->setAmBaseGiftCardsAmountUsed(0);
		$quote->setAmGiftCardsAmount(0);
		$quote->setAmGiftCardsAmountUsed(0);
		$quote->collectTotals()->save();
	}

	/**
	 * Do not modify return type, its use in auto-add-promo-items module!! ( array_keys(Mage::helper('amgiftcard')->getAmGiftCardFields()); )
	 * @return array
	 */
	public function getAmGiftCardFields()
	{
		$_currencyShortName = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getShortName();
		$checkTimezoneField = count($this->getAllowedTimezoneCodes()) != 1;


		return array(
			'am_giftcard_amount' 			=> array('fieldName' => $this->__('Card Value in %s', $_currencyShortName)),
			'am_giftcard_amount_custom' 	=> array('fieldName' => $this->__('Custom Card Value')),
			'am_giftcard_image' 			=> array('fieldName' => $this->__('Card Image')),
			'am_giftcard_type'				=> array('fieldName' => $this->__('Card Type')),
			'am_giftcard_sender_name'		=> array('fieldName' => $this->__('Sender Name')),
			'am_giftcard_sender_email' 		=> array('fieldName' => $this->__('Sender Email')),
			'am_giftcard_recipient_name'	=> array('fieldName' => $this->__('Recipient Name')),
			'am_giftcard_recipient_email'	=> array('fieldName' => $this->__('Recipient Email')),
			'am_giftcard_date_delivery'		=> array('fieldName' => $this->__('Date of certificate delivery'), 'isCheck'=>Mage::getStoreConfigFlag('amgiftcard/card/choose_delivery_date')),
			'am_giftcard_date_delivery_timezone'		=> array('fieldName' => $this->__('Timezone'), 'isCheck'=>Mage::getStoreConfigFlag('amgiftcard/card/choose_delivery_date') && $checkTimezoneField),
			'am_giftcard_message'			=> array('fieldName' => $this->__('Message'), 'isCheck'=>false),
		);
	}

	/**
	 * Do not modify return type, its use in auto-add-promo-items module!! ( Mage::helper('amgiftcard')->getAmGiftCardFields(); )
	 * @return array
	 */
	public function getAmGiftCardOptionsInCart() {
		return array(
			'am_giftcard_prices',
			'am_allow_open_amount',
			'am_open_amount_min',
			'am_open_amount_max',
			//'am_giftcard_price_type',
			//'am_giftcard_price_percent',
			'am_giftcard_type',
			//'am_giftcard_lifetime',
			'am_allow_message',
			//'am_email_template',
			//'am_giftcard_code_set',
			'am_giftcard_code_image',
		);
	}

}
