<?php
/**
 * The template for displaying post type archive page ( mp_menu_item ).
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Monstroid2
 */

global $mprm_view_args;
mprm_get_taxonomy();

do_action( 'mprm-page-template-single-taxonomy-wrapper-before' );

$view = mprm_get_option( 'display_taxonomy', 'default' );

if ( 'default' === $view ) {
	locate_template( 'archive.php', true );
	return;
}

$mprm_view_args = taxonomy_settings();
$col            = (int) $mprm_view_args['col'];

?>
	<div class="<?php echo apply_filters( 'mprm-page-template-main-wrapper-class', 'mprm-main-wrapper' ) ?>">
		<div class="<?php echo apply_filters( 'mprm-page-template-wrapper-' . $view . '-taxonomy-class', 'mprm-taxonomy-items-' . $view . ' mprm-container mprm-category' ) ?> ">
			<div class="<?php echo apply_filters( 'mprm-page-template-items-wrapper-class', 'mprm-container mprm-page-template-items mprm-view-' . $view ) ?>">
				<?php if ( $view == 'simple-list' ){ ?>
				<div class="mprm-columns-count-<?php echo $col ?> mprm-all-items">
					<?php }

					foreach ( mprm_get_menu_items_by_term() as $term => $data ) {

						if ( in_array( $view, array( 'list', 'grid' ) ) ) {
							create_grid_by_posts( $data, $col );
						} elseif ( $view == 'simple-list' ) {

							list( $last_key, $first_key ) = mprm_get_first_and_last_key( $data );

							foreach ( $data['posts'] as $post_key => $post ) {

								if ( $post_key === $first_key ) {
									$class = ' mprm-first';
								} elseif ( $post_key === $last_key ) {
									$class = ' mprm-last';
								} else {
									$class = '';
								}

								setup_postdata( $post );

								mprm_set_menu_item( $post->ID ); ?>

								<div class="<?php echo apply_filters( 'mprm-page-template-simple-view-column', 'mprm-simple-view-column' ) . $class; ?> ">
									<?php render_current_html(); ?>
								</div>
								<?php wp_reset_postdata();
							}
						}
					}
					if ( $view == 'simple-list' ){ ?>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>
	<div class="mprm-clear"></div>
<?php do_action( 'mprm-page-template-taxonomy-wrapper-after' );
