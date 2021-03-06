<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $view = mphb_tmpl_get_room_type_view(); ?>

<?php if ( !empty( $view ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderViewListItemOpen	- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderViewTitle			- 20
	 */
	do_action( 'mphb_render_single_room_type_before_view' );
	?>

	<?php echo $view; ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderViewListItemClose	- 10
	 */
	do_action( 'mphb_render_single_room_type_after_view' );
	?>

<?php endif; ?>