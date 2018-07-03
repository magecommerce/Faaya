<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
abstract class Amasty_GiftCard_Model_Abstract extends Mage_Core_Model_Abstract
{

	protected $_isMassDelete = false;



	public final function onBeforeMassDelete()
	{
		$this->_isMassDelete = true;
		try {
			//$this->_beforeMassDelete();
			$this->_beforeDelete();
		}
		catch (Exception $e){
			throw $e;
		}
		return $this;
	}

	public final function onAfterMassDelete()
	{
		try {
			//$this->_afterMassDelete();
			$this->_afterDelete();
			$this->_afterDeleteCommit();
		}
		catch (Exception $e){
			throw $e;
		}
		return $this;
	}

	public function isMassDelete()
	{
		return $this->_isMassDelete;
	}

	public function beforeMassDeleteAll($listIds, $collection)
	{
	}

	public function afterMassDeleteAll($listIds, $collection)
	{
	}
}