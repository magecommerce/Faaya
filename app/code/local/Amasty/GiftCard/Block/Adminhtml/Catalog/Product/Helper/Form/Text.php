<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Block_Adminhtml_Catalog_Product_Helper_Form_Text extends Varien_Data_Form_Element_Text
{
	/**
	 * Retrieve element html
	 *
	 * @return string
	 */
	public function getElementHtml()
	{
		$value = $this->getValue();
		if ($value == '') {
			$this->setValue($this->_getValueFromConfig());
		}
		$html = parent::getElementHtml();

		$htmlId   = 'use_config_' . $this->getHtmlId();
		$checked  = ($value == '') ? ' checked="checked"' : '';
		$disabled = ($this->getReadonly()) ? ' disabled="disabled"' : '';

		$html .= '<input id="'.$htmlId.'" name="product['.$htmlId.']" '.$disabled.' value="1" ' . $checked;
		$html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';
		$html .= ' <label for="'.$htmlId.'">' . Mage::helper('adminhtml')->__('Use Config Settings').'</label>';
		$html .= '<script type="text/javascript">toggleValueElements($(\''.$htmlId.'\'), $(\''.$htmlId.'\').parentNode);</script>';

		return $html;
	}

	/**
	 * Get config value data
	 *
	 * @return mixed
	 */
	protected function _getValueFromConfig()
	{
		return '';
	}
}