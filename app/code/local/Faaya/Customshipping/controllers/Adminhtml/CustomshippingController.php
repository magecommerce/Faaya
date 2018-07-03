<?php
class Faaya_Customshipping_Adminhtml_CustomshippingController extends Mage_Adminhtml_Controller_action
{
	protected function _isAllowed() { return true; }

    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('cms/block')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Customshipping Items'), Mage::helper('adminhtml')->__('Customshipping Items'));
		return $this;
	}

	public function indexAction() {
		$this->_initAction()->renderLayout();
	}

	/*public function bannergroupAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('bannergroup.grid')->setBannergroups($this->getRequest()->getPost('bannergroups', null));
		$this->renderLayout();
	}*/

  /*  public function bannergroupgridAction() {
		$this->loadLayout();
		$this->getLayout()->getBlock('bannergroup.grid')->setBannergroups($this->getRequest()->getPost('bannergroups', null));
		$this->renderLayout();
    }*/

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customshipping/customshipping')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('customshipping_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('cms/block');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('customshipping Items'), Mage::helper('adminhtml')->__('customshipping Items'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('customshipping Items'), Mage::helper('adminhtml')->__('customshipping Items'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customshipping/adminhtml_customshipping_edit'))
				->_addLeft($this->getLayout()->createBlock('customshipping/adminhtml_customshipping_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customshipping')->__('Customshipping Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {
        if ($data = $this->getRequest()->getPost())
		{
            $model = Mage::getModel('customshipping/customshipping');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

				$model->save();
	
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customshipping')->__('customshipping Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customshipping')->__('Unable to find customshipping Item to save'));
        $this->_redirect('*/*/');
    }

	public function deleteAction() {
		$id = $this->getRequest()->getParam('id');
		if($id > 0) {
			try {
				$model = Mage::getModel('customshipping/customshipping')->setId($id)->delete(); // Delete particular Banner Item

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('customshipping Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $id));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $banIds = $this->getRequest()->getParam('customshipping');
        if(!is_array($banIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customshipping Item(s)'));
        } else {
            try {
                foreach ($banIds as $banId) {
                    $banner = Mage::getModel('customshipping/customshipping')->load($banId)->delete(); // Delete Selected Banners
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d customshipping Item(s) were successfully deleted', count($banIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $banIds = $this->getRequest()->getParam('customshipping');
        if(!is_array($banIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select customshipping Item(s)'));
        } else {
            try {
                foreach ($banIds as $banId) {
                    $banner = Mage::getSingleton('customshipping/customshipping')
                        ->load($banId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($banIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'customshipping.csv';
        $content    = $this->getLayout()->createBlock('banner/adminhtml_customshipping_grid')->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'customshipping.xml';
        $content    = $this->getLayout()->createBlock('banner/adminhtml_customshipping_grid')->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
