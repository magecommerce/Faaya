<?php
class Faaya_Assist_IndexController extends Mage_Core_Controller_Front_Action{
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_RECIPIENT  = 'contacts/email/recipient_email';
    public function indexAction() {
        $post = $this->getRequest()->getParams();
        $asssistmodel = Mage::getModel('assist/assist');
        $asssistmodel->setData('unique_id',$post['uid']);
        $asssistmodel->setData('email_id',$post['emailid']);
        $asssistmodel->setData('contact_no',$post['contactno']);
        if($asssistmodel->save()){
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);
                $emailTemplate = Mage::getModel('core/email_template')->loadByCode('Faaya assist');
                $emailTemplateVariables = array('data' => $postObject);
                $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
                //echo "<pre/>";print_r($processedTemplate);exit;
                $emailTemplate->setSenderName('Faaya assist');
                $emailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER));
                $emailTemplate->setTemplateSubject("Faaya assist");
                $emailTemplate->send(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT), 'Admin', $emailTemplateVariables);
            } catch (Exception $e) {
                throw new Exception('email not sent');
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode('Thank you for filling this form, we will get back to you soon'));
        }else{
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode('Something went wrong'));
        }

    }
}