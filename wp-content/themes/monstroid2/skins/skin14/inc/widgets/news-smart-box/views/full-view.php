<?php
/**
 * Template part to display full-view news-smart-box widget.
 *
 * @package Monstroid2
 * @subpackage widgets
 */
?>
<div class="news-smart-box__item-inner">
	<div class="news-smart-box__item-header">
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
	</div>
	<div class="news-smart-box__item-content">
		<?php echo $title; ?>
		<div class="entry-meta">
			<?php echo $author; ?>
			<?php echo $date; ?>
			<?php echo $comments; ?>
		</div>
		<?php echo $excerpt; ?>
		<?php echo $more_btn; ?>
	</div>
</div>
