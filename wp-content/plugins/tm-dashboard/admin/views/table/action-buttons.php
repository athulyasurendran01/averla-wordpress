<?php
/**
 * Action buttons in table with backups.
 *
 * @package    TM_Dashboard
 * @subpackage Views
 * @author     Cherry Team
 * @version    1.1.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( empty( $__data ) ) {
	return;
}
?>

<span class="tm-dashboard-hint tm-dashboard-hint--bottom">
	<span class="tm-dashboard-action-btn tm-dashboard-action-btn--restore <?php echo esc_attr( $__data['restore']['icon'] ); ?>"></span>
	<span class="tm-dashboard-hint__text">
		<?php echo esc_html( $__data['restore']['hint'] ); ?>
	</span>
</span>

<span class="tm-dashboard-hint tm-dashboard-hint--bottom">
	<span class="tm-dashboard-action-btn tm-dashboard-action-btn--download <?php echo esc_attr( $__data['download']['icon'] ); ?>"></span>
	<span class="tm-dashboard-hint__text">
		<?php echo esc_html( $__data['download']['hint'] ); ?>
	</span>
</span>

<span class="tm-dashboard-hint tm-dashboard-hint--bottom">
	<span class="tm-dashboard-action-btn tm-dashboard-action-btn--delete <?php echo esc_attr( $__data['delete']['icon'] ); ?>"></span>
	<span class="tm-dashboard-hint__text">
		<?php echo esc_html( $__data['delete']['hint'] ); ?>
	</span>
</span>
