<?php
/**
 * Skin13 functions, hooks and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Monstroid2
 */

// Change comment template.
add_filter( 'monstroid2_comment_template_part_slug', 'monstroid2_skin13_comment_template_part_slug' );

// Change carousel-widget template.
add_filter( 'monstroid2_carousel_widget_view_dir', 'monstroid2_skin13_carousel_widget_view_slug' );

// Set specific content classes.
add_filter( 'monstroid2_content_classes', 'monstroid2_skin13_set_specific_content_classes' );

/**
 * Change comment template.
 *
 * @return string
 */
function monstroid2_skin13_comment_template_part_slug() {

	return 'skins/skin2/template-parts/comment';
}

/**
 * Change carousel-widget template.
 *
 * @return string
 */
function monstroid2_skin13_carousel_widget_view_slug() {

	return 'skins/skin2/inc/widgets/carousel/views/carousel-view.php';
}


/**
 * Set specific content classes for blog listing
 */
function monstroid2_skin13_set_specific_content_classes( $layout_classes ) {
	$sidebar_position = get_theme_mod( 'sidebar_position' );

	if ( ( 'fullwidth' === $sidebar_position && is_single() && ! is_singular( array( 'product', 'mp_menu_item' ) ) ) ) {
		$layout_classes = array( 'col-xs-12', 'col-md-12', 'col-xl-12' );
	}

	return $layout_classes;
}