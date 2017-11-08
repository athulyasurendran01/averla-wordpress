<?php
/**
 * Update/Install Plugin/Theme administration panel.
 *
 * @package    TM_Dashboard
 * @subpackage Class
 * @author     Cherry Team
 * @version    1.0.0
 * @license    GPL-3.0+
 * @copyright  2012-2017, Cherry Team
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class `TM_Remote_Query` doesn't exists yet.
if ( ! class_exists( 'TM_Remote_Query' ) ) {

	class TM_Remote_Query {

		/**
		 * This function performs the remote request.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return object
		 */
		public function get_request( $url ) {
			$request = wp_remote_get( $url, array() );

			if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ){
				return;
			}

			if ( empty( $request ) || empty( $request['body'] ) ) {
				return;
			}

			$release = json_decode( $request['body'], true );

			return $release;
		}
	}
}
