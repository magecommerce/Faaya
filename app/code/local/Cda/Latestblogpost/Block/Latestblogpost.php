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
        $catId = Mage::app()->getRequest()->getParam('id');
        $catIdArray = $this->getPostByCategory($catId);

                $posts = Mage::getResourceModel('wordpress/post_collection');
                if(!empty($catIdArray)){
                    $posts->addFieldToFilter('ID', array('in' => $catIdArray));
                }
                $posts->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('top_story', '1')
                 ->setPageSize(6)
                ->load();
                return $posts;

    }
    public function getLatestTopPostCollection(){
    $catId = Mage::app()->getRequest()->getParam('id');
    $catIdArray = $this->getPostByCategory($catId);
     $posts = Mage::getResourceModel('wordpress/post_collection');
                if(!empty($catIdArray)){
                    $posts->addFieldToFilter('ID', array('in' => $catIdArray));
                }
                $posts->addIsViewableFilter()
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
        $catId = Mage::app()->getRequest()->getParam('id');
        $catIdArray = $this->getPostByCategory($catId);
      $posts = Mage::getResourceModel('wordpress/post_collection');
                if(!empty($catIdArray)){
                    $posts->addFieldToFilter('ID', array('in' => $catIdArray));
                }
                $posts->addIsViewableFilter()
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
        $catId = Mage::app()->getRequest()->getParam('id');
        $catIdArray = $this->getPostByCategory($catId);
        $posts = Mage::getResourceModel('wordpress/post_collection');
                if(!empty($catIdArray)){
                    $posts->addFieldToFilter('ID', array('in' => $catIdArray));
                }
                $posts->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addFieldToFilter('post_type', array('eq' => 'post'))
                ->setOrderByPostDate('desc')
                ->addMetaFieldToFilter('all_time_favourite', '1')
                 ->setPageSize(8)
                ->load();
                return $posts;
                //echo '<pre>';print_r($posts->getData());exit;
    }

    public function getPostByCategory($catId)
    {
        $catIdArray = array();
        if($catId){
            $getCatPosts = new WP_Query(
                        array('post_type'=>'post',
                              'posts_per_page' => -1,
                               'tax_query' => array(
                                                array(
                                                    'taxonomy' => 'category',
                                                    'field' => 'term_id',
                                                    'terms' => $catId
                                                    )
                                                )
                                )
                        );
            if($getCatPosts->have_posts()){
                while($getCatPosts->have_posts()){ $getCatPosts->the_post();
                    $catIdArray[] = get_the_ID();
                }
            }
        }
        return $catIdArray;

    }

    public function getRelatedPost()
    {
        $id = Mage::app()->getRequest()->getParam('id');

        $terms = get_the_terms($id,'category');


        $getCatPosts = new WP_Query(
                        array('post_type'=>'post',
                              'posts_per_page' => 3,
                              'orderby' => 'date',
                              'order' => 'DESC',
                              'post__not_in' => array($id),
                               'tax_query' => array(
                                                array(
                                                    'taxonomy' => 'category',
                                                    'field' => 'term_id',
                                                    'terms' => $terms[0]->term_id
                                                    )
                                                )
                                )
                        );
            return $getCatPosts;
    }
}
?>