<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_AccController extends Mage_Core_Controller_Front_Action
{
	private $_customerId = 0;

	public function preDispatch()
	{
		parent::preDispatch();
		if (!Mage::getStoreConfigFlag('amgiftcard/general/active')) {
			$this->norouteAction();
			return;
		}

		$session = Mage::getSingleton('customer/session');
		if (!$session->authenticate($this)) {
			$this->setFlag('', 'no-dispatch', true);
		}

		$this->_customerId = $session->getCustomer()->getId();
	}

	/**
	 * Highlight menu and render layout
	 */
	private function _renderLayoutWithMenu()
	{
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
		if ($navigationBlock) {
			$navigationBlock->setActive('amgiftcard/acc');
		}
		$this->renderLayout();
	}

	public function indexAction()
	{
		$listCards = Mage::getModel('amgiftcard/account')->getCollection()->savedByCustomer($this->_customerId);
		Mage::register('customer_am_gift_cards', $listCards);
		$this->_renderLayoutWithMenu();
	}

	public function addAction()
	{
		if (!$this->_validateFormKey()) {
			$this->_redirect('*/*/');
			return ;
		}
		$code = trim($this->getRequest()->getParam('am_giftcard_code'));
		$account = Mage::getModel('amgiftcard/account')->loadByCode($code);
		if($account->getId()) {
			try {
				$model = Mage::getModel('amgiftcard/customerCard')->load(array('account_id'=> $account->getId(), 'customer_id'=>$this->_customerId));
				if($model->getId()) {
					Mage::throwException(
						Mage::helper('amgiftcard')->__('This Gift Code already exists')
					);
				}
				$model->setAccountId($account->getId())->setCustomerId($this->_customerId)->save();
				Mage::getSingleton('customer/session')->addSuccess(Mage::helper('amgiftcard')->__('Gift Card has been successfully added'));
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('customer/session')->addError(
				Mage::helper('amgiftcard')->__('Wrong Gift Card code')
			);
		}

		$this->_redirect('*/*/');
		return;
	}

	public function removeAction()
	{
		$id     = (int)$this->getRequest()->getParam('id');
		$model   = Mage::getModel('amgiftcard/customerCard')->load(array('account_id'=> $id, 'customer_id'=>$this->_customerId));

		if ($model->getCustomerId() == $this->_customerId){
			try {
				$model->delete();
				Mage::getSingleton('customer/session')->addSuccess($this->__('Gift Card has been successfully removed'));
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}
}