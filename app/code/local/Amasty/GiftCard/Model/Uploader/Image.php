<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class  Amasty_GiftCard_Model_Uploader_Image extends Amasty_GiftCard_Model_Uploader_Abstract
{
	protected function _init()
	{
		parent::_init();

		$this->setAllowedExtensions(array('jpg','jpeg','gif','png'));
	}


}