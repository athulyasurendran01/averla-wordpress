<?php
/**
 * Represents the view for the `Helpful Links` section.
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
<ul class="tm-dashboard-section__list">
	<li><a href="http://documentation.templatemonster.com/projects/<?php echo esc_attr( $doc_id ); ?>/index.html" target="_blank" rel="nofollow"><?php esc_html_e( 'Theme Documentation', 'tm-dashboard' ); ?></a></li>
	<li><a href="https://support.template-help.com/index.php/Tickets/Submit" target="_blank" rel="nofollow"><?php esc_html_e( 'Ticket system', 'tm-dashboard' ); ?></a></li>
	<li><a href="http://www.cherryframework.com/" target="_blank" rel="nofollow"><?php esc_html_e( 'Cherry Framework', 'tm-dashboard' ); ?></a></li>
</ul>
