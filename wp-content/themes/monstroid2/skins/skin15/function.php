<?php
/**
 * Skin15 functions, hooks and definitions.
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Monstroid2
 */

// Add new services list template
add_filter( 'cherry_services_listing_templates_list', 'monstroid2_skin15_cherry_services_listing_templates_list' );

// Change single template part slug
add_filter( 'monstroid2_single_post_template_part_slug', 'monstroid2_skin15_single_post_template_part_slug', 10, 2 );

// Change single template modern header
add_filter( 'monstroid2_single_modern_header_template_part_slug', 'monstroid2_skin15_single_modern_header_template_part_slug' );

// Change post template part slug
add_filter( 'monstroid2_post_template_part_slug', 'monstroid2_skin15_post_template_part_slug', 10, 2 );

/**
 * Add new services list template
 */
function monstroid2_skin15_cherry_services_listing_templates_list( $tmpl ) {

	$tmpl['media-icon-skin-15'] = 'media-icon-skin-15.tmpl';
	return $tmpl;
}

/**
 * Change single modern header template part slug
 *
 * @return string
 */
function monstroid2_skin15_single_modern_header_template_part_slug($single_modern_header_template) {
	$single_modern_header_template = 'skins/skin15/template-parts/post/single/content-single-modern-header';

	return $single_modern_header_template;
}

/**
 * Change single post template part slug
 *
 * @return string
 */
function monstroid2_skin15_single_post_template_part_slug( $single_post_template, $single_post_type ) {
	if ( 'modern' === $single_post_type && is_singular( 'post' ) ) {
		$single_post_template = 'skins/skin15/template-parts/post/single/content-single-modern';
	}
	else {
		$single_post_template = 'skins/skin15/template-parts/post/single/content-single';
	}

	return $single_post_template;
}

/**
 * Change post template part slug
 *
 * @return string
 */
function monstroid2_skin15_post_template_part_slug( $blog_post_template, $blog_layout_type ) {
	$blog_post_template = 'skins/skin15/template-parts/post/default/content';

	return $blog_post_template;
}

add_filter( 'monstroid2_specific_content_classes', 'monstroid2_skin15_specific_content_classes' );

function monstroid2_skin15_specific_content_classes( $classes ) {
	return array( 'col-xs-12', 'col-md-12', 'col-lg-8', 'col-lg-push-2' );
}
