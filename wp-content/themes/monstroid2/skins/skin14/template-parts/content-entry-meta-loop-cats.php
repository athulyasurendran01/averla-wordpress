<?php
/**
 * Template part for displaying entry-meta-cats.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>
<?php $utility = monstroid2_utility()->utility; ?>

<?php if ( 'post' === get_post_type() ) : ?>
		<?php $cats_visible = monstroid2_is_meta_visible( 'blog_post_categories', 'loop' );

		$utility->meta_data->get_terms( array(
			'visible'   => $cats_visible,
			'type'      => 'category',
			'before'    => '<span class="post__cats">',
			'after'     => '</span>',
			'echo'      => true,
		) );
		?>

<?php endif; ?>
