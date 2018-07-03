<?php
//Replace XXX with your twitter application variables.
define('CONSUMER_KEY', Mage::helper('sociallogin')->getTwAPPID());
define('CONSUMER_SECRET', Mage::helper('sociallogin')->getTw_secretKey());
define('OAUTH_CALLBACK', Mage::getUrl('sociallogin/index/twitterpost'));
