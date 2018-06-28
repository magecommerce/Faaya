<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Sales_Items_Column_Name_Giftcard
    extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{

	public function getOrderOptions()
	{
		return array_merge($this->_getGiftcardOptions(), parent::getOrderOptions());
	}

    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getItem()->getProductOptionByCode($code)) {
            return $this->escapeHtml($option);
        }
        return false;
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        $result = array();


		$value = $this->_prepareCustomOption('am_giftcard_amount');

		if ($value) {
			$result[] = array(
				'label' => $this->__('Card Value'),
				'value' => Mage::helper('core')->currency($value,true,false)
			);
		}

		$value = $this->_prepareCustomOption('am_giftcard_type');
		$giftcardType = $value;

		if ($value) {
			$result[] = array(
				'label' => $this->__('Card Type'),
				'value' => Mage::helper('amgiftcard')->getCardType($value)
			);
		}

		$value = $this->_prepareCustomOption('am_giftcard_image');
		if ($value) {
			$image = Mage::getModel('amgiftcard/image')->load($value);
			if ($image->getId()) {
				$value = '<img src="'.$image->getThumbUrl().'" title="'.$this->__('Image Id %d', $image->getId()).'"/>';
				$result[] = array(
					'label' => $this->__('Gift Card Image'),
					'value' => $value,
					'custom_view'=> true,
				);
			}

		}

		$value = $this->_prepareCustomOption('am_giftcard_sender_name');
		if ($value) {
			$email = $this->_prepareCustomOption('am_giftcard_sender_email');
			if ($email) {
				$value = "{$value} &lt;{$email}&gt;";
			}
			$result[] = array(
				'label' => $this->__('Gift Card Sender'),
				'value' => $value
			);
		}

		$value = $this->_prepareCustomOption('am_giftcard_recipient_name');
		if ($value && $giftcardType != Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED) {
			$email = $this->_prepareCustomOption('am_giftcard_recipient_email');
			if ($email) {
				$value = "{$value} &lt;{$email}&gt;";
			}
			$result[] = array(
				'label' => $this->__('Gift Card Recipient'),
				'value' => $value
			);
		}

		$value = $this->_prepareCustomOption('am_giftcard_message');
		if ($value) {
			$result[] = array(
				'label' => $this->__('Gift Card Message'),
				'value' => $value
			);
		}

        if ($value = $this->_prepareCustomOption('am_giftcard_lifetime')) {
            $result[] = array(
                'label'=>$this->__('Gift Card Lifetime'),
                'value'=>sprintf('%s days', $value),
            );
        }

		if ($value = $this->_prepareCustomOption('am_giftcard_date_delivery')) {
			$result[] = array(
				'label'=>$this->__('Date of certificate delivery'),
				'value'=>$this->formatDate($value, 'short', true),
			);
		}


        $createdCodes = 0;
        $totalCodes = $this->getItem()->getQtyOrdered();
        if ($codes = $this->getItem()->getProductOptionByCode('am_giftcard_created_codes')) {
            $createdCodes = count($codes);
        }

        if (is_array($codes)) {
            foreach ($codes as &$code) {
                if ($code === null) {
                    $code = $this->__('Unable to create.');
                }
            }
        } else {
            $codes = array();
        }

        for ($i = $createdCodes; $i < $totalCodes; $i++) {
            $codes[] = $this->__('N/A');
        }

        $result[] = array(
            'label'=>$this->__('Gift Card Accounts'),
            'value'=>implode('<br />', $codes),
            'custom_view'=>true,
        );



        return $result;
    }


}
