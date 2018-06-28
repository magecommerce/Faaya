<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
class Cda_Latestblogpost_Block_View extends Fishpig_Wordpress_Block_Post_Abstract
{
	public function getPostFeatureViewTemplate()
	{
        $post = $this->getPost();
        return $post;
	}
}
