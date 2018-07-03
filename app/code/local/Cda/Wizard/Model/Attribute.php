<?php
class Cda_Wizard_Model_Attribute extends Mage_Core_Model_Abstract{
    protected $_path;
    protected $_connectionWrite;

    public function __construct()
    {
        $this->_path = 'wizardxml/widget.xml';
        $this->_connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_connectionRead = Mage::getModel('core/resource')->getConnection('core_read');
    }

   public function importAttribute()
   {
        $xmlObj = new Varien_Simplexml_Config($this->_path);
        $xmlData = $xmlObj->getNode();
        $count = 0;
        foreach ($xmlData->attribute as $value) {
            $value = (array)$value;

            if($id = $this->checkExistingRecord($value,'wizardattribute')){
                $this->updateData($value,$id,'wizardoptions');
                continue;
            }

            $count++;
            $inData = array('code'=>$value['code'],'title'=>$value['title'],'type'=>$value['type'],'tooltip'=>$value['tooltip']);
            $aid = $this->insertData($inData, 'wizardattribute','id');
            $option = explode(',', $value['options']);
            foreach ($value['option'] as $opt) {
              $opt = (array)$opt;
                if($opt != ''){
                    $this->insertData(array('attr_id'=>$aid,'value'=>$opt['title'],'image'=>$opt['image']), 'wizardoptions','id');
                }
            }
        }
        echo 'Total '.$count.' attribute imported';
   }

   public function insertData($insertArr, $tableName,$primaryKey)
    {
        $this->_connectionWrite->beginTransaction();
        $this->_connectionWrite->insert($tableName, $insertArr);
        $this->_connectionWrite->commit();
        return $this->getLastInsertId($tableName,$primaryKey);
    }


    public function updateData($insertArr,$aid,$tableName)
    {
      $connection = Mage::getSingleton('core/resource')
      ->getConnection('core_write');
      foreach ($insertArr['option'] as $value) {
        $value = (array)$value;
        $connection->beginTransaction();
        $where = array();
        $where[] = $connection->quoteInto('attr_id =?', $aid);
        $where[] = $connection->quoteInto('value =?', $value['title']);
        $connection->update($tableName, array('image'=>$value['image']), $where);
        $connection->commit();
      }
    }

   public function getLastInsertId($tableName, $primaryKey)
   {
        $result = $this->_connectionRead->raw_fetchRow("SELECT MAX(`{$primaryKey}`) as LastID FROM `{$tableName}`");
        return $result['LastID'];
   }

   public function checkExistingRecord($arr,$tableName)
   {

        $query = 'SELECT id FROM ' . $tableName . ' WHERE code = "'.$arr['code'].'" and type = "'.$arr['type'].'" LIMIT 1';
        $data = $this->_connectionRead->fetchOne($query);
        return $data;
   }
}
