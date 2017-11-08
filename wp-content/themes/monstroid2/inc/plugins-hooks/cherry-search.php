<?php
/**
 * Cherry-search hooks.
 *
 * @package Monstroid2
 */

// Modify cherry-search button icon
add_filter( 'cherry_search_button_icon', 'monstroid2_modify_cherry_search_button_icon' );

// Modify cherry-search image placeholder
add_filter( 'cherry_search_placeholder', 'monstroid2_modify_cherry_search_image_placeholder' );

/**
 * Modify cherry-search button icon.
 *
 * @param array $icon_set Icon set.
 *
 * @return array
 */
function monstroid2_modify_cherry_search_button_icon( $icon_set = array() ) {

	$icon_set = array(
		'icon_set'    => 'monstroid2LinearIcons',
		'icon_css'    => esc_url( MONSTROID2_THEME_URI . '/assets/css/linearicons.css' ),
		'icon_base'   => 'linearicon',
	);

	return $icon_set;
}

/**
 * Modify cherry-search image placeholder
 *
 * @param array $args Image placeholder arguments.
 *
 * @return array
 */
function monstroid2_modify_cherry_search_image_placeholder( $args = array() ) {

	$args['title'] = get_bloginfo( 'name' );

	return $args;
}