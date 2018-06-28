<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Adminhtml_AmgiftcardimgController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		$this->loadLayout();
		$this->_title($this->__('Gift Card Images'));
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_image')
		);
		$this->renderLayout();
	}

	public function editAction()
	{
		$id = (int)$this->getRequest()->getParam('id');
		$model = Mage::getModel('amgiftcard/image')->load($id);
		Mage::register('amgiftcard_image', $model);

		$this->loadLayout();
		$this->_addContent(
			$this->getLayout()->createBlock('amgiftcard/adminhtml_image_edit')
		);
		$this->_addLeft($this->getLayout()->createBlock('amgiftcard/adminhtml_image_edit_tabs'));
		$this->renderLayout();
	}

	public function saveAction()
	{
		if($data = $this->getRequest()->getPost())
		{
			$id = (int)$this->getRequest()->getParam('id');
			$model = Mage::getModel('amgiftcard/image')->load($id);

			$model->addData($data);

			try {
				// save the data
				$model->save();
				// display success message
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('amgiftcard')->__('The image has been saved.'));
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
					Mage::helper('amgiftcard')->__('An error occurred while saving the image.'));
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
		$model = Mage::getModel('amgiftcard/image')->load($id);
		$model->delete();
		//Mage::register('amgiftcard_image', $model);
		$this->_redirect('*/*/');
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('customer/amgiftcard/amgiftcard3');
	}
}