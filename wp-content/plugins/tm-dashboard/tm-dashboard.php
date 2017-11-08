<?php
/**
 * Plugin Name: Jetimpex Dashboard
 * Plugin URI:  http://www.cherryframework.com/
 * Description: Dashboard for Template Monster themes.
 * Version:     1.1.0
 * Author:      Cherry Team
 * Text Domain: tm-dashboard
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.1.0
 * @license   GPL-3.0+
 * @copyright 2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `TM_Dashboard` doesn't exists yet.
if ( ! class_exists( 'TM_Dashboard' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class TM_Dashboard {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $core = null;

		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '1.1.0';

		/**
		 * Plugin folder URL.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_url = '';

		/**
		 * Plugin folder path.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_dir = '';

		/**
		 * Plugin slug.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_slug = 'tm-dashboard';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->inc();

			// Do this only on backend.
			if ( is_admin() ) {
				$this->hooks();
				$this->updater();
			}

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 1.0.3
		 */
		public function hooks() {
			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ), 1 );

			// Register stylesheet and javascript.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );

			// Load the installer core.
			add_action( 'after_setup_theme', require( trailingslashit( dirname( __FILE__ ) ) . 'cherry-framework/setup.php' ), 0 );

			add_action( 'after_setup_theme', array( $this, 'get_core' ),                 1 );
			add_action( 'after_setup_theme', array( 'Cherry_Core', 'load_all_modules' ), 2 );
			add_action( 'after_setup_theme', array( $this, 'load_interface' ),           3 );

			add_filter( 'extra_theme_headers', array( $this, 'add_extra_headers' ) );
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.3
		 */
		public function inc() {
			require_once $this->plugin_dir( 'public/includes/class-tm-dashboard-tools.php' );
			require_once $this->plugin_dir( 'public/includes/class-tm-dashboard-paths.php' );
			require_once $this->plugin_dir( 'public/includes/class-tm-dashboard-cron.php' );
			require_once $this->plugin_dir( 'public/includes/class-tm-theme-backup.php' );
		}

		/**
		 * Init updater.
		 *
		 * @since 1.0.0
		 */
		public function updater() {
			require_once $this->plugin_dir( 'admin/includes/class-cherry-update/class-cherry-plugin-update.php' );

			$updater = new Cherry_Plugin_Update();
			$updater->init( array(
				'version'         => $this->version,
				'slug'            => $this->plugin_slug,
				'repository_name' => $this->plugin_slug,
				'product_name'    => 'templatemonster',
			) );
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		public function lang() {
			load_plugin_textdomain( 'tm-dashboard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Register stylesheet and javascript.
		 *
		 * @since 1.0.0
		 */
		public function register_assets( $hook ) {
			wp_register_style(
				'tm-dashboard',
				$this->plugin_url( 'admin/assets/css/min/admin.min.css' ),
				array(),
				$this->version
			);

			wp_register_style(
				'tm-dashboard-notification',
				$this->plugin_url( 'admin/assets/css/min/admin-notification.min.css' ),
				array(),
				$this->version
			);

			wp_register_script(
				'tm-dashboard',
				$this->plugin_url( 'admin/assets/js/min/admin.min.js' ),
				array( 'cherry-js-core', 'cherry-handler-js', 'wp-util' ),
				$this->version,
				true
			);

			wp_localize_script( 'tm-dashboard', 'tmDashbordVars', array(
				'messages' => array(
					'deleteBackup'  => esc_html__( 'Are you sure that you want to delete backup?', 'tm-dashboard' ),
					'restoreBackup' => esc_html__( 'All current theme files will be overwritten', 'tm-dashboard' ),
				),
			) );
		}

		/**
		 * Loads the core functions. These files are needed before loading anything else in the
		 * plugin because they have required functions for use.
		 *
		 * @since 1.0.0
		 * @return object
		 */
		public function get_core() {

			/**
			 * Fires before loads the plugin's core.
			 *
			 * @since 1.0.0
			 */
			do_action( 'tm_dashboard_core_before' );

			global $chery_core_version;

			if ( null !== $this->core ) {
				return $this->core;
			}

			if ( 0 < sizeof( $chery_core_version ) ) {
				$core_paths = array_values( $chery_core_version );
				require_once( $core_paths[0] );
			} else {
				die( 'Class Cherry_Core not found' );
			}

			$this->core = new Cherry_Core( array(
				'base_dir' => $this->plugin_dir( 'cherry-framework' ),
				'base_url' => $this->plugin_url( 'cherry-framework' ),
				'modules'  => array(
					'cherry-js-core' => array(
						'autoload' => false,
					),
					'cherry-ui-elements' => array(
						'autoload' => false,
					),
					'cherry-interface-builder' => array(
						'autoload' => false,
					),
					'cherry-handler' => array(
						'autoload' => false,
					),
				),
			) );

			return $this->core;
		}

		/**
		 * Loads interface.
		 *
		 * @since 1.0.0
		 */
		public function load_interface() {
			require_once $this->plugin_dir( 'admin/includes/class-tm-dashboard-interface.php' );
		}

		/**
		 * Generate a unique key.
		 *
		 * @since 1.1.0
		 * @return string
		 */
		protected function generate_key() {
			$keys = array( ABSPATH, time() );
			$constants = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT', 'SECRET_KEY' );

			foreach ( $constants as $constant ) {
				if ( defined( $constant ) ) {
					$keys[] = constant( $constant );
				}
			}

			shuffle( $keys );
			$key = md5( serialize( $keys ) );
			update_option( 'tm_dashboard_generated_key', $key );
		}

		/**
		 * Add new extra headers `DocumentationID` - TemplateMonster unique.
		 *
		 * @since 1.0.0
		 * @param array
		 */
		public function add_extra_headers( $headers ) {
			$headers[] = 'DocumentationID';

			return $headers;
		}

		/**
		 * Get plugin URL (or some plugin dir/file URL)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_url . $path;
			}

			return $this->plugin_url;
		}

		/**
		 * Get plugin dir path (or some plugin dir/file path)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_dir( $path = null ) {

			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_dir . $path;
			}

			return $this->plugin_dir;
		}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		public function activation() {
			/**
			 * Fire when plugin is activated.
			 *
			 * @since 1.1.0
			 */
			do_action( 'tm_dashboard_activation' );

			$this->generate_key();
			TM_Dashboard_Paths::get_instance()->protect_path( 'reset' );
		}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		public function deactivation() {
			/**
			 * Fire when plugin is deactivated.
			 *
			 * @since 1.1.0
			 */
			do_action( 'tm_dashboard_deactivation' );

			$this->cleaner();
		}

		/**
		 * Delete all transients and key.
		 *
		 * @since 1.1.0
		 */
		public function cleaner() {
			delete_option( 'tm_dashboard_generated_key' );
			delete_transient( 'tm_backup_archives' );
		}

		/**
		 * Returns the instance.
		 *
		 * @since 1.0.0
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

if ( ! function_exists( 'tm_dashboard' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_dashboard() {
		return TM_Dashboard::get_instance();
	}
}

tm_dashboard();
