<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Form_Element_Block extends Varien_Data_Form_Element_Abstract
{

	public function __construct($attributes = array())
	{
		parent::__construct($attributes);
		$this->getBlock()->setData('form_element', $this);

	}

	public function getElementHtml()
	{
		$html = $this->getBlock()->toHtml();
		$html.= $this->getAfterElementHtml();
		return $html;

	}

}