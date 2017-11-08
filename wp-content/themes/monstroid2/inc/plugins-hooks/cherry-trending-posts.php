<?php
/**
 * Cherry trending posts hooks.
 *
 * @package Monstroid2
 */

// Modify cherry-trend-posts image args.
add_filter( 'cherry_trend_posts_image_args', 'monstroid2_modify_cherry_trend_posts_image_args' );

// Cache fix.
add_filter( 'cherry_trend_posts_cache_fix', '__return_true' );

/**
 * Modify cherry-trend-posts image args.
 *
 * @param array $args Image arguments.
 *
 * @return array
 */
function monstroid2_modify_cherry_trend_posts_image_args( $args = array() ) {

	$args['size']        = 'post-thumbnail';
	$args['mobile-size'] = 'post-thumbnail';

	return $args;
}
