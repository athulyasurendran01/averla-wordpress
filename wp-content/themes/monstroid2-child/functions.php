<?php
/**
 * Monstroid2 Child functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Monstroid2
 */
add_action( 'wp_enqueue_scripts', 'monstroid2_child_theme_enqueue_styles', 20 );

/**
 * Enqueue styles.
 */
function monstroid2_child_theme_enqueue_styles() {

	$parent_style = 'monstroid2-theme-style';

	wp_enqueue_style( $parent_style,
		get_template_directory_uri() . '/style.css',
		array( 'font-awesome', 'material-icons', 'magnific-popup', 'linear-icons', 'material-design' )
	);

	wp_enqueue_style( 'monstroid2-child-theme-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get( 'Version' )
	);
}
