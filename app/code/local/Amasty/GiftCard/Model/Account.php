<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Account extends Amasty_GiftCard_Model_Abstract
{
	const STATUS_INACTIVE	= 0;
	const STATUS_ACTIVE		= 1;
	const STATUS_EXPIRED	= 2;
	const STATUS_USED		= 3;

	const FONT_FILE_ARIAL	= 'amasty_giftcard/arial_bold.ttf';

	protected $imagePath 	= 'amasty_giftcard/generated_images_cache';

	protected function _construct()
	{
		$this->_init('amgiftcard/account');
	}

	public function addOrder($order)
	{
		if(Mage::getModel('amgiftcard/accountOrder')->loadByOrder($order->getId(), $this->getId())->getId()) {
			return $this;
		}
		Mage::getModel('amgiftcard/accountOrder')->setOrderId($order->getId())->setAccountId($this->getId())->save();

		return $this;
	}

	public function removeOrder($order)
	{
		Mage::getModel('amgiftcard/accountOrder')->loadByOrder(
			$order->getId(), $this->getId()
		)->delete();
		return $this;
	}


	/**
	 * @param float $amount
	 *
	 * @return $this
	 */
	public function charge($amount)
	{
		$this->setCurrentValue($this->getCurrentValue()+$amount);
		return $this;
	}

	/**
	 * @param float $amount
	 *
	 * @return $this
	 */
	public function discharge($amount)
	{
		$currentValue = $this->getCurrentValue();
		if($currentValue < $amount) {
			Mage::throwException(
				Mage::helper('amgiftcard')->__('Gift card account %s balance is less than amount to be charged.', $this->getCode())
			);
		}
		$this->setCurrentValue($currentValue-$amount);
		return $this;
	}



	public function getStatusId()
	{
		$this->_updateStatus();
		return parent::getStatusId();
	}

	public function addToCart($quote = null)
	{
		if(is_null($quote)) {
			$quote = $this->_getCheckoutSession()->getQuote();
		}
		$website = Mage::app()->getStore($quote->getStoreId())->getWebsite();
		$customerId = $this->_getCustomerId();
		$allowThemselves = Mage::getStoreConfig('amgiftcard/card/allow_use_themselves');
		$buyerId = $this->getBuyerId();
		if ($this->isValid($website)) {
			$cards = $quote->getAmGiftCards();
			if($cards) {
				$cards = unserialize($cards);
			}
			if (!$cards) {
				$cards = array();
			} else {
				foreach ($cards as $card) {
					if ($card['i'] == $this->getId()) {
						Mage::throwException(Mage::helper('amgiftcard')->__('This gift card account is already in the quote.'));
					}
				}
			}
			if(!$allowThemselves && $buyerId && $customerId && $customerId == $buyerId) {
				Mage::throwException(Mage::helper('amgiftcard')->__('Please be aware that it is not possible to use the gift card you purchased for your own orders.'));
			}
			$cards[$this->getId()] = array(
				'i' => $this->getId(),
				'c' => $this->getCode(),
				'a' => $this->getCurrentValue(),
				'ba' => $this->getCurrentValue(),
			);
			$quote->setAmGiftCards(serialize($cards));
			$quote->save();
		}

		return $this;
	}

	public function removeFromCart($quote = null)
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		if (!$this->getId()) {
			Mage::throwException($_helper->__('Wrong gift card website'));
		}
		if(is_null($quote)) {
			$quote = $this->_getCheckoutSession()->getQuote();
		}
		$cards = $quote->getAmGiftCards();
		if ($cards) {
			$cards = unserialize($cards);
			unset($cards[$this->getId()]);
			$quote->setAmGiftCards(serialize($cards));
			$quote->collectTotals()->save();
			return $this;
		}

		Mage::throwException($_helper->__('This gift card not found in the quote.'));
	}


	public function isValid($website = null)
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');
		if(!$this->getId()){
			Mage::throwException($_helper->__('Wrong gift card code'));
		}

		$website = Mage::app()->getWebsite($website)->getId();
		if ($this->getWebsiteId() != $website) {
			Mage::throwException($_helper->__('Wrong gift card website'));
		}

		if ($this->getStatusId() != self::STATUS_ACTIVE) {
			Mage::throwException($_helper->__('Gift card %s is not enabled.', $this->getCode()));
		}

		if($this->isExpired()) {
			Mage::throwException($_helper->__('Gift card %s is expired.', $this->getCode()));
		}

		if ($this->getCurrentValue() <= 0) {
			Mage::throwException($_helper->__('Gift card %s  balance does not have funds.', $this->getCode()));
		}

		return true;
	}

	/**
	 * @param null $website
	 *
	 * @return bool
	 */
	public function isValidBool($website = null)
	{
		$isValid = true;
		try {
			$this->isValid($website);
		} catch (Exception $e) {
			$isValid = false;
		}

		return $isValid;
	}


	public function isExpired()
	{
		if (!$this->getExpiredDate()) {
			return false;
		}
		$currentDate = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
		if (strtotime($this->getExpiredDate()) < strtotime($currentDate)) {
			return true;
		}
		return false;
	}


	/**
	 * @return array
	 */
	public function getListStatuses()
	{
		/* @var $_helper Amasty_GiftCard_Helper_Data */
		$_helper = Mage::helper('amgiftcard');

		return array(
			self::STATUS_INACTIVE 	=> $_helper->__('Inactive'),
			self::STATUS_ACTIVE 	=> $_helper->__('Active'),
			self::STATUS_EXPIRED 	=> $_helper->__('Expired'),
			self::STATUS_USED		=> $_helper->__('Used'),
		);
	}

	/**
	 * @param int|null $statusId
	 *
	 * @return string
	 */
	public function getStatus($statusId = null)
	{
		if(is_null($statusId)) {
			$statusId = $this->getStatusId();
		}
		$listStatuses = $this->getListStatuses();

		return isset($listStatuses[$statusId]) ? $listStatuses[$statusId] : '';
	}

	public function getCodeModel()
	{
		if(!$codeModel = $this->getData('codeModel')) {
			$codeModel = Mage::getModel('amgiftcard/code')->load($this->getCodeId());
			$this->setData('codeModel',$codeModel);
		}

		return $codeModel;
	}

	public function getCode()
	{
		if(!$code = $this->getData('code')) {
			$code = $this->getCodeModel()->getCode();
			$this->setData('code',$code);
		}

		return $code;
	}

	/**
	 * @return Amasty_GiftCard_Model_Image
	 */
	public function getImage()
	{
		if(!$image = $this->getData('image')) {
			$image = Mage::getModel('amgiftcard/image')->load($this->getImageId());
			$this->setData('image', $image);
		}

		return $image;
	}

	public function getOrder()
	{
		if(!$order = $this->getData('order')) {
			$order = Mage::getModel('sales/order')->load($this->getOrderId());
			$this->setData('order', $order);
		}

		return $order;
	}

	public function getOrderNumber()
	{
		if(!$orderNumber = $this->getData('order_number')) {
			$orderNumber = $this->getOrder()->getIncrementId();
			$this->setData('order_number', $orderNumber);
		}

		return $orderNumber;
	}

	public function getProduct()
	{
		if(!$product = $this->getData('product')) {
			$product = Mage::getModel('catalog/product')->load($this->getProductId());
			$this->setData('product', $product);
		}

		return $product;
	}

	public function getBuyerId()
	{
		return $this->getOrder()->getCustomerId();
	}

	public function generateCode($codeSetId = null)
	{
		if(is_null($codeSetId)) {
			$codeSetId = $this->getCodeSetId();
		}
		$code = Mage::getModel('amgiftcard/code')->loadFreeCode($codeSetId);
		if(!$code->getId()) {
			Mage::throwException(
				Mage::helper('amgiftcard')->__('No free codes')
			);
		}

		$this->setCodeId($code->getId());

		return $code;
	}

	public function createAccount($data)
	{
		$product = $data->getOrderItem()->getProduct();
		$codeSetId = $product->getAmGiftcardCodeSet();
		if(!$codeSetId) {
			$codeSetId = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'am_giftcard_code_set', $data->getOrder()->getStoreId());
		}
		$code = $this->generateCode($codeSetId);

		$productOptions = $data->getProductOptions();

		$this->setData(array(
			'code_id' 			=> $code->getId(),
			'image_id' 			=> isset($productOptions['am_giftcard_image']) ? $productOptions['am_giftcard_image'] : null,
			'buyer_id' 			=> $this->_getCustomerId(),
			'order_id'			=> $data->getOrder()->getId(),
			'website_id'		=> $data->getWebsiteId(),
			'product_id'		=> $product->getId(),
			'status_id'			=> self::STATUS_ACTIVE,
			'initial_value'		=> $data->getAmount(),
			'current_value'		=> $data->getAmount(),
			//'comment'			=> '',
			'sender_name'		=> $productOptions['am_giftcard_sender_name'],
			'sender_email'		=> $productOptions['am_giftcard_sender_email'],
			'recipient_name'	=> $productOptions['am_giftcard_recipient_name'],
			'recipient_email'	=> $productOptions['am_giftcard_recipient_email'],
			'sender_message'	=> isset($productOptions['am_giftcard_message']) ? $productOptions['am_giftcard_message'] : null,
			'date_delivery'		=> $productOptions['am_giftcard_date_delivery'],
			'giftcard_type'		=> $productOptions['am_giftcard_type'],

		));

		if($lifetime = $data->getLifetime()){
			/*$currentDate = Mage::getModel('core/date')->date('Y-m-d H:i:s');
			$currentDate = Mage::app()->getLocale()->utcDate(null, $currentDate)->toString('y-M-d H:m:s');

			$currentDate = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');*/
			$expiredDate = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', "+{$lifetime} days");
			$this->setData('expired_date', $expiredDate);
		}

		$this->save();
		$code->setUsed(1)->save();

		$dateDelivery = $productOptions['am_giftcard_date_delivery'];
		$currentDate = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
		if((!$dateDelivery || strtotime($dateDelivery) <= strtotime($currentDate)) && $this->getGiftcardType() != Amasty_GiftCard_Model_GiftCard::TYPE_PRINTED){
			$this->sendDataToMail();
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function sendDataToMail()
	{
		if(!$this->getData('recipient_email')) {
			return false;
		}
		/**
		 * @var $emailModel Mage_Core_Model_Email_Template
		 */
		$emailModel = Mage::getModel('core/email_template');

		if($this->getProduct())
		{
			$template = $this->getProduct()->getAmEmailTemplate();
		}

		$storeId = $this->getOrder()->getStoreId();
		if($this->getData('store_id')) {
			$storeId = $this->getData('store_id');
		}

		$storeId = Mage::app()->getStore($storeId)->getId();

		if(empty($template)){
			$template = Mage::getStoreConfig('amgiftcard/email/email_template',$storeId);
		}

		$imageGiftCard = null;
		if($this->isImage()) {
			$id = uniqid('am_giftcard');

			$imageGiftCard = "cid:$id";

			if($emailModel->getMail() instanceof Mandrill_Message) {
				$emailModel->getMail()->createAttachment(
					file_get_contents($this->getImageWithCodePath()),
					'IMAGE/PNG',
					Zend_Mime::DISPOSITION_ATTACHMENT,
					Zend_Mime::ENCODING_BASE64,
					$id
				);
			} else {
				$mp = new Zend_Mime_Part(file_get_contents($this->getImageWithCodePath()));
				$mp->encoding = Zend_Mime::ENCODING_BASE64;
				$mp->type = 'IMAGE/PNG';
				$mp->id = $id;
				$emailModel->getMail()->addPart($mp);
				$emailModel->getMail()->setType(Zend_Mime::MULTIPART_RELATED);
			}
		}

		if($emailCC = Mage::getStoreConfig('amgiftcard/email/email_recepient_cc', $storeId)) {
			$emailCC = explode(",", $emailCC);
			array_walk($emailCC, 'trim');
            if (Mage::getStoreConfig('mailchimp/general/active')
                && Mage::helper('ambase')->isModuleEnabled('Ebizmarts_MailChimp')
            ) {
                $emailModel->getMail()->addBcc($emailCC);
            } else {
                $emailModel->getMail()->addCc($emailCC);
            }
		}

		$emailModel
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => $storeId
            ))
            ->sendTransactional(
			$template,
			Mage::getStoreConfig('amgiftcard/email/email_identity',$storeId),
			$this->getData('recipient_email'),
			null,
			array(
				'recipient_name'	=> $this->getData('recipient_name'),
				'sender_name'		=> $this->getData('sender_name'),
				'initial_value'		=> Mage::app()->getStore($storeId)->formatPrice($this->getData('initial_value')),
				'sender_message'	=> $this->getData('sender_message'),
				'gift_code'			=> $this->getCode(),
				//'image'			=> $this->getImageWithCode(),
				'image_base64'		=> $imageGiftCard,
				'expired_date'		=> Mage::helper('core')->formatDate($this->getData('expired_date'), 'long', true),
			),
			$storeId
		);


		if(Mage::getStoreConfig('amgiftcard/email/send_confirmation_to_sender', $storeId) && $this->getData('sender_email')) {
			$emailModelConfirmation = Mage::getModel('core/email_template');
			$emailModelConfirmation
					->setDesignConfig(array(
							'area'  => 'frontend',
							'store' => $storeId
					));
			$emailModelConfirmation->sendTransactional(
					Mage::getStoreConfig('amgiftcard/email/email_template_confirmation_to_sender',$storeId),
					Mage::getStoreConfig('amgiftcard/email/email_identity',$storeId),
					$this->getData('sender_email'),
					null,
					array(
							'recipient_name'	=> $this->getData('recipient_name'),
							'sender_name'		=> $this->getData('sender_name'),
							'initial_value'		=> Mage::app()->getStore($storeId)->formatPrice($this->getData('initial_value')),
							'sender_message'	=> $this->getData('sender_message'),
							'expired_date'		=> Mage::helper('core')->formatDate($this->getData('expired_date'), 'long', true),
							/*'gift_code'			=> $this->getCode(),*/
							/*'image_base64'		=> $imageGiftCard,*/
					),
					$storeId
			);
		}


		if($emailModel->getSentSuccess()) {
			$this->setIsSent(1)->save();
		}

		return $emailModel->getSentSuccess();
	}

	public function sendExpiryNotification()
	{
		if(!$this->getData('recipient_email')) {
			return false;
		}
		$emailModel = Mage::getModel('core/email_template');

        $storeId = $this->getOrder() ? $this->getOrder()->getStoreId() : 0;

		$emailModel
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => $storeId
            ))
            ->sendTransactional(
                Mage::getStoreConfig('amgiftcard/email/email_template_notify'),
                Mage::getStoreConfig('amgiftcard/email/email_identity'),
                $this->getData('recipient_email'),
                null,
                array(
                    'recipient_name'	=> $this->getData('recipient_name'),
                    //'sender_name'		=> $this->getData('sender_name'),
                    //'initial_value'		=> $this->getData('initial_value'),
                    //'sender_message'	=> $this->getData('sender_message'),
                    'gift_code'				=> $this->getCode(),
                    'expiry_days'			=> Mage::getStoreConfig('amgiftcard/card/notify_expires_date_days'),
                    //'image'				=> $this->getImageWithCode(),
                    //'image_base64'		=> ($this->isImage()) ? 'data:image/png;base64,'.base64_encode(file_get_contents($this->getImageWithCodePath())) : null,
                ),
                $storeId
		);
	}

	/**
	 * @return bool
	 */
	public function isImage()
	{
		return (bool) $this->getImageId();
	}

	public function getImageDirPath()
	{
		$imagesGeneratedCachePath = Mage::getBaseDir('media') . DS . $this->imagePath . DS;
		if(!is_dir($imagesGeneratedCachePath)) {
			mkdir($imagesGeneratedCachePath);
		}
		return $imagesGeneratedCachePath;
	}

	public function getImageDirUrl()
	{
		return Mage::getBaseUrl('media') . DS . $this->imagePath . DS;
	}

	public function getImageWithCodeUrl()
	{
		if(!$this->isImage()) {
			return '';
		}
		return $this->getImageDirUrl().$this->getImagePath();
	}

	public function getImageWithCodePath()
	{
		if(!$this->isImage()) {
			return '';
		}
		return $this->getImageDirPath().$this->getImagePath();
	}

	public function getImagePath()
	{
		if(!$this->isImage()) {
			return '';
		}
		$imagePath = $this->getData('image_path');
		if(!$imagePath || !is_file($this->getImageDirPath().$imagePath)) {
			$imagePath = $this->_buildImage();
			$this->setData('image_path', $imagePath);
			$this->save();
		}
		return $imagePath;
	}

	protected function _buildImage()
	{
		if(!$this->isImage()) {
			return null;
		}
		$image = $this->getImage();

		$imageInfo = getimagesize($image->getImagePath());

		$imageResource = null;

		switch($imageInfo['mime']) {
			case 'image/png':
				$imageResource = imagecreatefrompng($image->getImagePath());
				break;
			case 'image/gif':
				$imageResource = imagecreatefromgif($image->getImagePath());
				break;
			case 'image/jpeg':
			default:
				$imageResource = imagecreatefromjpeg($image->getImagePath());
				break;
		}

		$color = imagecolorallocate($imageResource, 0,0,0);		// Black
		$fontFile = Mage::getBaseDir('media').DS.self::FONT_FILE_ARIAL;
//		if(!file_exists($fontFile)){ @todo commented because can potentially break down invoice process
//			Mage::throwException(Mage::helper('amgiftcard')->__('Font not found in path %s', $fontFile));
//		}
		$fontSize = 15;
		//imagestring($imageResource,5,$this->getImage()->getCodePosX(),$this->getImage()->getCodePosY(), $this->getCode(), $color);

		imagettftext($imageResource, $fontSize, 0, (int)$this->getImage()->getCodePosX(), $this->getImage()->getCodePosY()+$fontSize+2,$color, $fontFile, $this->getCode());

		//$imageText = 'data:image/png;base64,';

		$imagePath = uniqid().'_'.preg_replace("/[^A-Za-z0-9_-]/","",$this->getCode());


		switch($imageInfo['mime']) {
			case 'image/png':
				$imagePath .= '.png';
				imagepng($imageResource, $this->getImageDirPath().$imagePath);
				break;
			case 'image/gif':
				$imagePath .= '.gif';
				imagegif($imageResource, $this->getImageDirPath().$imagePath);
				break;
			case 'image/jpeg':
			default:
				$imagePath .= '.jpg';
				imagejpeg($imageResource, $this->getImageDirPath().$imagePath);
				break;
		}
		/*
		$this->setData('image_path', $imagePath);
		$this->save();
		*/
		imagedestroy($imageResource);

		return $imagePath;
	}

	public function loadByCode($code)
	{
		$this->_getResource()->loadByCode($this, $code);

		return $this;
	}


	protected function _getCheckoutSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _updateStatus()
	{
		if($this->getData('status_id') == self::STATUS_INACTIVE) {
			return;
		}

		if($this->getCurrentValue() == 0) {
			$this->setStatusId(self::STATUS_USED);
			return;
		}

		if($this->isExpired()) {
			$this->setStatusId(self::STATUS_EXPIRED);
			return;
		}

		$this->setStatusId(self::STATUS_ACTIVE);
	}

	protected function _beforeSave()
	{
		$this->_updateStatus();
		if ($this->getCurrentValue() < 0) {
			Mage::throwException(
				$this->helper('amgiftcard')->__('Balance cannot be less than zero.')
			);
		}
		return parent::_beforeSave();
	}


	protected function _getCustomerId()
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		} else {
			$customerId = null;
		}

		return $customerId;
	}

}
