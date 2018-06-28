<?php
require 'app/Mage.php';
Mage::app();

Mage::getModel('wizard/attribute')->importAttribute();
exit;
