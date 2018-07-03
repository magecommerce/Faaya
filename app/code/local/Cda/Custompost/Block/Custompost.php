<?php   
class Cda_Custompost_Block_Custompost extends Mage_Core_Block_Template{   
	public function getCustomPost() {
     $args['post_type'] =  'trending';   
     $args['status'] = 'publish';     
     $args['posts_per_page'] = 10;   
     $postslist = get_posts( $args );  
     return $postslist;	
	}
    public function getCustomPostId() {
        $trendingId =  Mage::app()->getRequest()->getParam('postId') ;       
        if ($trendPost = get_page_by_path( $trendingId, OBJECT, 'trending' ) )
            $id = $trendPost->ID;
        else
            $id = 0;
       $trendPost = get_post($id);
       return $trendPost;
    }
}