<?php  

include_once("facebook/facebook.php"); 

 
class FME_Sociallogin_IndexController extends Mage_Core_Controller_Front_Action {  
 
    // public function indexAction()
    // {
    //     $this->loadLayout();
    //     $this->renderLayout();
    // }

protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
public function fbloginAction(){
 

 $facebook = new Facebook(array(
           'appId'  => Mage::helper('sociallogin')->getFbAPPID(),
           'secret' => Mage::helper('sociallogin')->getFb_secretKey(),
              ));
                $user = $facebook->getUser();
                if ($user) {
                  $user_profile = $facebook->api('/me','GET');
                  //echo $user_profile['name'].'<br>';
                  //echo $user_profile['first_name'].'<br>';
                  //echo $user_profile['last_name'].'<br>';
             
                         
                        $customer = Mage::getModel('customer/customer');
                        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                        $customer->loadByEmail($user_profile['email']);
                        if(!$customer->getId()) 
                        { // if customer does not already exists, by email
                         // new data
                        $customer->setEmail($user_profile['email']);
                        $customer->setFirstname($user_profile['first_name']);
                        $customer->setLastname($user_profile['last_name']);
                        $Password = $customer->generatePassword(); // generate a new password
                        $customer->setPassword($Password); // set it
                        $customer->setConfirmation(null);
                        $customer->save();
                        $customer->sendNewAccountEmail(); // save successful, send new password
                        $this->_getSession()->loginById($customer->getId());
                        $this->_redirect('customer/account');
                      } 
                      else{
                   
                        $this->_getSession()->loginById($customer->getId());
                        $this->_redirect('customer/account');
                      }

                     
                  }
          
          

  }     

public function twitterpostAction(){

session_start();
include_once("twitter/twitteroauth.php");
include_once ('twitter/config.php');
$oauth_token    = Mage::app()->getRequest()->getParam('oauth_token');
$oauth_verifier = Mage::app()->getRequest()->getParam('oauth_verifier');
$email = Mage::app()->getRequest()->getPost('email');


//echo $oauth_token;
if (isset($oauth_token) && Mage::getSingleton('core/session')->getToken()  !== $oauth_token) {

  // if token is old, distroy any session and redirect user to index.php
  session_destroy();
  //header('Location: ./index.php');
  
}elseif(isset($oauth_token) && Mage::getSingleton('core/session')->getToken() == $oauth_token) {

  // everything looks good, request access token
  //successful response returns oauth_token, oauth_token_secret, user_id, and screen_name
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, Mage::getSingleton('core/session')->getToken() , Mage::getSingleton('core/session')->getTokenSecret());
  $access_token = $connection->getAccessToken($oauth_verifier);
  if($connection->http_code=='200')
  {

    //redirect user to twitter
    Mage::getSingleton('core/session')->setStatus('verified');
    //$_SESSION['status'] = 'verified';
    Mage::getSingleton('core/session')->setRequestVars($access_token) ;
  // print_r(Mage::getSingleton('core/session')->getRequestVars());exit();
    $screenname    = Mage::getSingleton('core/session')->getRequestVars();
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail(Mage::getSingleton('core/session')->getEmail());
    //print_r($screenname['screen_name']);exit();
     if(!$customer->getId()) 
      { // if customer does not already exists, by email
                         // new data
        
      
        $customer->setEmail(Mage::getSingleton('core/session')->getEmail());
        $customer->setFirstname($screenname['screen_name']);
        $customer->setLastname($screenname['screen_name']);
        $Password = $customer->generatePassword(); // generate a new password
        $customer->setPassword($Password); // set it
        $customer->setConfirmation(null);
        $customer->save();
         
        //  $customer->save();

         $customer->sendNewAccountEmail(); // save successful, send new password
        $this->_getSession()->loginById($customer->getId());
        $this->_redirect('customer/account');

    }else{
      $this->_getSession()->loginById($customer->getId());
      $this->_redirect('customer/account');}
    Mage::getSingleton('core/session')->unsToken();
    Mage::getSingleton('core/session')->unsTokenSecret();
   // unset($_SESSION['token']);
    //unset($_SESSION['token_secret']);
  
  }else{
    die("error, try again later!");
  }
    
}else{
 
  $denied =  Mage::app()->getRequest()->getParam('denied');
  if(isset($denied))
  {
   echo Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl());
    //$this->_redirect();
    //header('Location: ./index.php');
   // die();
  }

  //fresh authentication
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
  $request_token = $connection->getRequestToken(OAUTH_CALLBACK);
  
  //received token info from twitter

