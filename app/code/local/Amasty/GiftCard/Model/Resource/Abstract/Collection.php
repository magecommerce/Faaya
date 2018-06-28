<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Resource_Abstract_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	public function massDelete()
	{
		$listIds = array();
		foreach($this as $item){
			$listIds[] = $item->getId();
		}
		$model = Mage::getModel($this->getModelName());
		$model->beforeMassDeleteAll($listIds, $this);
		$this->walk('onBeforeMassDelete', array('listIds'=>$listIds, 'collection'=>$this));
		$this->getResource()->massDelete($listIds);
		$this->walk('onAfterMassDelete', array('listIds'=>$listIds, 'collection'=>$this));
		$model->afterMassDeleteAll($listIds, $this);
	}


	/**
	 * Get SQL for get record count
	 *
	 * @return Varien_Db_Select
	 */
	public function getSelectCountSql()
	{
		$this->_renderFilters();

		$countSelect = clone $this->getSelect();
		$countSelect->reset(Zend_Db_Select::ORDER);
		$countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
		$countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
		$countSelect->reset(Zend_Db_Select::COLUMNS);

		if(count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
			$countSelect->reset(Zend_Db_Select::GROUP);
			$countSelect->distinct(true);
			$group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
			$countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
		} else {
			$countSelect->columns('COUNT(*)');
		}
		return $countSelect;
	}
}