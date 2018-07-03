<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Helper_Catalog_Product_Configuration extends Mage_Core_Helper_Abstract
    implements Mage_Catalog_Helper_Product_Configuration_Interface
{
    /**
     * @param string $code
     * @return mixed
     */
    public function prepareCustomOption(Mage_Catalog_Model_Product_Configuration_Item_Interface $item, $code)
    {
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            if ($value) {
                return $this->escapeHtml($value);
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getGiftcardOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $result = array();

		//$value = $this->prepareCustomOption($item, '');
		$value = $this->prepareCustomOption($item, 'am_giftcard_amount');

		if ($value) {
			$result[] = array(
				'label' => $this->__('Card Value'),
				'value' => Mage::helper('core')->currency($value,true,false)
			);
		}

		$value = $this->prepareCustomOption($item, 'am_giftcard_type');
		$giftcardType = $value;

		if ($value) {
			$result[] = array(
				'label' => $this->__('Card Type'),
				'value' => Mage::helper('amgiftcard')->getCardType($value)
			);
		}

        $value = $this->prepareCustomOption($item, 'am_giftcard_sender_name');
        if ($value) {
            $email = $this->prepareCustomOption($item, 'am_giftcard_sender_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Sender'),
                'value' => $value
            );
        }

        $value = $this->prepareCustomOption($item, 'am_giftcard_recipient_name');
        if ($value && $giftcardType != Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED) {
            $email = $this->prepareCustomOption($item, 'am_giftcard_recipient_email');
            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label' => $this->__('Gift Card Recipient'),
                'value' => $value
            );
        }

        $value = $this->prepareCustomOption($item, 'am_giftcard_message');
        if ($value) {
            $result[] = array(
                'label' => $this->__('Gift Card Message'),
                'value' => $value
            );
        }

        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        return array_merge(
            $this->getGiftcardOptions($item),
            Mage::helper('catalog/product_configuration')->getCustomOptions($item)
        );
    }
}
