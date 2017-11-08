<?php
/**
 * Template part for displaying a maintenance page.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>
<section class="maintenance-section">
	<div class="page-content">
		<?php if ( monstroid2_get_the_post_maintenance_content() ) :

			echo apply_filters( 'the_content', monstroid2_get_the_post_maintenance_content() );

		else :

			printf( '<div class="maintenance-default-content"><h1>%s</h1><h2>%s</h2></div>',
				esc_html__( 'This page is under development.', 'monstroid2' ),
				esc_html__( 'We apologize for the inconvenience.', 'monstroid2' )
			);

		endif ?>
	</div>
</section>
