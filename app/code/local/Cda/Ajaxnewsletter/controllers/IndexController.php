<?php
class Cda_Ajaxnewsletter_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

        $this->loadLayout();   

        $this->renderLayout(); 

    }
    public function newAction()
    {

        $response = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $message = $this->__('Please enter a valid email address.');
                    $response['status'] = 'ERROR';
                    $response['message'] = $message;
                    Mage::throwException($message);
                }
                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                !$customerSession->isLoggedIn()) {
                    $message = $this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl());
                    $response['status'] = 'ERROR';
                    $response['message'] = $message;
                    Mage::throwException($message);
                }
                $ownerId = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email)
                ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    $message = $this->__('This email address is already assigned to another user.');
                    $response['status'] = 'ERROR';
                    $response['message'] = $message;
                    Mage::throwException($message);
                }
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $message = $this->__('Confirmation request has been sent.');
                    $response['status'] = 'SUCCESS';
                    $response['message'] = $message;

                }
                else {
                    $message = $this->__('Thank you for your subscription.');;
                    $response['status'] = 'SUCCESS';
                    $response['message'] = $message;

                    /****** Have added code from Hook Controller of Fontis to solve the conflict starts******/
                    if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                        $session   = Mage::getSingleton('core/session');
                        $email     = (string)$this->getRequest()->getPost('email');

                        Mage::log("Fontis_CampaignMonitor: Adding newsletter subscription via frontend 'Sign up' block for $email");

                        $apiKey = trim(Mage::getStoreConfig('newsletter/campaignmonitor/api_key'));
                        $listID = trim(Mage::getStoreConfig('newsletter/campaignmonitor/list_id'));

                        if($apiKey && $listID) {
                            try {
                                $client = new SoapClient("http://api.createsend.com/api/api.asmx?wsdl", array("trace" => true));
                            } catch(Exception $e) {
                                Mage::log("Fontis_CampaignMonitor: Error connecting to CampaignMonitor server: ".$e->getMessage());
                                $session->addException($e, $this->__('There was a problem with the subscription'));
                                $this->_redirectReferer();
                            }

                            // if a user is logged in, fill in the Campaign Monitor custom
                            // attributes with the data for the logged-in user
                            $customerHelper = Mage::helper('customer');
                            if($customerHelper->isLoggedIn()) {
                                $customer = $customerHelper->getCustomer();
                                $name = $customer->getFirstname() . " " . $customer->getLastname();
                                $customFields = Fontis_CampaignMonitor_Model_Customer_Observer::generateCustomFields($customer);
                                try {    
                                    $result = $client->AddAndResubscribeWithCustomFields(array(
                                    "ApiKey" => $apiKey,
                                    "ListID" => $listID,
                                    "Email" => $email,
                                    "Name" => $name,
                                    "CustomFields" => $customFields));
                                } catch(Exception $e) {
                                    Mage::log("Fontis_CampaignMonitor: Error in CampaignMonitor SOAP call: ".$e->getMessage());
                                    $session->addException($e, $this->__('There was a problem with the subscription'));
                                    $this->_redirectReferer();
                                }
                            } else {
                                // otherwise if nobody's logged in, ignore the custom
                                // attributes and just set the name to '(Guest)'
                                try {
                                    $result = $client->AddAndResubscribe(array(
                                    "ApiKey" => $apiKey,
                                    "ListID" => $listID,
                                    "Email" => $email,
                                    "Name" => $this->getRequest()->getPost('text')));
                                } catch (Exception $e) {
                                    Mage::log("Fontis_CampaignMonitor: Error in CampaignMonitor SOAP call: ".$e->getMessage());
                                    $session->addException($e, $this->__('There was a problem with the subscription'));
                                    $this->_redirectReferer();
                                }
                            }
                        } else {
                            Mage::log("Fontis_CampaignMonitor: Error: Campaign Monitor API key and/or list ID not set in Magento Newsletter options.");
                        }
                    }
                     /****** Have added code from Hook Controller of Fontis to solve the conflict ends******/

                }
            }
            catch (Mage_Core_Exception $e) {
                $message = $this->__('There was a problem with the subscription: %s', $e->getMessage());
                $response['status'] = 'ERROR';
                $response['message'] = $message;

            }
            catch (Exception $e) {
                $message = $this->__('There was a problem with the subscription.');
                $response['status'] = 'ERROR';
                $response['message'] = $message;

            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;

    }
}