   $token     = $request_token['oauth_token'];
   $token_secret   = $request_token['oauth_token_secret'];
   Mage::getSingleton('core/session')->setToken($token);
   Mage::getSingleton('core/session')->setTokenSecret($token_secret);
  // any value other than 200 is failure, so continue only if http code is 200
  if($connection->http_code=='200')
  {
 Mage::getSingleton('core/session')->setEmail($email);
    //redirect user to twitter
    $twitter_url = $connection->getAuthorizeURL($request_token['oauth_token']);

    echo $twitter_url;
    //header('Location: ' . $twitter_url); 
  }else{
    die("error connecting to twitter! try again later!");
  }
}
   
}  
   
public function googlepostAction(){
require_once 'google/src/Google_Client.php';
require_once 'google/src/contrib/Google_Oauth2Service.php';
$google_client_id     = Mage::helper('sociallogin')->getGoogleAPPID();
$google_client_secret   = Mage::helper('sociallogin')->getGoogle_secretKey();
//$google_redirect_url  = 'http://demo004.fmeaddons.com/index.php/sociallogin/index/googlepost/';//Mage::getUrl('sociallogin/index/googlepost/');//'http://localhost/magento18/index.php/sociallogin/index/googlepost/'; //path to your script
$google_redirect_url = Mage::getBaseUrl().'sociallogin/index/googlepost/';

$gClient = new Google_Client();
$gClient->setApplicationName(Mage::helper('sociallogin')->getGoogleAppname());
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
//$gClient->setDeveloperKey($google_developer_key);
$code = Mage::app()->getRequest()->getParam('code');
$google_oauthV2 = new Google_Oauth2Service($gClient);
if (isset($code)) 
{ 
 

  $gClient->authenticate($code);
  Mage::getSingleton('core/session')->setToken($gClient->getAccessToken());
  //$_SESSION['token'] = $gClient->getAccessToken();
  $user         = $google_oauthV2->userinfo->get();
  $email        = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
  $gender      = filter_var($user['gender'], FILTER_SANITIZE_SPECIAL_CHARS);
  $firstname = filter_var($user['given_name'],FILTER_SANITIZE_SPECIAL_CHARS);
  $lastname = filter_var($user['family_name'],FILTER_SANITIZE_SPECIAL_CHARS);
 

    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail($email);
    if(!$customer->getId()) 
      { // if customer does not already exists, by email
                         // new data
        $customer->setEmail($email);
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setGender($gender);
        $Password = $customer->generatePassword(); // generate a new password
        $customer->setPassword($Password); // set it
        $customer->setConfirmation(null);
        $customer->save();
        $customer->sendNewAccountEmail(); // save successful, send new password
        $this->_getSession()->loginById($customer->getId());
        $this->_redirect('customer/account');

    }else{
      $this->_getSession()->loginById($customer->getId());
      $this->_redirect('customer/account');
      Mage::getSingleton('core/session')->unsToken();
    }
      
    

 //echo '<pre>'; 
 //print_r($user);
  // echo '</pre>';
  //header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
  return;
}
else 
{
  //For Guest user, get google login url
  $authUrl = $gClient->createAuthUrl();
  echo Mage::app()->getResponse()->setRedirect($authUrl);
}



}

public function yahoopostAction()
 {
      require_once 'yahoo/Yahoo.inc';
      define('OAUTH_CONSUMER_KEY', Mage::helper('sociallogin')->getYahooConsumerKey()); // Place Yoru Consumer Key here
      define('OAUTH_CONSUMER_SECRET', Mage::helper('sociallogin')->getYahoo_secretKey()); // Place your Consumer Secret
      define('OAUTH_APP_ID', Mage::helper('sociallogin')->getYahooAPPID()); // Place Your App ID here


    $session = YahooSession::requireSession(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID);
    if (is_object($session)) {
        $user = $session->getSessionedUser();
        $profile = $user->getProfile();
       //print_r($profile);exit;
      $email =  $profile->emails[0]->handle;
       //print_r($profile->emails[0]->handle);
      $name = $profile->nickname;
        // foreach ($profile->emails as $userpro) {
        //    echo  $userpro->handle ;

        //      print_r($userpro->handle);
        //  }
      //echo  $name = $profile->givenName; // Getting user name
     // echo  $guid = $profile->guid; // Getting Yahoo ID
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail($email);
    if(!$customer->getId()) 
      { // if customer does not already exists, by email
                         // new data
        $customer->setEmail($email);
        $customer->setFirstname($name);
        $customer->setLastname($name);
        $Password = $customer->generatePassword(); // generate a new password
        $customer->setPassword($Password); // set it
        $customer->setConfirmation(null);
        $customer->save();
        $customer->sendNewAccountEmail(); // save successful, send new password
        $this->_getSession()->loginById($customer->getId());
        $this->_redirect('customer/account');

    }else{
      $this->_getSession()->loginById($customer->getId());
      $this->_redirect('customer/account');
    }

    }
     
 }

}