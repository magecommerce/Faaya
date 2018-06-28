<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Code extends Amasty_GiftCard_Model_Abstract
{
	const STATE_USED = 1;
	const STATE_UNUSED = 0;

	public function loadFreeCode($code_set_id)
	{
		$this->_getResource()->loadFreeCode($this, $code_set_id);

		return $this;
	}

	/**
	 * Is code used
	 * @return bool
	 */
	public function isUsed()
	{
		return $this->getUsed() == self::STATE_USED;
	}

	protected function _construct()
	{
		$this->_init('amgiftcard/code');
	}

	protected function _beforeDelete()
	{
		parent::_beforeDelete();
		if($this->isUsed()){
			throw new Exception(Mage::helper('amgiftcard')->__('Delete impossible. Code used'));
		}
		return $this;
	}

	public function generateAndSaveCodes($qty, $template, $codeSetId)
	{
		$listDefaultDigits = range(0,9);
		$listDefaultLetters = range('A','Z');

		$listDigits = $listDefaultDigits;
		$listLetters = $listDefaultLetters;


		$listCodes = array();
		//$tmpListCodes = array();
		$validCodes = true;
		do {
			for($i = 0; $i<$qty; $i++) {
				do {
					$code = $this->generateCode($template);
				} while(isset($listCodes[$this->generateCode($template)]));
			}
		} while(!$validCodes);

	}

	public function importCsv($csvFile)
	{

	}

	public function generateCode($template)
	{

	}


}