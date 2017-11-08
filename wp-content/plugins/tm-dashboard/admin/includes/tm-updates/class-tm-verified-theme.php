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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class `Tm_Verified_Theme` doesn't exists yet.
if ( ! class_exists( 'Tm_Verified_Theme' ) ) {

	/**
	 * Tm_Verified_Theme class.
	 */
	class Tm_Verified_Theme extends Tm_Themes_Forms {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of Tm_Check_Theme_Update class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $check_update = null;

		/**
		 * Contains a link to the update.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string
		 */
		private $api_upd = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/get-update?template=%1$s&order_id=%2$s&update_version=%3$s';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->init_handlers();

			$this->check_update = new TM_Check_Themes();
		}

		/**
		 * Html render.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function init_handlers() {
			tm_dashboard()->get_core()->init_module( 'cherry-js-core' );
			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_verified_theme',
				'action'       => 'tm_verified_theme',
				'capability'   => 'manage_options',
				'type'         => 'GET',
				'callback'     => array( $this, 'verified_theme' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Unable to process the request without nonche or server error', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No right for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Stop CHEATING!!!', 'tm-dashboard' ),
					'access_is_allowed' => '',
				),
			) );
		}

		public function verified_theme() {

			if ( empty( $_GET['data'] ) ) {
				return;
			}

			$theme_data = array();

			foreach ( $_GET['data'] as $key => $value ) {
				$theme_data[ $value['name'] ] = $value['value'];
			}

			$theme_slug            = $theme_data['slug'];
			$theme                 = wp_get_theme( $theme_slug );
			$verified_themes       = get_option( 'verified_themes', false );
			$theme_data['version'] = $theme->get( 'Version' );
			$theme_data['message'] = esc_html__( 'Order details saved successfully.', 'tm-dashboard' );
			$theme_data['verify']  = true;
			$update                = $this->check_update->check_theme_update( $theme_data['product-id'], $theme_data['version'] );
			$theme_data['update']  = $update->version;

			$verify = $this->check_order_data( $theme_data['product-id'], $theme_data['order-id'], $theme_data['update'] );

			if ( true === $verify['error'] || ! $verify['download_url'] ) {
				$theme_data['message'] = esc_html__( 'There are no updates now. You will get notified.', 'tm-dashboard' );
				$theme_data['verify']  = false;

				return $theme_data;
			}

			if ( $verified_themes ) {
				$verified_themes[ $theme_slug ] = $theme_data;

			} else {
				$verified_themes = array(
					$theme_slug => $theme_data,
				);
			}

			$db_version_update = get_option( 'tm_updates_themes', array() );

			$db_version_update[ $theme_slug ] = array(
				'version' => $theme_data['version'],
				'update'  => $theme_data['update'],
			);

			update_option( 'tm_updates_themes', $db_version_update );
			update_option( 'verified_themes', $verified_themes );

			$theme_data['htmlForm'] = $this->get_theme_info_block( $theme_data );

			return $theme_data;
		}

		public function check_order_data( $template_id, $order_id, $version ) {
			$url     = sprintf( $this->api_upd, $template_id, $order_id, $version );
			$respons = $this->check_update->get_request( $url );
			$respons = $respons;

			return $respons;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}

	if ( ! function_exists( 'tm_verified_theme' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_verified_theme() {
			return Tm_Verified_Theme::get_instance();
		}
	}

	tm_verified_theme();
}
