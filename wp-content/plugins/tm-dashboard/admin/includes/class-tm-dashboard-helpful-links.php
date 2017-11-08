<?php
/**
 * Class for `Helpful Links` logic.
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.0.3
 * @license   GPL-3.0+
 * @copyright 2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'TM_Dashboard_Helpful_Links' not exists.
if ( ! class_exists( 'TM_Dashboard_Helpful_Links' ) ) {

	/**
	 * Interface management class.
	 */
	class TM_Dashboard_Helpful_Links {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.3
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of `Cherry_Interface_Builder` class.
		 *
		 * @since 1.0.3
		 * @var object
		 */
		private $builder = null;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'tm_dashboard_add_section', array( $this, 'build_section' ), 30, 2 );
		}

		/**
		 * Register and output section.
		 *
		 * @since 1.0.3
		 */
		public function build_section( $builder, $plugin ) {
			$builder->register_section( array(
				'help-links' => array(
					'title' => esc_html__( 'Helpful Links', 'tm-dashboard' ),
					'class' => 'tm-dashboard-section tm-dashboard-section--help-links',
					'view'  => $plugin->plugin_dir( 'admin/views/section.php' ),
				),
			) );

			$builder->register_html( array(
				'help-links-child' => array(
					'parent' => 'help-links',
					'html'   => $this->get_helpful_links(),
				),
			) );
		}

		/**
		 * Retrieve a HTML for section.
		 *
		 * @since  1.0.3
		 * @return string
		 */
		public function get_helpful_links() {
			ob_start();

			$theme_data = get_file_data( get_stylesheet_directory() . '/style.css', array( 'TextDomain' => 'Text Domain' ), 'theme' );
			$doc_id     = ! empty( $theme_data['DocumentationID'] ) ? $theme_data['DocumentationID'] : $theme_data['TextDomain'];

			include tm_dashboard()->plugin_dir( 'admin/views/helpful-links.php' );

			return ob_get_clean();
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

if ( ! function_exists( 'tm_dashboard_helpful_links' ) ) {

	/**
	 * Returns instanse of the interface class.
	 *
	 * @since  1.0.3
	 * @return object
	 */
	function tm_dashboard_helpful_links() {
		return TM_Dashboard_Helpful_Links::get_instance();
	}
}

tm_dashboard_helpful_links();
