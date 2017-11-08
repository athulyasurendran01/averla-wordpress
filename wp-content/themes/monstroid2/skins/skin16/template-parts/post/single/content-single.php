<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php $utility = monstroid2_utility()->utility; ?>
	<?php $single_post_type = get_theme_mod( 'single_post_type', monstroid2_theme()->customizer->get_default( 'single_post_type' ) ); ?>

	<?php if ( 'modern' !== $single_post_type ) { ?>
		<?php get_template_part( 'skins/skin16/template-parts/content-entry-meta-single-header' ); ?>

		<header class="entry-header">

			<?php $utility->attributes->get_title( array(
					'class' => 'entry-title',
					'html'  => '<h3 %1$s>%4$s</h3>',
					'echo'  => true,
				) );
			?>

		</header><!-- .entry-header -->

		<?php get_template_part( 'skins/skin16/template-parts/content-entry-meta-single-footer' ); ?>
	<?php } ?>

	<?php monstroid2_ads_post_before_content() ?>

	<?php if ( 'modern' !== $single_post_type ) { ?>

		<figure class="post-thumbnail">
			<?php $size = monstroid2_post_thumbnail_size(); ?>

			<?php $utility->media->get_image( array(
				'size'        => $size['size'],
				'html'        => '<img class="post-thumbnail__img wp-post-image" src="%3$s" alt="%4$s">',
				'placeholder' => false,
				'echo'        => true,
				) );
			?>
		</figure><!-- .post-thumbnail -->

	<?php } ?>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links__title">' . esc_html__( 'Pages:', 'monstroid2' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span class="page-links__item">',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'monstroid2' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php monstroid2_share_buttons( 'single' ); ?>
		<?php do_action( 'cherry_trend_posts_display_rating' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
