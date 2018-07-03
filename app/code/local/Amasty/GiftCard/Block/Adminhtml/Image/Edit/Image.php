<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Image_Edit_Image extends Mage_Adminhtml_Block_Widget
{

	public function __construct()
	{
		parent::__construct();
		//$this->setTemplate('amgiftcard/');
		$this->setTemplate('amasty/amgiftcard/image/edit/image.phtml');
	}
}