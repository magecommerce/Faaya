<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_CartController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if (!Mage::getStoreConfigFlag('amgiftcard/general/active')) {
			$this->norouteAction();
			return;
		}
	}

	/**
	 * check gift card balance
	 */
	public function ajaxAction()
	{
		$code = $this->getRequest()->getParam('code', '');
		$codeAccount = Mage::getModel('amgiftcard/account')->loadByCode($code);
		Mage::register('amgiftcard_code_account', $codeAccount);
		$this->loadLayout();
		$this->renderLayout();
		//$this->lo
		/*if($codeAccount->getId())
		{
			$data = array(
				'status' => $codeAccount->getStatus(),
				'value' => Mage::helper('core')->currency($codeAccount->getCurrentValue(),true,false),
				'expired_date' => date('M j, Y', strtotime($codeAccount->getExpiredDate())),
			);
		} else {
			$data = array(
				'error' => Mage::helper('amgiftcard')->__('Gift Code does not exist'),
			);
		}

		echo json_encode($data);
		*/
	}

	public function addAction()
	{
		$data = $this->getRequest()->getPost();
		if (isset($data['am_giftcard_code'])) {
			$code = trim($data['am_giftcard_code']);
			try {
				Mage::getModel('amgiftcard/account')
					->loadByCode($code)
					->addToCart();
				Mage::getSingleton('checkout/session')->addSuccess(
					$this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($code))
				);
			} catch (Mage_Core_Exception $e) {
				Mage::getSingleton('checkout/session')->addError(
					$e->getMessage()
				);
			} catch (Exception $e) {
				Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot apply gift card.').$e->getMessage());
			}
		}
		$this->_redirect('checkout/cart');
	}

	public function removeAction()
	{
		$data = $this->getRequest()->getParams();
		if (isset($data['am_giftcard_code'])) {
			$code = $data['am_giftcard_code'];
			try {
				Mage::getModel('amgiftcard/account')
					->loadByCode($code)
					->removeFromCart();
				Mage::getSingleton('checkout/session')->addSuccess(
					$this->__('Gift Card "%s" was removed.', Mage::helper('core')->escapeHtml($code))
				);
			} catch (Mage_Core_Exception $e) {
				Mage::getSingleton('checkout/session')->addError(
					$e->getMessage()
				);
			} catch (Exception $e) {
				Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot remove gift card.'));
			}
		}

		$place = $this->getRequest()->getParam('place', 'cart');
		if($place == 'onepage') {
			$this->_redirect('checkout/onepage');
		} else {
			$this->_redirect('checkout/cart');
		}
	}
}
