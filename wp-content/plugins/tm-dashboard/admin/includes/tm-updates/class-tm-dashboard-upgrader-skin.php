<?php
/**
 * Upgrader API: TM_Dashboard_Upgrader_Skin class
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.1.0
 * @license   GPL-3.0+
 * @copyright 2012-2017, Cherry Team
 */

if ( ! class_exists( 'WP_Upgrader_Skin' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php' );
}

/**
 * Upgrader Skin for TM Dashboard Restores.
 *
 * @since 1.1.0
 * @see WP_Upgrader_Skin
 */
class TM_Dashboard_Upgrader_Skin extends WP_Upgrader_Skin {

	/**
	 * Class constructor.
	 *
	 * @since 1.1.0
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * Output markup after installation processed.
	 */
	public function after() {}

	/**
	 *  Output header markup.
	 */
	public function header() {}

	/**
	 *  Output footer markup.
	 */
	public function footer() {}

	/**
	 *
	 * @since 1.1.0
	 * @param string $string
	 */
	public function feedback( $string ) {}
}
