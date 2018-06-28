<?php
class Faaya_Customregister_IndexController extends Mage_Core_Controller_Front_Action{
    public function registerAction() {
        $email = $this->getRequest()->getParam('email_address');
        $firstname = $this->getRequest()->getParam('firstname');
        $lastname = $this->getRequest()->getParam('firstname');
        $password = $this->getRequest()->getParam('password');
        $isNewletter = $this->getRequest()->getParam('isSubscribe');
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);
        //$subscriber = Mage::getModel('newsletter/subscriber')->subscribe($email);
        if(!$customer->getId()) {
          $customer->setEmail($email); 
          $customer->setFirstname($firstname);
          $customer->setLastname($lastname);
          $customer->setPassword($password);
          if($isNewletter){
              $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
              if(!$subscriber->getId() || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE)
              {
                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED); 
                    $subscriber->setSubscriberEmail($email); 
                    $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence()); 
                    $subscriber->setStoreId(Mage::app()->getStore()->getId());
                    $subscriber->setCustomerId($customer->getId());             
                    $subscriber->save();
              }
          }
          try{
              $customer->save();
              $customer->setConfirmation(null); //confirmation needed to register?
              $customer->save(); //yes, this is also needed
              $customer->sendNewAccountEmail(); //send confirmation email to customer?
              
              $session = Mage::getSingleton('customer/session');
              $session->setCustomerAsLoggedIn($customer);
              $response['success'] = 'success';
              return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
              //$this->_redirect('checkout/onepage/index/');
          } catch(Exception $e){
               Mage::log($e->__toString());
          }
        }else{
            $response['fail'] = 'There is already an account with this email address.';
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }
}