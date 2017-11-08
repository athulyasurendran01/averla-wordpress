<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'posts-list__item card' ); ?>>

	<?php $utility       = monstroid2_utility()->utility;
	$size                = monstroid2_post_thumbnail_size( array( 'class_prefix' => 'post-thumbnail--' ) );
	?>

	<figure class="post-thumbnail">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php $utility->media->get_image( array(
				'size'        => 'monstroid2-skin16-769-431',
				'mobile_size' => 'monstroid2-thumb-l',
				'class'       => 'post-thumbnail__link ' . $size['class'],
				'html'        => '<a href="%1$s" %2$s><img class="post-thumbnail__img wp-post-image" src="%3$s" alt="%4$s" %5$s></a>',
				'placeholder' => false,
				'echo'        => true,
			) );
			?>
		<?php endif; ?>

		<?php do_action( 'cherry_post_format_link', array( 'render' => true ) ); ?>
	</figure><!-- .post-thumbnail -->

	<div class="post-list__item-content">
		<?php get_template_part( 'skins/skin16/template-parts/content-entry-meta-loop-header' ); ?>

		<header class="entry-header">
			<?php monstroid2_sticky_label(); ?>

			<?php $title_html = ( is_single() ) ? '<h3 %1$s>%4$s</h3>' : '<h4 %1$s><a href="%2$s" rel="bookmark">%4$s</a></h4>';

			$utility->attributes->get_title( array(
				'class' => 'entry-title',
				'html'  => $title_html,
				'echo'  => true,
			) );
			?>
		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php $blog_content = get_theme_mod( 'blog_posts_content', monstroid2_theme()->customizer->get_default( 'blog_posts_content' ) );
			$length             = ( 'full' === $blog_content ) ? -1 : 40;
			$content_visible    = ( 'none' !== $blog_content ) ? true : false;
			$content_type       = ( 'full' !== $blog_content ) ? 'post_excerpt' : 'post_content';

			$utility->attributes->get_content( array(
				'visible'      => $content_visible,
				'length'       => $length,
				'content_type' => $content_type,
				'echo'         => true,
			) );
			?>
		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<?php get_template_part( 'skins/skin16/template-parts/content-entry-meta-loop-footer' ); ?>

			<?php $btn_text = get_theme_mod( 'blog_read_more_text', monstroid2_theme()->customizer->get_default( 'blog_read_more_text' ) );
			$btn_visible    = $btn_text ? true : false;

			$utility->attributes->get_button( array(
				'visible' => $btn_visible,
				'class'   => 'link btn btn-primary',
				'text'    => $btn_text,
				'icon'    => '',
				'html'    => '<a href="%1$s" %3$s><span class="link__text">%4$s</span>%5$s</a>',
				'echo'    => true,
			) );
			?>

			<?php monstroid2_share_buttons( 'loop' ); ?>
		</footer><!-- .entry-footer -->
	</div><!-- .post-list__item-content -->

</article><!-- #post-## -->