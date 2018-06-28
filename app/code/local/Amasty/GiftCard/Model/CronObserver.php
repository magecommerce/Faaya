<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_CronObserver extends Mage_Core_Model_Abstract
{

    public function notifyExpiredCards()
    {
        if (!Mage::getStoreConfig('amgiftcard/card/notify_expires_date')) {
            return $this;
        }
        $days = Mage::getStoreConfig('amgiftcard/card/notify_expires_date_days');

        $date = Mage::getModel('core/date')->gmtDate('Y-m-d', "+{$days} days");
        $dateExpired = array(
            'from' => $date . "00:00:00",
            'to' => $date . "23:59:59",
        );
        $collection = Mage::getModel('amgiftcard/account')->getCollection()
            ->addFieldToFilter('expired_date', $dateExpired)
            ->addFieldToFilter('status_id', array('nin' =>
                array(
                    Amasty_GiftCard_Model_Account::STATUS_EXPIRED,
                    Amasty_GiftCard_Model_Account::STATUS_USED
                )
            ));
        $collection->walk('sendExpiryNotification');

        return $this;
    }

    public function sendCards()
    {
        //$currentDate = Mage::getModel('core/date')->date('Y-m-d');
        $currentDate = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $date = Mage::app()->getLocale()->utcDate(null, $currentDate)->toString('y-M-d H:m:s');
        $date = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
        $collection = Mage::getModel('amgiftcard/account')
            ->getCollection()
            ->addFieldToFilter('date_delivery', array('lteq' => $date))
            ->addFieldToFilter('is_sent', 0);

        $collection->walk('sendDataToMail');

        return $this;
    }

    public function expireAccounts()
    {
        $date = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
        $collection = Mage::getModel('amgiftcard/account')
            ->getCollection()
            ->addFieldToFilter('expired_date', array('lteq' => $date))
            ->addFieldToFilter('status_id', Amasty_GiftCard_Model_Account::STATUS_ACTIVE);
        $collection->walk('setStatusId', array(Amasty_GiftCard_Model_Account::STATUS_EXPIRED));
        $collection->save();

        return $this;
    }
}