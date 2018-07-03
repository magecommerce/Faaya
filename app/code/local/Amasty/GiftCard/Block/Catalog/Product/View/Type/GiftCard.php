<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Catalog_Product_View_Type_GiftCard extends Mage_Catalog_Block_Product_View_Abstract
{

	public function displayProductStockStatus()
	{
		if(method_exists('Mage_Catalog_Block_Product_View_Abstract', 'displayProductStockStatus')) {
			return parent::displayProductStockStatus();
		}
		$statusInfo = new Varien_Object(array('display_status' => true));
		Mage::dispatchEvent('catalog_block_product_status_display', array('status' => $statusInfo));
		return (boolean)$statusInfo->getDisplayStatus();
	}
	/**
	 * @return bool
	 */
	public function isMultiAmount()
	{
		$product = $this->getProduct();
		return $product->getPriceModel()->isMultiAmount($product);
	}

	/**
	 * @return bool
	 */
	public function isPredefinedAmount()
	{
		return count($this->getListAmounts()) > 0;
	}


	/**
	 * @return array
	 */
	public function getListAmounts()
	{
		//return array();
		$product = $this->getProduct();
		$listAmounts = array();
		foreach($product->getPriceModel()->getAmounts($product) as $amount) {
			$listAmounts[] = Mage::app()->getStore()->roundPrice($amount['website_value']);
		}
		return $listAmounts;
	}

	/**
	 * @return array
	 */
	public function getListCardTypes()
	{
		return Mage::helper('amgiftcard')->getCardTypes();
	}

	/**
	 * @return string
	 */
	public function getCustomerName()
	{
		$firstName = (string)Mage::getSingleton('customer/session')->getCustomer()->getFirstname();
		$lastName  = (string)Mage::getSingleton('customer/session')->getCustomer()->getLastname();

		return trim($firstName . ' ' . $lastName);
	}

	/**
	 * @return string
	 */
	public function getCustomerEmail()
	{
		return (string) Mage::getSingleton('customer/session')->getCustomer()->getEmail();
	}


	public function getImages()
	{
		$product = $this->getProduct();
		return Mage::helper('amgiftcard')->getImagesByProduct($product);
	}


	public function getPricePercent()
	{

		$_product = $this->getProduct();
		return $_product->getAmGiftcardPriceType() == Amasty_GiftCard_Model_GiftCard::PRICE_TYPE_PERCENT ?
			$_product->getAmGiftcardPricePercent() :
			100;
	}


	public function isMessageAvailable($_product) {
		$_amAllowMessage = Mage::helper('amgiftcard')->getValueOrConfig(
			$_product->getAmAllowMessage(),
			Amasty_GiftCard_Model_GiftCard::XML_PATH_ALLOW_MESSAGE,
			Mage::app()->getStore()
		);
		return $_amAllowMessage;
	}


	public function isConfigured()
	{
		$product = $this->getProduct();
		if (!$product->getAmAllowOpenAmount() && !$this->getListAmounts()) {
			return false;
		}
		return true;
	}


	public function getDefaultValue($key)
	{
		return (string) $this->getProduct()->getPreconfiguredValues()->getData($key);
	}

	public function hasDisplayDeliveryDateChooser()
	{
		return Mage::getStoreConfigFlag('amgiftcard/card/choose_delivery_date');
	}

	public function getListTimezones()
	{
		$allowedCodes = Mage::helper('amgiftcard')->getAllowedTimezoneCodes();
		$source = Mage::getModel('adminhtml/system_config_source_locale_timezone')->toOptionArray();
		$allowedTimezones = array();
		$skipRestrictCodes = empty($allowedCodes);
		foreach($source as $timezoneData) {
			if($skipRestrictCodes || in_array($timezoneData['value'], $allowedCodes)) {
				$allowedTimezones[$timezoneData['value']] = $timezoneData['label'];
			}
		}

		return $allowedTimezones;
	}
}
