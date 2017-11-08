<?php
/**
 * Tm-mega-menu hooks.
 *
 * @package Monstroid2
 */

// Mega menu mobile data.
add_filter( 'tm_mega_menu_mobile_button', '__return_true' );

// Disable mega menu plugin when style-3 or style-7 header layout.
add_filter( 'wp_nav_menu_args', 'monstroid2_disable_mega_menu', 1000 );

// Add animation option to tm-mega-menu.
add_filter( 'tm_mega_menu_options', 'monstroid2_add_animation_mega_menu_option' );

// Set default value mega-menu settings.
add_filter( 'tm_mega_menu_options', 'monstroid2_set_default_value_tm_mega_menu_settings' );

// Set theme default parent css selector.
add_filter( 'tm_mega_menu_default_parent', 'monstroid2_set_tm_mega_menu_default_parent_css_selector' );

/**
 * Disable mega menu plugin when style-3 or style-7 header layout
 *
 * @param array $args Menu arguments.
 *
 * @return array
 */
function monstroid2_disable_mega_menu( $args = array() ) {
	$header_layout_type = get_theme_mod( 'header_layout_type', monstroid2_theme()->customizer->get_default( 'header_layout_type' ) );

	if ( has_nav_menu( 'main' ) && ( 'style-3' === $header_layout_type || 'style-7' === $header_layout_type ) ) {
		$args['walker'] = '';
	}

	return $args;
}

/**
 * Add animation option to tm-mega-menu.
 *
 * @param array $menu_options Mega menu options.
 *
 * @return array
 */
function monstroid2_add_animation_mega_menu_option( $menu_options = array() ) {

	$menu_options['tm-mega-menu-effect']['callback_args']['options']['slide-bottom'] = esc_html__( 'Slide from bottom', 'monstroid2' );

	return $menu_options;
}

/**
 * Set default value mega-menu settings.
 *
 * @param array $menu_options Mega menu options.
 *
 * @return array
 */
function monstroid2_set_default_value_tm_mega_menu_settings( $menu_options = array() ) {

	// Set default mobile trigger.
	$menu_options['tm-mega-menu-mobile-trigger']['callback_args']['value'] = 767;

	return $menu_options;
}

/**
 * Set theme default parent css selector.
 *
 * @param string $selector Default parent css selector.
 *
 * @return string
 */
function monstroid2_set_tm_mega_menu_default_parent_css_selector( $selector = '' ) {
	return '.header-container_wrap';
}
