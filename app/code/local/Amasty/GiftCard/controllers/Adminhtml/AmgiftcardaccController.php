<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Adminhtml_AmgiftcardaccController extends Mage_Adminhtml_Controller_Action
{

	/**
	 * @param null $id
	 *
	 * @return Amasty_GiftCard_Model_Account
	 */
	protected function _getModel($id = null)
	{
		$model = Mage::getModel('amgiftcard/account');

		return is_null($id) ? $model : $model->load($id);
	}

	public function indexAction()
	{
		$this->loadLayout();
		$this->_title($this->__('Gift Code Accounts'));
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_account')
		);
		$this->renderLayout();
	}

	public function newAction()
	{
		$this->loadLayout();
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_account_new')
		);
		$this->renderLayout();
	}

	public function editAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
		$model = $this->_getModel($id);
        if ($expDate = $model->getExpiredDate()) {
            $model->setExpiredDate(Mage::getModel('core/date')->date('Y-m-d H:i:s', $expDate));
        }
		Mage::register('amgiftcard_account', $model);

		$this->loadLayout();
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_account_edit')
		);
		$this->_addLeft($this->getLayout()->createBlock('amgiftcard/adminhtml_account_edit_tabs'));
		$this->renderLayout();
	}

	public function createAction()
	{
		if($data = $this->getRequest()->getPost())
		{
			$model = $this->_getModel();

			if(!empty($data['expired_date'])) {
				$data['expired_date'] = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', $data['expired_date']);
			}

			$model->addData($data);


			try {
				$model->setInitialValue($model->getValue());
				$model->setCurrentValue($model->getValue());
				$model->setWebsiteId(Mage::app()->getStore($model->getStoreId())->getWebsiteId());
				$imageId = $model->getImageId();
				if(empty($imageId)){
					$model->setImageId(null);
				}

				$code = $model->generateCode();
				// save the data
				$model->save();
				$code->setUsed(1)->save();
				// display success message



				if ($this->getRequest()->getParam('send')) {
					$model->sendDataToMail();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amgiftcard')->__('The email has been sent successfully.'));
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amgiftcard')->__('The code account has been saved.'));


				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('continue')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId(), '_current'=>true));
					return;
				}

				// go to grid
				$this->_redirect('*/*/');
				return;

			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e,
					Mage::helper('amgiftcard')->__('An error occurred while saving the code account.') . $e->getMessage());
			}

			$this->_getSession()->setFormData($data);

			$this->_redirect('*/*/new');
			return;
		}

		//Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quantities have been successfully updated'));
		$this->_redirect('*/*/');
	}

	public function saveAction()
	{
		if($data = $this->getRequest()->getPost())
		{
			$id = (int)$this->getRequest()->getParam('id');
			$model = $this->_getModel($id);

			if(!empty($data['expired_date'])) {
				$data['expired_date'] = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s', $data['expired_date']);
			}

			$model->addData($data);

			try {
				// save the data
				$model->save();
				// display success message



				if ($this->getRequest()->getParam('send')) {
					$model->sendDataToMail();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amgiftcard')->__('The email has been sent successfully.'));
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amgiftcard')->__('The code account has been saved.'));


				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('continue')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId(), '_current'=>true));
					return;
				}

				// go to grid
				$this->_redirect('*/*/');
				return;

			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e,
					Mage::helper('amgiftcard')->__('An error occurred while saving the code account.') . $e->getMessage());
			}

			$this->_getSession()->setFormData($data);

			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			return;
		}

		//Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quantities have been successfully updated'));
		$this->_redirect('*/*/');

	}

	public function deleteAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
		$model = $this->_getModel($id);
		$model->delete();
		$this->_redirect('*/*/');
	}

	public function exportCsvAction()
	{
		$fileName   = 'amasty_giftcard_code_accounts.csv';
		$content    = $this->getLayout()->createBlock('amgiftcard/adminhtml_account_grid')->getCsvFile();

		$this->_prepareDownloadResponse($fileName, $content);
	}


	public function gridOrdersAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
		$model = Mage::getModel('amgiftcard/account')->load($id);
        if ($expDate = $model->getExpiredDate()) {
            $model->setExpiredDate(Mage::getModel('core/date')->date('Y-m-d H:i:s', $expDate));
        }
		Mage::register('amgiftcard_account', $model);

		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_account_edit_tab_order_grid')->toHtml()
		);
	}


	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('customer/amgiftcard/amgiftcard2');
	}
}