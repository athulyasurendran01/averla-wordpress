<?php
/**
 * Template part for displaying entry-meta.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>
<?php $utility = monstroid2_utility()->utility; ?>

<?php if ( 'post' === get_post_type() ) : ?>

	<div class="entry-meta entry-meta-header">

		<?php $cats_visible = monstroid2_is_meta_visible( 'single_post_categories', 'single' );

		$utility->meta_data->get_terms( array(
			'visible'   => $cats_visible,
			'type'      => 'category',
			'delimiter' => ', ',
			'before'    => '<div class="post__cats h6-style">',
			'after'     => '</div>',
			'echo'      => true,
		) );
		?>

	</div><!-- .entry-meta -->

<?php endif; ?>
