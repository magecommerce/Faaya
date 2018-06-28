<?php require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'ShipmentController.php';

class Cda_Wizard_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
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
        $this->getRequest()->setPost('shipment',$data);

        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }


}

