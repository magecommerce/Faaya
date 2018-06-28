<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'CreditmemoController.php';

class Cda_Wizard_Sales_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');

        $orderId = $this->getRequest()->getParam('order_id');

        $addedItem = array();
        foreach ($data['items'] as $key=>$value) {
            if($value['qty'] > 0){
                $addedItem[] = $key;
            }
        }
        $orderData = Mage::getModel('sales/order')->load($orderId);
        $itemsData = $orderData->getAllItems();
        $data['items'] = array();

        foreach ($itemsData as $orderItem) {
            $options = Mage::getResourceModel('sales/quote_item_option_collection');
            $options->addItemFilter($orderItem->getData('quote_item_id'));
            foreach ($options as $option) {
                if ($option->getCode() == 'setting') {
                    $values = unserialize($option->getValue()); // to array object
                    if(in_array($values['group']['sid'], $addedItem)){
                        $data['items'][$orderItem->getId()]['qty'] =  1;
                    }else{
                        $data['items'][$orderItem->getId()]['qty'] =  0;
                    }
                }
            }
        }
        $this->getRequest()->setPost('creditmemo',$data);

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The credit memo has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/*/new', array('_current' => true));
    }

}