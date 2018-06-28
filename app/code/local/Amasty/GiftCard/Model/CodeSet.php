<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_CodeSet extends Amasty_GiftCard_Model_Abstract
{

	public function getTemplate()
	{
		$template = parent::getTemplate();

		if(empty($template) && $this->getData('qty') > 0) {
			$template = Mage::helper('amgiftcard')->__('Imported');
		}

		return $template;
	}

	protected function _construct()
	{
		$this->_init('amgiftcard/codeSet');
	}

	protected function _generateCodes($qty)
	{

		$codeGenerator = Mage::getModel('amgiftcard/codeGenerator');
		$codeGenerator
			->setResource(Mage::getResourceModel('amgiftcard/code'))
			->setTemplate($this->getTemplate())
			->generateAndSave($qty,  array('code_set_id'=>$this->getId()));
	}

	protected function _loadCodes($csvFile)
	{
		$io     = new Varien_Io_File();
		$info   = pathinfo($csvFile);
		$io->open(array('path' => $info['dirname']));
		$io->streamOpen($info['basename'], 'r');
		$listAllCodes = array();
		$listCodes = array();
		$rowNumber = 0;
		$tmpCountRows = 0;
		$duplicateCodes = array();
		while (false !== ($csvLine = $io->streamReadCsv(";"))) {
			$rowNumber++;
			if (empty($csvLine)) {
				continue;
			}
			$code = $csvLine[0];

			$listAllCodes[$code] = $rowNumber;
			$listCodes[$code] = 1;

			if($tmpCountRows >= 100) {
				$collection = Mage::getResourceModel('amgiftcard/code_collection')->addFieldToFilter('code', array('in'=>array_keys($listCodes)));
				$listCodes = array();
				$tmpCountRows = 0;
				foreach($collection AS $itemCode) {
					//$this->_importErrors[] = Mage::helper('amgiftcard')->__('Code %s in row %d already exists', $itemCode->getCode(), $listAllCodes[$itemCode->getCode()]);
					$duplicateCodes[$itemCode->getCode()] = $listAllCodes[$itemCode->getCode()];
				}
			}
			$tmpCountRows++;
		}

		if($tmpCountRows > 0) {
			$collection = Mage::getResourceModel('amgiftcard/code_collection')->addFieldToFilter('code', array('in'=>array_keys($listCodes)));
			foreach($collection AS $itemCode) {
				//$this->_importErrors[] = Mage::helper('amgiftcard')->__('Code %s in row %d already exists', $itemCode->getCode(), $listAllCodes[$itemCode->getCode()]);
				$duplicateCodes[$itemCode->getCode()] = $listAllCodes[$itemCode->getCode()];
			}
		}
		$countDuplicateCodes = count($duplicateCodes);
		if($countDuplicateCodes > 0) {
			if($rowNumber == $countDuplicateCodes) {
				$error = Mage::helper('amgiftcard')->__('All codes already exists');
			} else {
				$strListCodes = array();
				foreach ($duplicateCodes as $code=>$codeRow) {
					$strListCodes[] = Mage::helper('amgiftcard')->__('%s in row %d ', $code, $codeRow);
				}
				$error = Mage::helper('amgiftcard')->__('Codes already exists. Duplicate codes:<br /> %s', implode(" \n", $strListCodes));
			}
			Mage::throwException($error);
			return false;
		}

		$listForSave = array_keys($listAllCodes);
		Mage::getResourceModel('amgiftcard/code')->massSaveCodes($listForSave, array('code_set_id'=>$this->getId()));

		return true;
	}

	/*protected function _beforeSave()
	{
		if($qty = $this->getData('qty')) {

		}
	}*/

	protected function _afterSave()
	{
		if($qty = $this->getData('qty')) {
			$this->_generateCodes($qty);
		}

		if(is_uploaded_file($_FILES['csv']['tmp_name'])) {
			$this->_loadCodes($_FILES['csv']['tmp_name']);
		}
	}
}