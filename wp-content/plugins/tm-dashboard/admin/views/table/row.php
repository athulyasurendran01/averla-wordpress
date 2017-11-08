<?php
/**
 * Table row.
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

$autobackup = ! empty( $__data['hint'] ) ? 'true' : 'false';
?>

<tr data-archivename="<?php echo esc_attr( $__data['archivename'] ); ?>" data-autobackup="<?php echo esc_attr( $autobackup ); ?>">
	<td class="tm-dashboard-table__td tm-dashboard-table__td--numbs">
		<?php echo wp_kses_post( $__data['hint'] ); ?>&nbsp;
	</td>
	<td><?php echo esc_html( $__data['date'] ); ?></td>
	<td><?php echo esc_html( $__data['version'] ); ?></td>
	<td class="tm-dashboard-table__td tm-dashboard-table__td--actions"><?php echo wp_kses_post( $__data['actions'] ); ?></td>
</tr>
