<?php
/**
 * Represents the view for the TemplateMonster dashboard.
 *
 * @package    TM_Dashboard
 * @subpackage Views
 * @author     Cherry Team
 * @version    1.0.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<div class="tm-dashboard tm-dashboard-wrap">
	<h1 class="tm-dashboard__title"><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="tm-dashboard__body">
		<?php tm_dashboard_interface()->render(); ?>
	</div>
</div>
