<?php require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'InvoiceController.php';

class Cda_Wizard_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        $addedItem = array();
        foreach ($data['items'] as $key=>$value) {
            if($value > 0){
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
                        $data['items'][$orderItem->getId()] =  1;
                    }else{
                        $data['items'][$orderItem->getId()] =  0;
                    }
                }
            }
        }
        $this->getRequest()->setPost('invoice',$data);

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $invoice = $this->_initInvoice();
            if ($invoice) {

                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                }

                $invoice->register();

                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $shipment = false;
                if (!empty($data['do_shipment']) || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                $transactionSave->save();

                if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                    $this->_getSession()->addError($this->__('The invoice and the shipment  have been created. The shipping label cannot be created at the moment.'));
                } elseif (!empty($data['do_shipment'])) {
                    $this->_getSession()->addSuccess($this->__('The invoice and shipment have been created.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('The invoice has been created.'));
                }

                // send invoice/shipment emails
                $comment = '';
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
                try {
                    $invoice->sendEmail(!empty($data['send_email']), $comment);
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Unable to send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $shipment->sendEmail(!empty($data['send_email']));
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $this->_getSession()->addError($this->__('Unable to send the shipment email.'));
                    }
                }
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('*/*/new', array('order_id' => $orderId));
            }
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to save the invoice.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/new', array('order_id' => $orderId));
    }


}

