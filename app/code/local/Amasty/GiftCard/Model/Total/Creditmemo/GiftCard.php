<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Total_Creditmemo_GiftCard extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	/**
	 * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
	 * @return $this
	 */
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
	{
		$order = $creditmemo->getOrder();
		if ($order->getAmBaseGiftCardsAmount() && $order->getAmBaseGiftCardsInvoiced() != 0) {
			$baseAmountLeft = $order->getAmBaseGiftCardsInvoiced() - $order->getAmBaseGiftCardsRefunded();

			if ($baseAmountLeft >= $creditmemo->getBaseGrandTotal()) {
				$baseUsed = $creditmemo->getBaseGrandTotal();
				$used = $creditmemo->getGrandTotal();

				$creditmemo->setBaseGrandTotal(0);
				$creditmemo->setGrandTotal(0);

				$creditmemo->setAllowZeroGrandTotal(true);
			} else {
				$baseUsed = $order->getAmBaseGiftCardsInvoiced() - $order->getAmBaseGiftCardsRefunded();
				$used = $order->getAmGiftCardsInvoiced() - $order->getAmGiftCardsRefunded();

				$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()-$baseUsed);
				$creditmemo->setGrandTotal($creditmemo->getGrandTotal()-$used);
			}

			$creditmemo->setAmBaseGiftCardsAmount($baseUsed);
			$creditmemo->setAmGiftCardsAmount($used);
		}
		/*
		$creditmemo->setBaseCustomerBalanceReturnMax($creditmemo->getBaseCustomerBalanceReturnMax() + $creditmemo->getAmBaseGiftCardsAmount());

		$creditmemo->setCustomerBalanceReturnMax($creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getGiftCardsAmount());
		$creditmemo->setCustomerBalanceReturnMax($creditmemo->getCustomerBalanceReturnMax() + $creditmemo->getGrandTotal());
		*/
		return $this;
	}
}