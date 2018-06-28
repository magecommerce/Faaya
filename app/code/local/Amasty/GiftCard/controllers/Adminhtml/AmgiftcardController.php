<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Adminhtml_AmgiftcardController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		$this->loadLayout();
		$this->_title($this->__('Gift Cards'));
		/*$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_amgiftcard')
		);*/
		$this->renderLayout();
	}

	public function codesAction()
	{
		$this->loadLayout();
		$this->_title($this->__('Gift Card Code Sets'));
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet')
		);
		$this->renderLayout();
	}

	public function massDeleteCodeSetAction()
	{
		$deleteIds = $this->getRequest()->getParam('code_sets');
		if(!is_array($deleteIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amgiftcard')->__('Please select code set(s).'));
		} else {
			try {
				$collection = Mage::getModel('amgiftcard/codeSet')->getCollection()->addFieldToFilter("code_set_id", array('in'=>$deleteIds));
				$collection->massDelete();
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

		}
		$this->_redirect('*/*/codes');
	}

	public function deleteCodeAction()
	{
		$deleteId = $this->getRequest()->getParam('code_id');
		try {
			$code = Mage::getModel('amgiftcard/code')->load($deleteId)->delete();
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$codeSetId = $code->getCodeSetId();
		$this->_redirect('*/*/editCodeSet', array('code_set_id'=>$codeSetId));
	}

	public function editCodeSetAction()
	{
		$id = (int)$this->getRequest()->getParam('code_set_id');
		$model = Mage::getModel('amgiftcard/codeSet')->load($id);
		Mage::register('amgiftcard_codeSet', $model);

		$this->loadLayout();
		$this->_title($this->__('Gift Card Code Set Edit'));
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet_edit')
		);

		$this->_addLeft($this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet_edit_tabs'));


		$this->renderLayout();
	}

	public function saveCodeSetAction()
	{
		if($data = $this->getRequest()->getPost())
		{
			$model = Mage::getModel('amgiftcard/codeSet');

			if ($id = $this->getRequest()->getParam('code_set_id')) {
				$model->load($id);
			}

			$fieldsForSave = array(
				'title',
				'template',
				'qty',
			);
			foreach($fieldsForSave as $field) {
				if(isset($data[$field])) {
					if($field == 'template') {
						$data[$field] = trim($data[$field]);
					}
					$model->setData($field, $data[$field]);
				}
			}

			try {
				// save the data
				$model->save();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('amgiftcard')->__('The code set has been saved.'));
				// clear previously saved data from session
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('continue')) {
					$this->_redirect('*/*/editCodeSet', array('code_set_id' => $model->getCodeSetId(), '_current'=>true));
					return;
				}
				// go to grid
				$this->_redirect('*/*/codes');
				return;

			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
			catch (Exception $e) {
				$this->_getSession()->addException($e,
					Mage::helper('amgiftcard')->__('An error occurred while saving the code set.'));
			}

			$this->_getSession()->setFormData($data);

			$this->_redirect('*/*/editCodeSet', array('code_set_id' => $this->getRequest()->getParam('code_set_id')));
			return;
		}

		//Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quantities have been successfully updated'));
		$this->_redirect('*/*/codes');
	}

	public function gridCodeAction()
	{
		$id = (int)$this->getRequest()->getParam('code_set_id');
		$model = Mage::getModel('amgiftcard/codeSet')->load($id);
		Mage::register('amgiftcard_codeSet', $model);

		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet_edit_tab_codesList_grid')->toHtml()
		);
	}

	/**
	 * Export customer grid to CSV format
	 */
	public function exportCodesCsvAction()
	{
		$id = (int)$this->getRequest()->getParam('code_set_id');
		$model = Mage::getModel('amgiftcard/codeSet')->load($id);
		Mage::register('amgiftcard_codeSet', $model);

		$fileName   = 'amasty_giftcard_codes.csv';
		$content    = $this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet_edit_tab_codesList_grid')
			->getCsvFile();

		$this->_prepareDownloadResponse($fileName, $content);
	}

	/**
	 * Export customer grid to XML format
	 */
	public function exportCodesXmlAction()
	{
		$id = (int)$this->getRequest()->getParam('code_set_id');
		$model = Mage::getModel('amgiftcard/codeSet')->load($id);
		Mage::register('amgiftcard_codeSet', $model);

		$fileName   = 'amasty_giftcard_codes.xml';
		$content    = $this->getLayout()->createBlock('amgiftcard/adminhtml_codeSet_edit_tab_codesList_grid')
			->getXml();

		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function accountsAction()
	{
		$this->loadLayout();
		$this->_title($this->__('Gift Card Accounts'));
		/*$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_amgiftcard')
		);*/
		$this->renderLayout();
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('customer/amgiftcard/amgiftcard');
	}
}