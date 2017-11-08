<?php
/**
 * Class for `Rete Form` logic.
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.0.0
 * @license   GPL-3.0+
 * @copyright 2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'TM_Dashboard_Rate' not exists.
if ( ! class_exists( 'TM_Dashboard_Rate' ) ) {

	/**
	 * Interface management class.
	 */
	class TM_Dashboard_Rate {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of `Cherry_Interface_Builder` class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $builder = null;

		/**
		 * REST route for reviews.
		 *
		 * @since 1.0.3
		 * @var string
		 */
		private $reviews_url = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/v1/reviews';

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 9 );
			add_action( 'tm_dashboard_add_section', array( $this, 'build_section' ), 10, 2 );
		}

		/**
		 * Register javascripts.
		 *
		 * @since 1.0.3
		 */
		public function enqueue_assets( $hook ) {
			wp_enqueue_script(
				'jquery-validation',
				tm_dashboard()->plugin_url( 'admin/assets/js/jquery-validation/jquery.validate.min.js' ),
				array( 'jquery' ),
				'1.15.0',
				true
			);
		}

		/**
		 * Fire module initialization.
		 *
		 * @since 1.0.0
		 */
		public function build_section( $builder, $plugin ) {
			$plugin->get_core()->init_module( 'cherry-js-core' );
			$plugin->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_rate_form_id',
				'action'       => 'tm_rate_form_id',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'send' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Unable to process the request without nonce or server error', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, something wrong. Try later.', 'tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'Your review are send successfully','tm-dashboard' ),
				),
			) );

			$builder->register_section( array(
				'rate-us' => array(
					'title' => esc_html__( 'Rate us', 'tm-dashboard' ),
					'class' => 'tm-dashboard-section tm-dashboard-section--rate-us',
					'view'  => $plugin->plugin_dir( 'admin/views/section.php' ),
				),
			) );

			$builder->register_html( array(
				'rate-us-child' => array(
					'parent' => 'rate-us',
					'html'   => $this->get_rate_form(),
				),
			) );
		}

		/**
		 * Retrieve a HTML for section.
		 *
		 * @since  1.0.3
		 * @return string
		 */
		public function get_rate_form() {
			ob_start();

			include tm_dashboard()->plugin_dir( 'admin/views/rate-form.php' );

			return ob_get_clean();
		}

		/**
		 * Handler for save options.
		 *
		 * @since 1.0.3
		 */
		public function send() {
			$result = array(
				'success' => false,
			);

			if ( empty( $_POST['data'] ) ) {
				return $result;
			}

			$body = wp_parse_args( $_POST['data'], array(
				'tm_rate_author_url' => esc_url( home_url( '/' ) ),
			) );

			$args = array(
				'method' => 'POST',
				'body'   => $body,
			);

			$response = wp_remote_post( $this->reviews_url, $args );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return $result;
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				return $result;
			}

			$result['success'] = true;

			return $result;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.3
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
}

if ( ! function_exists( 'tm_dashboard_rate' ) ) {

	/**
	 * Returns instanse of the interface class.
	 *
	 * @since  1.0.3
	 * @return object
	 */
	function tm_dashboard_rate() {
		return TM_Dashboard_Rate::get_instance();
	}
}

tm_dashboard_rate();
