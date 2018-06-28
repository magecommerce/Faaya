<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_CodeGenerator_Abstract
{

	const MAX_ATTEMPTS = 5;
	const MAX_QTY = 10000;

	protected $_template = '';

	protected $_maxQty = 1;

	protected $_mask;

	protected $_countMasksInTemplate;
	protected $_listMaskInTemplate;

	public function __construct($template)
	{
		$this->_mask = array(
			// no "0" and "1" as they are confusing
			'{D}'  => array(2,3,4,5,6,7,8,9),
			// no I, Q and O as they are confusing
			'{L}'  => array('A','B','C','D','E','F','G','H','J','K','L','M','N','P','R','S','T','U','V','W','X','Y','Z'),
		);
	}

	public function setTemplate($template)
	{
		$this->_template = $template;


		$listMask = array();

		foreach($this->_mask as $placeholder=>$values) {
			$this->_countMasksInTemplate[$placeholder] = substr_count($this->_template, $placeholder);
			$this->_countValues[$placeholder] = count($values);
			$listMask[] = preg_quote($placeholder, '/');
		}

		$listMaskInTemplate = array();
		$regExpTemplate = implode('|', $listMask);
		if(preg_match_all('/'.$regExpTemplate.'/', $this->_template, $matches)){
			$listMaskInTemplate = $matches[0];
		}

		$this->_listMaskInTemplate = $listMaskInTemplate;

		$this->_calcMaxQty();

		return $this;
	}


	public function getMaxQty()
	{
		return $this->_maxQty;
	}


	public function generate($qty)
	{
		$this->_validate($qty);
		$this->_beforeGenerate();
		for($i = 0; $i<$qty; $i++) {
			$attempts = 0;
			do {
				$code = $this->_generateCode();
				$attempts++;
			} while($this->exist($code) && $attempts < self::MAX_ATTEMPTS);
			if($attempts == self::MAX_ATTEMPTS){
				Mage::throwException('Maximum number of code combinations for the current template achieved');
			}
			$this->_preprocessCode($code);
		}

		$this->_afterGenerate();
	}

	public function exist($code)
	{
		return false;
	}

	protected function _validate($qty)
	{
		if (false === strpos($this->_template, '{L}') && false === strpos($this->_template, '{D}')){
			$msg = Mage::helper('amgiftcard')->__('Please add {L} or {D} placeholders into the template "%s"', $this->_template);
			Mage::throwException($msg);
		}

		if($qty > $this->getMaxQty()) {
			Mage::throwException(Mage::helper('amgiftcard')->__('Maximum number of code combinations for the current template is %d, please update Quantity field accordingly.', $this->getMaxQty()));
		}

		if($qty > self::MAX_QTY) {
			Mage::throwException(Mage::helper('amgiftcard')->__('Over time, you can generate no more than %d codes.', self::MAX_QTY));
		}
	}


	protected function _beforeGenerate()
	{

	}
	protected function _preprocessCode(&$code)
	{

	}

	protected function _afterGenerate()
	{

	}



	protected function _generateCode()
	{
		$code = $this->_template;
		foreach($this->_listMaskInTemplate as $j=>$maskSymbol){
			$key = array_rand($this->_mask[$maskSymbol]);
			$code = preg_replace('/' . preg_quote($maskSymbol, '/'	) . '/', $this->_mask[$maskSymbol][$key], $code, 1);
		}
		return $code;
	}



	protected function _calcMaxQty()
	{
		$this->_maxQty = $this->_maxQtyByTemplate() - $this->_getExistQtyByTemplate();
	}

	protected function _maxQtyByTemplate()
	{
		$maxQty = 1;
		foreach($this->_mask as $placeholder=>$values) {
			$maxQty *= pow($this->_countValues[$placeholder], $this->_countMasksInTemplate[$placeholder]);
		}

		return $maxQty;
	}

	protected function _getExistQtyByTemplate()
	{
		return 0;
	}
}