<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Catalog_Product_Type_GiftCard extends Mage_Catalog_Model_Product_Type_Abstract
{
	const TYPE_GIFTCARD_PRODUCT = 'amgiftcard';

	/**
	 * Is a configurable product type
	 *
	 * @var bool
	 */
	protected $_canConfigure = true;

	/**
	 * Whether product quantity is fractional number or not
	 *
	 * @var bool
	 */
	protected $_canUseQtyDecimals  = false;

	public function isGiftCard($product = null)
	{
		return true;
	}

	/**
	 * Check is virtual product
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return bool
	 */
	public function isVirtual($product = null)
	{
		$product = $this->getProduct($product);
		$giftCardType = $product->getAmGiftcardType();
		if(is_null($giftCardType)) {
			$giftCardType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'am_giftcard_type', Mage::app()->getStore()->getId());
		}

		if(
			$giftCardType == Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED &&
			$product->hasCustomOptions() &&
			$product->getCustomOption('am_giftcard_type') &&
			$product->getCustomOption('am_giftcard_type')->getValue() == Amasty_GiftCard_Model_GiftCard::TYPE_VIRTUAL
		) {
			return true;
		}

		if ($giftCardType == Amasty_GiftCard_Model_GiftCard::TYPE_VIRTUAL) {
			return true;
		}
		return false;
	}

	/**
	 * Check if product is available for sale
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return bool
	 */
	public function isSalable($product = null)
	{
		$_product = $this->getProduct($product);
		$prices   = $_product->getPriceModel()->getAmounts($product);
		$open     = $_product->getAmAllowOpenAmount();
		if (is_null($open)) {
			$open = $_product->getResource()->getAttributeRawValue($_product->getId(), 'am_allow_open_amount', Mage::app()->getStore());
		}

		if (!$open && !$prices) {
			return false;
		}

		if(!Mage::helper('amgiftcard')->isModuleActive()) {
			return false;
		}

		return parent::isSalable($product);
	}


	/**
	 *
	 * @param  Mage_Catalog_Model_Product $product
	 * @param  Varien_Object $buyRequest
	 * @return array
	 */
	public function processBuyRequest($product, $buyRequest)
	{
		$options = array();
		foreach($this->_customFields() as $field=>$data){
			$options[$field] = $buyRequest->getData($field);
		}
		return $options;
	}

	/**
	 *
	 * @param  Mage_Catalog_Model_Product $product
	 * @return Mage_Catalog_Model_Product_Type_Abstract
	 * @throws Mage_Core_Exception
	 */
	public function checkProductBuyState($product = null)
	{
		parent::checkProductBuyState($product);
		$product = $this->getProduct($product);
		$option = $product->getCustomOption('info_buyRequest');
		if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
			$buyRequest = new Varien_Object(unserialize($option->getValue()));
			$this->_validate($buyRequest, $product, self::PROCESS_MODE_FULL);
		}
		return $this;
	}

	/**
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return $this
	 */
	public function beforeSave($product = null)
	{
		parent::beforeSave($product);
		$this->getProduct($product)->setTypeHasOptions(true);
		$this->getProduct($product)->setTypeHasRequiredOptions(true);
		return $this;
	}

	/**
	 * Prepare product and its configuration to be added to some products list.
	 * Use standard preparation process and also add specific giftcard options.
	 *
	 * @param Varien_Object $buyRequest
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $processMode
	 * @return array|string
	 */
	protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
	{
		$result = parent::_prepareProduct($buyRequest, $product, $processMode);

		if (is_string($result)) {
			return $result;
		}

		try {
			$amount = $this->_validate($buyRequest, $product, $processMode);
		} catch (Mage_Core_Exception $e) {
			return $e->getMessage();
		} catch (Exception $e) {
			Mage::logException($e);
			return Mage::helper('amgiftcard')->__('An error has occurred while preparing Gift Card.');
		}

		$product->addCustomOption('am_giftcard_amount', $amount, $product);

		$defaultTimezone = false;
		$allowedTimezones = Mage::helper('amgiftcard')->getAllowedTimezoneCodes();
		if(count($allowedTimezones) == 1) {
			$defaultTimezone = array_shift($allowedTimezones);
		}
		foreach($this->_customFields() as $field=>$data) {
			if($field == 'am_giftcard_amount') {
				continue;
			}
			if($field == 'am_giftcard_type' && $product->getAmGiftcardType() != Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED) {
				$product->addCustomOption($field, $product->getAmGiftcardType(), $product);
				continue;
			}
			if($field == 'am_giftcard_date_delivery_timezone' && $defaultTimezone) {
				$product->addCustomOption($field, $defaultTimezone, $product);
				continue;
			}

			if($field == 'am_giftcard_date_delivery') {
				$timezone = $buyRequest->getData('am_giftcard_date_delivery_timezone');
				if($defaultTimezone) {
					$timezone = $defaultTimezone;
				}
				$currentDate = strtotime($buyRequest->getData('am_giftcard_date_delivery') . " " . $timezone);
				$date = Mage::app()->getLocale()->utcDate(null, $currentDate)->toString('y-M-d H:m:s');
				$product->addCustomOption($field, $date, $product);
				continue;
			}
			$product->addCustomOption($field, $buyRequest->getData($field), $product);
		}

		return $result;

	}


	protected function _customFields()
	{
		return Mage::helper('amgiftcard')->getAmGiftCardFields();
	}

	/**
	 *
	 * @param Varien_Object $buyRequest
	 * @param  $product
	 * @param  $processMode
	 * @return double|float|mixed
	 */
	private function _validate(Varien_Object $buyRequest, $product, $processMode)
	{
		$product = $this->getProduct($product);
		$currentProduct = Mage::getModel('catalog/product')->load($product->getId());


		$isStrictProcessMode = $this->_isStrictProcessMode($processMode);
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		$allowedAmounts = array();
		$minBaseCustomAmount = $currentProduct->getAmOpenAmountMin();
		$maxBaseCustomAmount = $currentProduct->getAmOpenAmountMax();
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $minCustomAmount = Mage::helper('directory')->currencyConvert($minBaseCustomAmount, $baseCurrencyCode, $currentCurrencyCode);
        $maxCustomAmount = Mage::helper('directory')->currencyConvert($maxBaseCustomAmount, $baseCurrencyCode, $currentCurrencyCode);

		foreach ($this->getProduct($product)->getPriceModel()->getAmounts($product) as $value) {
			$itemAmount = Mage::app()->getStore()->roundPrice($value['website_value']);
			$allowedAmounts[(string)$itemAmount] = $itemAmount;
		}

		$isAmountCustom = $currentProduct->getAmAllowOpenAmount() && ($buyRequest->getAmGiftcardAmount() == 'custom' || count($allowedAmounts) == 0);

		if($isStrictProcessMode) {
			$listErrors = array();

			$listImages = Mage::helper('amgiftcard')->getImagesByProduct($currentProduct);

			$listFields = $this->_customFields();
			$listFields['am_giftcard_amount']['isCheck'] = !(count($allowedAmounts) == 1) && !$isAmountCustom;
			$listFields['am_giftcard_amount_custom']['isCheck'] = $isAmountCustom;
			$listFields['am_giftcard_image']['isCheck'] = (bool)$listImages;
			$listFields['am_giftcard_type']['isCheck'] = $currentProduct->getAmGiftcardType() == Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED;

			if(
				(
					$currentProduct->getAmGiftcardType() == Amasty_GiftCard_Model_GiftCard::TYPE_COMBINED &&
					$buyRequest->getData('am_giftcard_type') == Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED
				) ||
				$currentProduct->getAmGiftcardType() == Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED
			) {
				$listFields['am_giftcard_recipient_name']['isCheck'] = false;
				$listFields['am_giftcard_recipient_email']['isCheck'] = false;
			}

			foreach($listFields as $field=>$data) {
				$isCheck = isset($data['isCheck']) ? $data['isCheck'] : true;
				if(!$buyRequest->getData($field) && $isCheck) {
					$listErrors[] = $_helper->__('Please specify %s', $data['fieldName']);
				}
			}
			$countErrors = count($listErrors);
			if($countErrors > 1) {
				Mage::throwException(
					$_helper->__('Please specify all the required information.')
				);
			} elseif($countErrors) {
				Mage::throwException(
					$listErrors[0]
				);
			}
		}




		$amount = null;
		if($isAmountCustom) {
			if($minCustomAmount && $minCustomAmount > $buyRequest->getAmGiftcardAmountCustom() && $isStrictProcessMode) {
				$minCustomAmountText = Mage::helper('core')->currency($minBaseCustomAmount, true, false);
				Mage::throwException(
					Mage::helper('amgiftcard')->__('Gift Card min amount is %s', $minCustomAmountText)
				);
			}

			if($maxCustomAmount && $maxCustomAmount < $buyRequest->getAmGiftcardAmountCustom() && $isStrictProcessMode)  {
				$maxCustomAmountText = Mage::helper('core')->currency($maxBaseCustomAmount, true, false);
				Mage::throwException(
					Mage::helper('amgiftcard')->__('Gift Card max amount is %s', $maxCustomAmountText)
				);
			}

			if($buyRequest->getAmGiftcardAmountCustom() <= 0 && $isStrictProcessMode) {
				Mage::throwException(
					$_helper->__('Please specify Gift Card Value')
				);
			}

			if(
				(!$minCustomAmount || ($minCustomAmount <= $buyRequest->getAmGiftcardAmountCustom())) &&
				(!$maxCustomAmount || ($maxCustomAmount >= $buyRequest->getAmGiftcardAmountCustom())) &&
				$buyRequest->getAmGiftcardAmountCustom() > 0

			) {
				$amount = $buyRequest->getAmGiftcardAmountCustom();

				$rate = Mage::app()->getStore()->getCurrentCurrencyRate();
				if ($rate != 1) {
					$amount = Mage::app()->getStore()->roundPrice($amount/$rate);
				}
			}
		} else {
			if(count($allowedAmounts) == 1) {
				$amount = array_shift($allowedAmounts);
			} elseif(isset($allowedAmounts[$buyRequest->getAmGiftcardAmount()])) {
				$amount = $allowedAmounts[$buyRequest->getAmGiftcardAmount()];
			} elseif($isStrictProcessMode) {
				Mage::throwException(
					Mage::helper('amgiftcard')->__('Please specify Gift Card amount.')
				);
			}
		}

		return $amount;
	}
}
