<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Total_Invoice_GiftCard extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	/**
	 * @param Mage_Sales_Model_Order_Invoice $invoice
	 * @return $this
	 */
	public function collect(Mage_Sales_Model_Order_Invoice $invoice)
	{
		$order = $invoice->getOrder();
		if ($order->getAmBaseGiftCardsAmount() && $order->getAmBaseGiftCardsInvoiced() != $order->getAmBaseGiftCardsAmount()) {
			$baseAmountLeft = $order->getAmBaseGiftCardsAmount() - $order->getAmBaseGiftCardsInvoiced();
			if ($baseAmountLeft >= $invoice->getBaseGrandTotal()) {
				$baseUsed = $invoice->getBaseGrandTotal();
				$used = $invoice->getGrandTotal();
				$invoice->setBaseGrandTotal(0);
				$invoice->setGrandTotal(0);
			} else {
				$baseUsed = $order->getAmBaseGiftCardsAmount() - $order->getAmBaseGiftCardsInvoiced();
				$used = $order->getAmGiftCardsAmount() - $order->getAmGiftCardsInvoiced();

				$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$baseUsed);
				$invoice->setGrandTotal($invoice->getGrandTotal()-$used);
			}

			$invoice->setAmBaseGiftCardsAmount($baseUsed);
			$invoice->setAmGiftCardsAmount($used);
		}
		return $this;
	}
}