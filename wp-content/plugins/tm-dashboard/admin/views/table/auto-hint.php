<?php
/**
 * Table auto hint.
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

?>

<span class="tm-dashboard-hint tm-dashboard-hint--left">
	<span class="tm-dashboard-hint__icon tm-dashboard-icon tm-dashboard-icon--auto"></span>
	<span class="tm-dashboard-hint__text"><?php echo esc_html__( 'Scheduled Backup', 'tm-dashboard' ); ?></span>
</span>
