<?php
/**
 *
 * DO NOT EDIT THIS FILE
 * Any changes you make to this file will be lost
 * To customise things, create a file at wp-content/themes/fishpig/local.php
 * This file will not be deleted or overwritten and is automatically included at the end of this file
 *
 */

if (!function_exists('fishpig_setup')) {
	function fishpig_setup() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size(9999, 9999);
		
		add_theme_support( 'post-formats', array(
			'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'
		));
		
		show_admin_bar(false);
	}
}

add_action( 'after_setup_theme', 'fishpig_setup' );

function fishpig_widgets_init() {
	register_sidebar(array(
		'name' => __( 'Left Sidebar', 'fishpig' ),
		'id' => 'sidebar-1',
		'description' => 'Add widgets here to appear in your left Magento sidebar.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
	
	register_sidebar(array(
		'name' => __( 'Right Sidebar', 'fishpig' ),
		'id' => 'sidebar-2',
		'description' => 'Add widgets here to appear in your right Magento sidebar.',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
}

add_action( 'widgets_init', 'fishpig_widgets_init' );

if (!function_exists('fishpig_comment')):
function fishpig_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite><b class="fn">%1$s</b> %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

function fishpig_invalidate_cache( $post_id ) {
	// If this is just a revision, don't do anything
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Make an invalidation call to Magento
	$salt = get_option( 'fishpig_salt' );
	if (!$salt) {
		$salt = wp_generate_password( 64, true, true );
		update_option( 'fishpig_salt', $salt );
	}

	$nonce_tick = ceil(time() / ( 86400 / 2 ));

	$action = 'invalidate_' . $post_id;

	$nonce = substr( hash_hmac( 'sha256', $nonce_tick . '|fishpig|' . $action, $salt ), -12, 10 );

	wp_remote_get( home_url( '/wordpress/post/invalidate?id=' . $post_id . '&nonce=' . $nonce ) );
}

//add_action( 'save_post', 'fishpig_invalidate_cache' );

remove_filter('template_redirect', 'redirect_canonical');

add_filter('preview_post_link', 'fishpig_preview_post_link', 10, 2);

function fishpig_preview_post_link($pl, $post) {
	if (strpos($pl, 'nonce') !== false) {
		if (preg_match('/nonce=([a-z0-9]{10})/', $pl, $matches)) {
			$pl = str_replace($matches[1], substr(wp_hash(wp_nonce_tick()."|post_preview_{$post->ID}|0|", 'nonce'), -12, 10), $pl);
		}
	}
	return $pl . '&fishpig=' . time();
}

/* Include local.php*/
$localFile = __DIR__ . DIRECTORY_SEPARATOR . 'local.php';

if (is_file($localFile)) {
	include($localFile);
}



