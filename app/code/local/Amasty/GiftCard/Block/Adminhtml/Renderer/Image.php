<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * Renders grid column
	 *
	 * @param   Varien_Object $row
	 * @return  string
	 */
	public function render(Varien_Object $row)
	{
		$html = '<img ';
		$html .= 'style="width:128px; height:128px;" ';
		$html .= 'src="' . $this->_getValue($row) . '"';
		$html .= 'class="' . $this->getColumn()->getInlineCss() . '"/>';
		return $html;
	}
}