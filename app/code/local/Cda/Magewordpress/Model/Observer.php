<?php 
class Cda_Magewordpress_Model_Observer
{
	
	public function saveCmsCustomDetail(Varien_Event_Observer $observer)
    {   
		$page_id = array();
		$page_id = $observer->getEvent()->getDataObject()->getPageId();
		$model = Mage::getModel('cms/page')->load($page_id);
		$post = Mage::app()->getRequest()->getParams();
		$wordpressID = $post['wordpress_page_id'];
		//$model->setWordpressPageId(5)->save();
		
		
		/*$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql = 'UPDATE cms_page SET wordpress_page_id = '.$wordpressID.' WHERE page_id = '.$page_id.'';
		$connection->update($sql);*/
		
	}	
    public function cmsField($observer)
    {
        //get CMS model with data
		
        $model = Mage::registry('cms_page');
		
        //get form instance
		
        $form = $observer->getForm();
        //create new custom fieldset 'atwix_content_fieldset'
		
        $fieldset = $form->addFieldset('magewordpress_content_fieldset', array('legend'=>Mage::helper('cms')->__('Wordpress'),'class'=>'fieldset-wide'));
        
		//add new field
		
        $fieldset->addField('wordpress_page_id', 'select', array(
          'label'     => Mage::helper('cms')->__('Wordpress Page'),
          'name'      => 'wordpress_page_id',
          'options'   => $this->getPageList(),
		  'value'	  => $model->getWordpressPageId(),	
     ));
	 
	}
	
	public function getPageList(){
		$pagelist = array();
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = "SELECT * FROM wp_posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_date DESC";
		$rows = $connection->fetchAll($sql); 
		$pagelist[''] = Mage::helper('cms')->__("Please select wordpress page");
		if($rows){
			foreach($rows as $page){
				$pagelist[$page['ID']] =  Mage::helper('cms')->__($page['post_title']);
			}	
		}
		return $pagelist;
	}
	
}
