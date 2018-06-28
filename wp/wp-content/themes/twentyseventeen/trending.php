<?php
function trending_post_type() {
	$labels = array(
		'name'                  => _x( 'Trending Type', 'Post Type General Name', 'faaya' ),
		'singular_name'         => _x( 'Trending', 'Post Type Singular Name', 'faaya' ),
		'menu_name'             => __( 'Trending Type', 'faaya' ),
		'name_admin_bar'        => __( 'Trending', 'faaya' ),
		'parent_item_colon'     => __( 'Parent Item:', 'faaya' ),
		'all_items'             => __( 'All Trending', 'faaya' ),
		'add_new_item'          => __( 'Add New Trending', 'faaya' ),
		'add_new'               => __( 'Add New Trending', 'faaya' ),
		'new_item'              => __( 'New Trending', 'faaya' ),
		'edit_item'             => __( 'Edit Trending', 'faaya' ),
		'update_item'           => __( 'Update Trending', 'faaya' ),
		'view_item'             => __( 'View Trending', 'faaya' ),
		'search_items'          => __( 'Search Trending', 'faaya' ),
		'not_found'             => __( 'Not found', 'faaya' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'faaya' ),
		'items_list'            => __( 'Items list', 'faaya' ),
		'items_list_navigation' => __( 'Items list navigation', 'faaya' ),
		'filter_items_list'     => __( 'Filter items list', 'faaya' ),
	);
	$args = array(
		'label'                 => __( 'Trending', 'faaya' ),
		'description'           => __( 'Post Type Description', 'faaya' ),
		'labels'                => $labels,
		'supports'              => array( 'title','editor'),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,		
		'exclude_from_search'   => false,
		'capability_type'       => 'post',
	);
	register_post_type( 'trending', $args );
}
add_action( 'init', 'trending_post_type', 0 );

?>