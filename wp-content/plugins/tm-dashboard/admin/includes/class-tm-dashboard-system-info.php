<?php
/**
 * Class for `System Info` logic.
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

// If class 'TM_Dashboard_System_Info' not exists.
if ( ! class_exists( 'TM_Dashboard_System_Info' ) ) {

	/**
	 * Interface management class.
	 */
	class TM_Dashboard_System_Info {

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
			add_action( 'tm_dashboard_add_section', array( $this, 'build_section' ), 20, 2 );
		}

		/**
		 * Register and output section.
		 *
		 * @since 1.0.3
		 */
		public function build_section( $builder, $plugin ) {
			$builder->register_section( array(
				'system-info' => array(
					'title' => esc_html__( 'System Information', 'tm-dashboard' ),
					'class' => 'tm-dashboard-section tm-dashboard-section--system-info',
					'view'  => $plugin->plugin_dir( 'admin/views/section.php' ),
				),
			) );

			$builder->register_html( array(
				'system-info-child' => array(
					'parent' => 'system-info',
					'html'   => $this->get_system_info(),
				),
			) );
		}

		/**
		 * Retrieve a HTML for section.
		 *
		 * @since  1.0.3
		 * @return string
		 */
		public function get_system_info() {
			$return = '';

			// Site info.
			$site_info = TM_Dashboard_Tools::get_site_info();

			if ( ! empty( $site_info ) ) {

				$list = '';

				foreach ( $site_info as $info ) {
					$list .= sprintf( '<li>%s: %s</li>', esc_html( $info['name'] ), esc_html( $info['value'] ) );
				}

				$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			}

			// Theme.
			$theme_info = TM_Dashboard_Tools::get_theme_info();

			if ( ! empty( $theme_info ) ) {

				$list = '';

				foreach ( $theme_info as $info ) {
					$list .= sprintf( '<li>%s: %s</li>', esc_html( $info['name'] ), esc_html( $info['value'] ) );
				}

				$return .= sprintf( '<h5>%s</h5>', esc_html__( 'Theme', 'tm-dashboard' ) );
				$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			}

			// Server info.
			$server_params = TM_Dashboard_Tools::get_server_params();

			if ( ! empty( $server_params ) ) {

				$list = '';

				foreach ( $server_params as $param ) {
					$list .= sprintf( '<li>%s: %s</li>', esc_html( $param['name'] ), $param['value'] );
				}

				$return .= sprintf( '<h5>%s</h5>', esc_html__( 'Server', 'tm-dashboard' ) );
				$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			}

			// Plugins.
			$installed_plugins = TM_Dashboard_Tools::get_installed_plugins();

			// Active plugins.
			if ( ! empty( $installed_plugins['active'] ) ) {

				$list = '';

				foreach ( $installed_plugins['active'] as $plugin ) {
					$list .= sprintf( '<li>%s: %s</li>', $plugin['Name'], $plugin['Version'] );
				}

				$return .= sprintf( '<h5>%s</h5>', esc_html__( 'Active Plugins', 'tm-dashboard' ) );
				$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			}

			// Inactive plugins.
			// if ( ! empty( $installed_plugins['inactive'] ) ) {

			// 	$list = '';

			// 	foreach ( $installed_plugins['inactive'] as $plugin ) {
			// 		$list .= sprintf( '<li>%s: %s</li>', $plugin['Name'], $plugin['Version'] );
			// 	}

			// 	$return .= sprintf( '<h5>%s</h5>', esc_html__( 'Inactive Plugins', 'tm-dashboard' ) );
			// 	$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			// }

			// Installed themes.
			$themes       = TM_Dashboard_Tools::get_installed_themes();
			$active_theme = get_stylesheet();

			if ( ! empty( $themes ) ) {

				$list = '';

				foreach ( $themes as $theme_slug => $theme_data ) {

					if ( $active_theme == $theme_slug ) {
						continue;
					}

					$list .= sprintf( '<li>%s: %s</li>', $theme_data['Name'], $theme_data['Version'] );
				}

				$return .= sprintf( '<h5>%s</h5>', esc_html__( 'Installed Themes', 'tm-dashboard' ) );
				$return .= sprintf( '<ul class="tm-dashboard-section__list">%s</ul>', $list );
			}

			return $return;
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

if ( ! function_exists( 'tm_dashboard_system_info' ) ) {

	/**
	 * Returns instanse of the interface class.
	 *
	 * @since  1.0.3
	 * @return object
	 */
	function tm_dashboard_system_info() {
		return TM_Dashboard_System_Info::get_instance();
	}
}

tm_dashboard_system_info();
