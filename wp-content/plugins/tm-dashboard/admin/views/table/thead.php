<?php
/**
 * Table header.
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

<thead>
	<tr>
		<th><?php esc_html_e( '#', 'tm-dashboard' ); ?></th>
		<th><?php esc_html_e( 'Backup Date', 'tm-dashboard' ); ?></th>
		<th><?php esc_html_e( 'Version', 'tm-dashboard' ); ?></th>
		<th><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'tm-dashboard' ); ?></span></th>
	</tr>
</thead>
