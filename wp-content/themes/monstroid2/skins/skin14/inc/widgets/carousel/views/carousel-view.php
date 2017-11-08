<?php
/**
 * Template part to display Carousel widget.
 *
 * @package Monstroid2
 * @subpackage widgets
 */
?>

<div class="swiper-slide-inner inner">
	<?php $this->utility->meta_data->get_terms( array(
			'visible' => true,
			'type'    => 'category',
			'icon'    => '',
			'before'  => '<div class="post__cats category">',
			'after'   => '</div>',
			'echo'    => true,
	) );
	?>
	<?php echo $image; ?>
	<div class="content-wrapper">
		<header class="entry-header">
			<?php echo $title; ?>
		</header>
		<div class="entry-meta">
			<?php echo $author; ?>
			<?php echo $date; ?>
		</div>
		<div class="entry-content">
			<?php echo $content; ?>
		</div>
		<footer class="entry-footer">
			<?php echo $more_button; ?>
		</footer>
	</div>
</div>
