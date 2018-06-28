<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_CodeGenerator extends Amasty_GiftCard_Model_CodeGenerator_Abstract
{
	protected $_listCodes = array();
	protected $_listAllCodes = array();
	protected $_countCodes = 0;

	/**
	 * @var Amasty_GiftCard_Model_Resource_Code
	 */
	protected $_resource;
	protected $_paramsForSave;

	protected function _preprocessCode(&$code)
	{
		$this->_listCodes[] = $code;
		$this->_listAllCodes[] = $code;

		$this->_countCodes++;

		if($this->_countCodes >= 500) {
			$this->_insert();
			$this->_listCodes = array();
			$this->_countCodes = 0;
		}
	}

	public function setResource($resource)
	{
		$this->_resource = $resource;

		return $this;
	}

	public function generateAndSave($qty, $paramsForSave)
	{
		$this->_paramsForSave = $paramsForSave;
		$this->generate($qty);
	}

	public function exist($code)
	{
		return in_array($code, $this->_listAllCodes) || $this->existDb($code);
	}

	public function existDb($code)
	{
		return $this->_resource->exists($code);
	}

	protected function _insert()
	{
		$this->_resource->massSaveCodes($this->_listCodes, $this->_paramsForSave);
	}

	protected function _afterGenerate()
	{
		$this->_insert();
		$this->_listCodes = array();
		$this->_countCodes = 0;
	}

	protected function _getExistQtyByTemplate()
	{
		$template = $this->_template;
		foreach($this->_mask as $placeholder=>$values) {
			$template = str_replace($placeholder, "_", $template);
		}
		return $this->_resource->countByTemplate($template);
	}


}