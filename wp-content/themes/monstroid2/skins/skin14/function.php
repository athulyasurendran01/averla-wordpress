<?php
/**
 * Skin14 functions, hooks and definitions.
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Monstroid2
 */

// Change comment template.
add_filter( 'monstroid2_comment_template_part_slug', 'monstroid2_skin14_comment_template_part_slug' );

// Change carousel-widget template.
add_filter( 'monstroid2_carousel_widget_view_dir', 'monstroid2_skin14_carousel_widget_view_slug' );

// Change featured posts widget template.
add_filter( 'monstroid2_featured_posts_block_widget_view_dir', 'monstroid2_skin14_featured_posts_block_widget_view_dir' );

// Change News smart box full template.
add_filter( 'monstroid2_news_smart_box_full_view_dir', 'monstroid2_skin14_news_smart_box_full_view_dir' );

// Change News smart box mini template.
add_filter( 'monstroid2_news_smart_box_mini_view_dir', 'monstroid2_skin14_news_smart_box_mini_view_dir' );

// Change Custom Posts template.
add_filter( 'monstroid2_custom_posts_widget_view_dir', 'monstroid2_skin14_custom_posts_widget_view_dir' );

//Change post template part slug
add_filter( 'monstroid2_post_template_part_slug', 'monstroid2_skin14_post_template_part_slug', 10, 2 );

//Make after content fullwidth area not global
add_filter( 'monstroid2_widget_area_default_settings', 'monstroid2_skin14_widget_area_default_settings' );

//Change image size at about widget
add_filter( 'monstroid2_about_widget_image_size', 'monstroid2_skin14_about_widget_image_size' );

add_action( 'monstroid2_before_loop', 'monstroid2_skin14_add_title_before_loop' );


/**
 * Change comment template.
 *
 * @return string
 */
function monstroid2_skin14_comment_template_part_slug( $slug ) {

	return 'skins/skin14/template-parts/comment';
}

/**
 * Change carousel-widget template.
 *
 * @return string
 */
function monstroid2_skin14_carousel_widget_view_slug( $view ) {

	return 'skins/skin14/inc/widgets/carousel/views/carousel-view.php';
}

/**
 * Change featured posts widget template.
 *
 * @return string
 */
function monstroid2_skin14_featured_posts_block_widget_view_dir( $view ) {
	return 'skins/skin14/inc/widgets/featured-posts-block/views/item.php';
}


/**
 * Change News smart box template.
 *
 * @return string
 */
function monstroid2_skin14_news_smart_box_full_view_dir( $view ) {
	return 'skins/skin14/inc/widgets/news-smart-box/views/full-view.php';
}

/**
 * Change News smart box template.
 *
 * @return string
 */
function monstroid2_skin14_news_smart_box_mini_view_dir( $view ) {
	return 'skins/skin14/inc/widgets/news-smart-box/views/mini-view.php';
}

/**
 * Change Custom Posts template.
 *
 * @return string
 */
function monstroid2_skin14_custom_posts_widget_view_dir( $view ) {

	return 'skins/skin14/inc/widgets/custom-posts/views/custom-post-view.php';
}

/**
 * Change post template part slug
 *
 * @return string
 */
function monstroid2_skin14_post_template_part_slug( $blog_post_template, $blog_layout_type ) {

	if ( 'default' !== $blog_layout_type ) {
		$blog_post_template = 'skins/skin14/template-parts/post/grid/content';
	} else {
		$blog_post_template = 'skins/skin14/template-parts/post/default/content';
	}

	return $blog_post_template;
}

/**
 * Make after content fullwidth area not global
 *
 */

function monstroid2_skin14_widget_area_default_settings( $args ) {
	$args['after-content-full-width-area']['is_global'] = false;
	$args['after-content-full-width-area']['conditional'] = array( 'is_home' );
	return $args;
}

/**
 * Change image size at about widget
 *
 * @return string
 */
function monstroid2_skin14_about_widget_image_size( $param ){
	return 'large';
}

function monstroid2_skin14_add_title_before_loop() {
	printf( '<h6 class="page-title">%s</h6>', esc_html__( 'Latest News', 'monstroid2' ) );
}
