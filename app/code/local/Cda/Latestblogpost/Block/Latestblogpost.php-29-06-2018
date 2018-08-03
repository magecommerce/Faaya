<?php
class Cda_Latestblogpost_Block_Latestblogpost extends Fishpig_Wordpress_Block_Post_List {

    public function getLatestHomePostCollection(){
      $collection = Mage::getResourceModel('wordpress/post_collection')
                 ->addIsViewableFilter()
                 ->addPostTypeFilter($this->getPostType())
                 ->addFieldToFilter('post_type', array('eq' => 'post'))
                 ->setOrderByPostDate('desc')
                 ->setPageSize(8)
                 ->load();
        return  $collection;    
    }
  public function getLatestBlogPostCollection(){
     /* $collection = Mage::getResourceModel('wordpress/post_collection')
                 ->addIsViewableFilter()
                 ->addPostTypeFilter($this->getPostType())
                 ->addFieldToFilter('post_type', array('eq' => 'post'))
                 ->setOrderByPostDate('desc')
                 ->setPageSize(6)
                 ->load();
        return  $collection;      */
        /*$meta_query_args = array(
            'relation' => 'OR', // Optional, defaults to "AND"
            array(
                'key'     => 'top_story',
                'value'   => '1',
                'compare' => '='
            )
        );
        $meta_query = new WP_Meta_Query( $meta_query_args );
        echo '<pre>';print_r($meta_query);exit;   */
        $posts = Mage::getResourceModel('wordpress/post_collection')
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('top_story', '1')
                 ->setPageSize(6)
                ->load();
                //echo '<pre>';print_r($posts->getData());
                return $posts;
    
    }
    public function getLatestTopPostCollection(){
     $posts = Mage::getResourceModel('wordpress/post_collection')
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('featured_top', '1')
                 ->setPageSize(1)
                ->load();   
                //echo '<pre>';print_r($posts->getData()); 
                return $posts;
    }
    public function getLatestBottomPostCollection(){
      $posts = Mage::getResourceModel('wordpress/post_collection')
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('featured_bottom', '1')
                 ->setPageSize(1)
                ->load(); 
                return $posts;
                //echo '<pre>';print_r($posts->getData());   
    }
    public function getAllTimeFavouritePostCollection(){
      $posts = Mage::getResourceModel('wordpress/post_collection')
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('all_time_favourite', '1')
                 ->setPageSize(8)
                ->load();   
                return $posts;
                //echo '<pre>';print_r($posts->getData());exit; 
    }
}  
?>    