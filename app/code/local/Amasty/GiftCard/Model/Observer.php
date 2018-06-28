<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Event sales_convert_quote_item_to_order_item
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function appendGiftcardAdditionalData(Varien_Event_Observer $observer)
    {
        /* @var $orderItem Mage_Sales_Model_Order_Item */
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $keys = array(
            'am_giftcard_amount',
            'am_giftcard_image',
            'am_giftcard_type',
            'am_giftcard_sender_name',
            'am_giftcard_sender_email',
            'am_giftcard_recipient_name',
            'am_giftcard_recipient_email',
            'am_giftcard_date_delivery',
            'am_giftcard_message',
        );
        $productOptions = $orderItem->getProductOptions();
        foreach ($keys as $key) {
            if ($option = $quoteItem->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }

        $product = $quoteItem->getProduct();

        $productOptions['am_giftcard_lifetime'] = Mage::helper('amgiftcard')->getValueOrConfig(
            $product->getAmGiftcardLifetime(),
            Amasty_GiftCard_Model_GiftCard::XML_PATH_LIFETIME,
            $orderItem->getStore()
        );

        $productOptions['am_giftcard_email_template'] = Mage::helper('amgiftcard')->getValueOrConfig(
            $product->getAmEmailTemplate(),
            Amasty_GiftCard_Model_GiftCard::XML_PATH_EMAIL_TEMPLATE,
            $orderItem->getStore()
        );


        $orderItem->setProductOptions($productOptions);

        return $this;
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function generateGiftCardAccounts(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $loadedInvoices = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() != Amasty_GiftCard_Model_Catalog_Product_Type_GiftCard::TYPE_GIFTCARD_PRODUCT) {
                continue;
            }

            $qty = 0;
            $options = $item->getProductOptions();

            $paidInvoiceItems = (isset($options['am_giftcard_paid_invoice_items'])
                ? $options['am_giftcard_paid_invoice_items']
                : array());

            $invoiceItemCollection = Mage::getResourceModel('sales/order_invoice_item_collection')
                ->addFieldToFilter('order_item_id', $item->getId());
            $registryPaidInvoiceItems = Mage::registry('am_giftcard_paid_invoice_items');
            $registryPaidInvoiceItems = is_array($registryPaidInvoiceItems) ? $registryPaidInvoiceItems : array();
            foreach ($invoiceItemCollection as $invoiceItem) {
                $invoiceId = $invoiceItem->getParentId();
                if (isset($loadedInvoices[$invoiceId])) {
                    $invoice = $loadedInvoices[$invoiceId];
                } else {
                    $invoice = Mage::getModel('sales/order_invoice')
                        ->load($invoiceId);
                    $loadedInvoices[$invoiceId] = $invoice;
                }

                if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID &&
                    !in_array($invoiceItem->getId(), $paidInvoiceItems) &&
                    !in_array($invoiceItem->getId(), $registryPaidInvoiceItems)
                ) {
                    $qty += $invoiceItem->getQty();
                    $paidInvoiceItems[] = $invoiceItem->getId();
                }
            }
            $options['am_giftcard_paid_invoice_items'] = $paidInvoiceItems;
            Mage::register('am_giftcard_paid_invoice_items', $paidInvoiceItems, true);

            $isError = false;

            if ($qty > 0) {
                $amount = (isset($options['am_giftcard_amount'])) ? $options['am_giftcard_amount'] : 0;
                //$lifetime = (isset($options['am_giftcard_lifetime'])) ? $options['am_giftcard_lifetime'] : 0;
                $lifetime = Mage::helper('amgiftcard')->getValueOrConfig(
                    $item->getProduct()->getAmGiftcardLifetime(),
                    'amgiftcard/card/lifetime',
                    Mage::app()->getStore($order->getStoreId())
                );
                $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

                $data = new Varien_Object();
                $data->setWebsiteId($websiteId)
                    ->setAmount($amount)
                    ->setOrder($order)
                    ->setLifetime($lifetime)
                    ->setProductOptions($options)
                    ->setOrderItem($item);
                $codes = (isset($options['am_giftcard_created_codes']) ? $options['am_giftcard_created_codes'] : array());
                for ($i = 0; $i < $qty; $i++) {
                    try {
                        $account = Mage::getModel('amgiftcard/account')->createAccount($data);
                        $codes[] = $account->getCode();
                    } catch (Mage_Core_Exception $e) {
                        Mage::log($e->getMessage(), null, 'amgiftcard.txt');
                        $isError = true;
                        $codes[] = null;
                    }
                }
                $options['am_giftcard_created_codes'] = $codes;


                $item->setProductOptions($options);
                $item->save();
            }

            if ($isError) {
                $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/amgiftcard/codes');
                $message = Mage::helper('amgiftcard')->__('You\'ve run out of the Gift Card codes. The gift code wasn\'t created. Please make a credit memo and <a href="%s" target="_blank">add more gift codes</a> for the appropriate code set.', $url);

                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }

        return $this;
    }

    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setAmGiftCardsTotalCollected(false);
    }

    public function quoteMergeAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getAmGiftCards()) {
            $sourceCards = unserialize($source->getAmGiftCards());
            $finalGiftCards = $sourceCards;
            if ($quote->getAmGiftCards()) {
                $quoteCards = unserialize($quote->getAmGiftCards());
                $finalGiftCards += $quoteCards;
            }
            $quote->setAmGiftCards(serialize($finalGiftCards));
        }
    }

    public function increaseOrderGiftCardInvoicedAmount(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getAmBaseGiftCardsAmount()) {
            $order->setAmBaseGiftCardsInvoiced($order->getAmBaseGiftCardsInvoiced() + $invoice->getAmBaseGiftCardsAmount());
            $order->setAmGiftCardsInvoiced($order->getAmGiftCardsInvoiced() + $invoice->getAmGiftCardsAmount());
        }

        return $this;
    }


    public function dischargeCard(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderCards = $this->_getCardsByOrder($order);

        if (count($orderCards) > 0) {
            $canonicalCards = array();
            foreach ($orderCards as $card) {
                $canonicalCards[$card['i']] = $card;
            }

            $cardsCollection = Mage::getModel('amgiftcard/account')->getCollection()->addFieldToFilter('account_id', array('in' => array_keys($canonicalCards)));
            $website = Mage::app()->getStore($order->getStoreId())->getWebsite();
            $cards = array();
            foreach ($cardsCollection as $card) {
                if ($card->isValid($website) && isset($canonicalCards[$card->getId()])) {
                    $orderCard = $canonicalCards[$card->getId()];
                    $card->discharge($orderCard['ba'])->save();
                    $cards[$card->getId()] = $canonicalCards[$card->getId()];

                    $cards[$card->getId()]['authorized'] = $cards[$card->getId()]['ba'];
                }
            }
            $order->setAmGiftCards(serialize($cards));
        }

        return $this;
    }

    public function addCardLog(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderCards = $this->_getCardsByOrder($order);

        if (count($orderCards) > 0) {
            $canonicalCards = array();
            foreach ($orderCards as $card) {
                $canonicalCards[$card['i']] = $card;
            }

            $cardsCollection = Mage::getModel('amgiftcard/account')->getCollection()->addFieldToFilter('account_id', array('in' => array_keys($canonicalCards)));
            foreach ($cardsCollection as $card) {
                $card->addOrder($order);
            }
        }

    }

    public function revertGiftCardAccountBalance(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $this->_revertGiftCardsForOrder($order);
        }

        return $this;
    }

    public function revertGiftCardsForAllOrders(Varien_Event_Observer $observer)
    {
        $orders = $observer->getEvent()->getOrders();

        foreach ($orders as $order) {
            $this->_revertGiftCardsForOrder($order);
        }

        return $this;
    }


    public function salesOrderLoadAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED) {
            return $this;
        }

        if ($order->getAmGiftCardsInvoiced() - $order->getAmGiftCardsRefunded() >= 0.0001) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }


    public function creditmemoDataImport(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $request->getParam('creditmemo');

        if (isset($input['refund_amgiftcard_return']) && $input['refund_amgiftcard_return']) {
            $creditmemo->setRefundAmGiftCardsAmount($input['refund_amgiftcard_return']);
        }

        return $this;
    }


    public function refund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        if ($creditmemo->getAmBaseGiftCardsAmount()) {
            $baseAmount = $creditmemo->getAmBaseGiftCardsAmount();
            $amount = $creditmemo->getAmGiftCardsAmount();

            $orderCards = $order->getAmGiftCards();
            if ($orderCards) {
                $orderCards = unserialize($orderCards);
            }
            $orderCards = $orderCards ? $orderCards : array();

            $baseAmountLeft = $baseAmount;
            foreach ($orderCards as $orderCard) {
                if ($baseAmountLeft == 0) {
                    break;
                }
                $returnBaseAmount = min($baseAmountLeft, $orderCard['ba']);

                $card = Mage::getModel('amgiftcard/account')->load($orderCard['i']);
                if (!$card->getId()) {
                    continue;
                }
                $card->charge($returnBaseAmount)->save();
                $baseAmountLeft -= $returnBaseAmount;
            }

            /*if ($creditmemo->getRefundAmGiftCardsAmount()) {


            }*/

            $order->setAmBaseGiftCardsRefunded(
                $order->getAmBaseGiftCardsRefunded() + $creditmemo->getAmBaseGiftCardsAmount()
            );
            $order->setAmGiftCardsRefunded($order->getAmGiftCardsRefunded() + $creditmemo->getAmGiftCardsAmount());

            if ($order->getAmGiftCardsInvoiced() > 0 &&
                $order->getAmGiftCardsInvoiced() == $order->getAmGiftCardsRefunded()
            ) {
                $order->setForcedCanCreditmemo(false);
            }
        }

        return $this;
    }


    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getPayment()->getQuote();
        if (!$quote || !$quote->getCustomerId()) {
            return $this;
        }

        $cards = $this->_getCardsByOrder($quote);
        $website = Mage::app()->getStore($quote->getStoreId())->getWebsite();
        foreach ($cards as $card) {
            Mage::getModel('amgiftcard/account')
                ->load($card['i'])
                ->isValid($website);
        }

        if ((float)$quote->getAmBaseGiftCardsAmountUsed()) {
            $quote->setAmGiftCardAccountApplied(true);
            $input = $observer->getEvent()->getInput();
            if (!$input->getMethod()) {
                $input->setMethod('free');
            }
        }

        return $this;
    }


    public function togglePaymentMethods($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        if ($quote->getBaseGrandTotal() == 0 && (float)$quote->getAmGiftCardsAmountUsed()) {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = $paymentMethod === 'free' && empty($result->isDeniedInConfig);
        }
    }


    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = array(
            'am_giftcard_price_percent',
            'am_giftcard_price_type',
            'am_giftcard_type',
        );

        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);

        return $this;
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('amgiftcard')->isModuleActive()) {
            $collection = $observer->getCollection();
            $resource = $collection->getResource();
            $entityTable = null;
            if (is_a($resource, 'Mage_Eav_Model_Entity_Abstract')
                || is_a($resource, 'Mage_Eav_Model_Entity_Type')
            ) {
                $entityTable = $resource->getEntityTable();
            }
            if ($entityTable
                && is_a($collection, 'Mage_Catalog_Model_Resource_Product_Collection')
                && strpos('catalog_product_entity', $entityTable) !== false
            ) {
                $collection->addFieldToFilter('type_id', array('neq' => 'amgiftcard'));
            }
        }
    }

    public function addPaypalGiftCardItem(Varien_Event_Observer $observer)
    {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            $value = abs($salesEntity->getAmBaseGiftCardsAmount());
            if ($value > 0.0001) {
                $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, $value,
                    Mage::helper('amgiftcard')->__('Gift Card (%s)', Mage::app()->getStore()->convertPrice($value, true, false))
                );
            }
        }
    }


    protected function _revertGiftCardsForOrder(Mage_Sales_Model_Order $order)
    {
        $orderCards = $this->_getCardsByOrder($order);
        $listRevertCards = array();
        foreach ($orderCards as $card) {
            if (isset($card['authorized'])) {
                $listRevertCards[$card['i']] = $card['authorized'];
            }
        }

        if (count($listRevertCards) > 0) {
            $cardsCollection = Mage::getModel('amgiftcard/account')->getCollection()->addFieldToFilter('account_id', array('in' => array_keys($listRevertCards)));
            foreach ($cardsCollection as $card) {
                $card->charge($listRevertCards[$card->getId()])->save();
            }
        }

        return $this;
    }

    protected function _getCardsByOrder($order)
    {
        $orderCards = $order->getAmGiftCards();
        if ($orderCards) {
            $orderCards = unserialize($orderCards);
        }
        $orderCards = $orderCards ? $orderCards : array();

        return $orderCards;
    }


    public function processOrderCreationData(Varien_Event_Observer $observer)
    {
        $model = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();
        if (isset($request['amgiftcard_add'])) {
            $code = $request['amgiftcard_add'];
            try {
                Mage::getModel('amgiftcard/account')
                    ->loadByCode($code)
                    ->addToCart($quote);
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addException(
                    $e->getMessage(), Mage::helper('amgiftcard')->__('Cannot apply Gift Card')
                );
            }
        }

        if (isset($request['amgiftcard_remove'])) {
            $code = $request['amgiftcard_remove'];

            try {
                Mage::getModel('amgiftcard/account')
                    ->loadByCode($code)
                    ->removeFromCart($quote);
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session_quote')->addException(
                    $e->getMessage(), Mage::helper('amgiftcard')->__('Cannot remove Gift Card')
                );
            }
        }

        return $this;
    }

}
