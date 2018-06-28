<?php

class FME_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
	const IS_EXT_ENABLE = 'config_options/general_info/module_enable';
    const DOB_SHOW = 'customer/address/dob_show';
    const VAT_SHOW = 'customer/address/taxvat_show';
	const GENDER_SHOW = 'customer/address/gender_show';
//////////////////////FB Details/////////////////////////////////
    const ISENABLE_FB = 'config_options/facebook/module_enable';
    const FB_APP_ID   = 'config_options/facebook/appid';
    const FB_APP_SECRET_KEY = 'config_options/facebook/secretkey';
    
    public function FbisEnable()
    {

    return Mage::getStoreConfig(self::ISENABLE_FB, Mage::app()->getStore()->getId());
  
    }
    public function getFbAPPID(){

    return Mage::getStoreConfig(self::FB_APP_ID, Mage::app()->getStore()->getId());
  
    }
    public function getFb_secretKey(){

    return Mage::getStoreConfig(self::FB_APP_SECRET_KEY, Mage::app()->getStore()->getId());
  
    }


//////////////////////Twitter Details/////////////////////////////////
    const ISENABLE_TW = 'config_options/twitter/module_enable';
    const TW_APP_ID   = 'config_options/twitter/appid';
    const TW_APP_SECRET_KEY = 'config_options/twitter/secretkey';
    
    public function TwisEnable()
    {

    return Mage::getStoreConfig(self::ISENABLE_TW, Mage::app()->getStore()->getId());
  
    }
    public function getTwAPPID(){

    return Mage::getStoreConfig(self::TW_APP_ID, Mage::app()->getStore()->getId());
  
    }
    public function getTw_secretKey(){

    return Mage::getStoreConfig(self::TW_APP_SECRET_KEY, Mage::app()->getStore()->getId());
  
    }
/////////////////////////////////////////////////////////////////

//////////////////////Google Details/////////////////////////////////
    const ISENABLE_GOOGLE = 'config_options/google/module_enable';
    const GOOGLE_APP_NAME   = 'config_options/google/appname';
    const GOOGLE_APP_ID   = 'config_options/google/appid';
    const GOOGLE_APP_SECRET_KEY = 'config_options/google/secretkey';
    
    public function GoogleisEnable()
    {

    return Mage::getStoreConfig(self::ISENABLE_GOOGLE, Mage::app()->getStore()->getId());
  
    }
    public function getGoogleAPPID(){

    return Mage::getStoreConfig(self::GOOGLE_APP_ID, Mage::app()->getStore()->getId());
  
    }
    public function getGoogle_secretKey(){

    return Mage::getStoreConfig(self::GOOGLE_APP_SECRET_KEY, Mage::app()->getStore()->getId());
  
    }
     public function getGoogleAppname(){

    return Mage::getStoreConfig(self::GOOGLE_APP_NAME, Mage::app()->getStore()->getId());
  
    }

//////////////////////Yahoo Details/////////////////////////////////
    const ISENABLE_YAHOO = 'config_options/yahoo/module_enable';
    const YAHOO_APP_ID   = 'config_options/yahoo/appid';
    const YAHOO_APP_CONSUMER_KEY   = 'config_options/yahoo/consumerkey';
    const YAHOO_APP_SECRET_KEY = 'config_options/yahoo/secretkey';
    
    public function YahooisEnable()
    {

    return Mage::getStoreConfig(self::ISENABLE_YAHOO, Mage::app()->getStore()->getId());
  
    }
    public function getYahooAPPID(){

    return Mage::getStoreConfig(self::YAHOO_APP_ID, Mage::app()->getStore()->getId());
  
    }
   public function getYahooConsumerKey(){

    return Mage::getStoreConfig(self::YAHOO_APP_CONSUMER_KEY, Mage::app()->getStore()->getId());
  
    }
    public function getYahoo_secretKey(){

    return Mage::getStoreConfig(self::YAHOO_APP_SECRET_KEY, Mage::app()->getStore()->getId());
  
    }
     
/////////////////////////////////////////////////////////////////
	public function isEnableExtension()
    {
        return Mage::getStoreConfig(self::IS_EXT_ENABLE, Mage::app()->getStore()->getId());
    }

    public function isDobShow()
    {
        return Mage::getStoreConfig(self::DOB_SHOW, Mage::app()->getStore()->getId());
    }
    public function isVATShow()
    {
        return Mage::getStoreConfig(self::VAT_SHOW, Mage::app()->getStore()->getId());
    }
    public function isGenderShow()
    {
        return Mage::getStoreConfig(self::GENDER_SHOW, Mage::app()->getStore()->getId());
    }

    public function isLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    public function getCreatepost(){

      return Mage::helper('core/url')->getCurrentUrl().'sociallogin/createPost';  
    }

    public function CheckCustomer($email)
    {
    //$customer_email = "abc@mail.com";
    
    $customer = Mage::getModel("customer/customer");
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail($email);
    if ($customer == true) {
        return $customer->getEmail();
    }
    else{
         
       return false;

        }
    // echo $customer->getId();
    // echo $customer->getFirstName();
    // echo $customer->getEmail(); 
    }

public function getfblogin()
    {
       
        return $this->_getUrl('customer/account/fblogin');
    }


}