<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Total_Quote_GiftCard extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct()
	{
		$this->setCode('amgiftcard');
	}


	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		$quote = $address->getQuote();

		if(!Mage::helper('amgiftcard')->isEnableGiftFormInCart($quote)){
			Mage::helper('amgiftcard')->removeAllCards($quote);
		}

		$this->_collectQuoteGiftCards($quote);
		$baseAmountLeft = $quote->getAmBaseGiftCardsAmount() - $quote->getAmBaseGiftCardsAmountUsed();
		$amountLeft = $quote->getAmGiftCardsAmount()-$quote->getAmGiftCardsAmountUsed();

		$skipped = $baseSaved = $saved = 0;

		if ($baseAmountLeft >= $address->getBaseGrandTotal()) {
			$baseUsed = $address->getBaseGrandTotal();
			$used = $address->getGrandTotal();

			$address->setBaseGrandTotal(0);
			$address->setGrandTotal(0);
		} else {
			$baseUsed = $baseAmountLeft;
			$used = $amountLeft;

			$address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseAmountLeft);
			$address->setGrandTotal($address->getGrandTotal()-$amountLeft);
		}

		$addressCards = array();
		$usedAddressCards = array();
		if ($baseUsed) {
			$quoteCards = $quote->getAmGiftCards();
			if($quoteCards) {
				$quoteCards = unserialize($quoteCards);
			}
			if (!$quoteCards) {
				$quoteCards = array();
			}
			foreach ($quoteCards as $quoteCard) {
				$card = $quoteCard;
				if ($quoteCard['ba'] + $skipped <= $quote->getAmBaseGiftCardsAmountUsed()) {
					$baseThisCardUsedAmount = $thisCardUsedAmount = 0;
				} elseif ($quoteCard['ba'] + $baseSaved > $baseUsed) {
					$baseThisCardUsedAmount = min($quoteCard['ba'], $baseUsed-$baseSaved);
					$thisCardUsedAmount = min($quoteCard['a'], $used-$saved);

					$baseSaved += $baseThisCardUsedAmount;
					$saved += $thisCardUsedAmount;
				} elseif ($quoteCard['ba'] + $skipped + $baseSaved > $quote->getAmBaseGiftCardsAmountUsed()) {
					$baseThisCardUsedAmount = min($quoteCard['ba'], $baseUsed);
					$thisCardUsedAmount = min($quoteCard['a'], $used);

					$baseSaved += $baseThisCardUsedAmount;
					$saved += $thisCardUsedAmount;
				} else {
					$baseThisCardUsedAmount = $thisCardUsedAmount = 0;
				}
				$card['ba'] = round($baseThisCardUsedAmount, 4);
				$card['a'] = round($thisCardUsedAmount, 4);
				$addressCards[] = $card;
				if ($baseThisCardUsedAmount) {
					$usedAddressCards[] = $card;
				}

				$skipped += $quoteCard['ba'];
			}
		}

		$address->setAmUsedGiftCards(serialize($usedAddressCards));
		$address->setAmGiftCards(serialize($addressCards));

		$baseTotalUsed = $quote->getAmBaseGiftCardsAmountUsed() + $baseUsed;
		$totalUsed = $quote->getAmGiftCardsAmountUsed() + $used;

		$quote->setAmBaseGiftCardsAmountUsed($baseTotalUsed);
		$quote->setAmGiftCardsAmountUsed($totalUsed);

		$address->setAmBaseGiftCardsAmount($baseUsed);
		$address->setAmGiftCardsAmount($used);

		return $this;

	}

	/**
	 * Return shopping cart total row items
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return $this
	 */
	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{

		if ($address->getQuote()->isVirtual()) {
			$quote = $address->getQuote()->getBillingAddress();
		} else {
			$quote = $address;
		}

		$cards = $quote->getAmGiftCards();
		if($cards) {
			$cards = unserialize($cards);
		}
		if (!$cards) {
			$cards = array();
		}

		$address->addTotal(array(
			'code'=>$this->getCode(),
			'title'=>Mage::helper('amgiftcard')->__('Gift Cards'),
			'value'=>-$address->getAmGiftCardsAmount(),
			'gift_cards'=>$cards,
		));

		return $this;
	}




	protected function _collectQuoteGiftCards($quote)
	{
		if (!$quote->getAmGiftCardsTotalCollected()) {
			$quote->setAmBaseGiftCardsAmount(0);
			$quote->setAmGiftCardsAmount(0);

			$quote->setAmBaseGiftCardsAmountUsed(0);
			$quote->setAmGiftCardsAmountUsed(0);

			$baseAmount = 0;
			$amount = 0;

			$cards = $quote->getAmGiftCards();
			if($cards) {
				$cards = unserialize($cards);
			}
			if (!$cards) {
				$cards = array();
			}

			$website = Mage::app()->getStore($quote->getStoreId())->getWebsite();
			foreach ($cards as $k=>&$card) {
				$model = Mage::getModel('amgiftcard/account')->load($card['i']);

				if (!$model->isValidBool($website)) {
					unset($cards[$k]);
				} else if ($model->getCurrentValue() != $card['ba']) {
					$card['ba'] = $model->getCurrentValue();
				} else {
					$card['a'] = $quote->getStore()->roundPrice($quote->getStore()->convertPrice($card['ba']));
					$baseAmount += $card['ba'];
					$amount += $card['a'];
				}
			}
			$quote->setAmGiftCards(serialize($cards));

			$quote->setAmBaseGiftCardsAmount($baseAmount);
			$quote->setAmGiftCardsAmount($amount);

			$quote->setAmGiftCardsTotalCollected(true);
		}
	}
}