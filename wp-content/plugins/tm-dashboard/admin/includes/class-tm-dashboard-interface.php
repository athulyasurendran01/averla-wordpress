<?php
/**
 * Re-build WordPress Admin menu for Jetimpex Dasboard and load interface.
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

// If class 'TM_Dashboard_Interface' not exists.
if ( ! class_exists( 'TM_Dashboard_Interface' ) ) {

	/**
	 * Interface management class.
	 */
	class TM_Dashboard_Interface {

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
		public $builder = null;

		/**
		 * Jetimpex menu item position.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		private $position = 101;

		/**
		 * Subpages list for Jetimpex menu item.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $subpages = array();

		/**
		 * The slug name to refer to dashboard menu.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $menu_slug = 'tm-dashboard';

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->includes();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'admin_menu', array( $this, 'register_new_pages' ) );
		}

		/**
		 * Loads additional files.
		 *
		 * @since 1.0.3
		 * @access public
		 * @return void
		 */
		public function includes() {
			require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-update-notices.php' );
			require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-remote-query.php' );
			require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-check-themes.php' );

			if ( $this->is_related_page( 'tm-dashboard' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				require_once tm_dashboard()->plugin_dir( 'admin/includes/class-tm-dashboard-rate.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/class-tm-dashboard-system-info.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/class-tm-dashboard-helpful-links.php' );

				$this->dashboard_page_modules();
				add_action( 'after_setup_theme', array( $this, 'register_section_hook' ) );
			}

			if ( $this->is_related_page( 'tm-updates' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-themes-forms.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-verified-theme.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-notification-popup.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-get-theme-update.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-backup/class-tm-dashboard-handlers.php' );
				require_once tm_dashboard()->plugin_dir( 'admin/includes/tm-backup/class-tm-backups-table.php' );

				add_action( 'admin_footer', array( $this, 'print_template' ) );
			}
		}

		public function print_template() {
			$args = array(
				'archivename' => '{{{data.name}}}',
				'date'        => '{{{data.date}}}',
				'version'     => '{{{data.version}}}',
				'actions'     => tm_backups_table()->get_action_buttons(),
				'hint'        => '',
			);
			$row = tm_backups_table()->get_row( $args );

			echo '<script type="text/html" id="tmpl-new-backup-template">' . $row . '</script>';
		}

		public function dashboard_page_modules() {
			$this->builder = tm_dashboard()->get_core()->init_module( 'cherry-interface-builder' );
		}

		public function get_builder() {
			return $this->builder;
		}

		public function render() {
			return $this->builder->render();
		}

		public function register_section_hook() {
			do_action( 'tm_dashboard_add_section', $this->get_builder(), tm_dashboard() );
		}

		/**
		 * Register new admin pages.
		 *
		 * @since 1.0.0
		 */
		public function register_new_pages() {
			$menu_page_args = apply_filters( 'tm_dashboard_menu_page_args', array(
				'page_title' => esc_html__( 'Jetimpex Dashboard', 'tm-dashboard' ),
				'menu_title' => esc_html__( 'Jetimpex', 'tm-dashboard' ) . tm_update_notices()->menu_bage,
				'capability' => 'manage_options',
				'menu_slug'  => $this->get_menu_slug(),
				'function'   => array( $this, 'build_page' ),
				'icon_url'   => tm_dashboard()->plugin_url( 'admin/assets/img/jetimplex-icon.png' ),
				'position'   => $this->position,
			) );

			// Register dashboard menu item.
			add_menu_page(
				$menu_page_args['page_title'],
				$menu_page_args['menu_title'],
				$menu_page_args['capability'],
				$menu_page_args['menu_slug'],
				$menu_page_args['function'],
				$menu_page_args['icon_url'],
				$menu_page_args['position']
			);

			// Register dashboard subpages.
			foreach ( $this->subpages() as $slug => $data ) {
				add_submenu_page(
					$this->get_menu_slug(),
					$data['page-title'],
					$data['menu-title'],
					$menu_page_args['capability'],
					$slug,
					array( $this, 'build_page' )
				);
			}
		}

		/**
		 * Enqueue stylesheet and javascript.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			wp_enqueue_style( 'tm-dashboard-notification' );

			if ( ! $this->is_related_page() ) {
				return;
			}

			wp_enqueue_style( 'tm-dashboard' );
			wp_enqueue_script( 'tm-dashboard' );
		}

		/**
		 * Get registered subpages list for Jetimpex menu.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function subpages() {

			if ( ! empty( $this->subpages ) ) {
				return $this->subpages;
			}

			$this->subpages = apply_filters( 'tm_dashboard_subpages', array(
				$this->get_menu_slug() => array(
					'page-title' => esc_html__( 'Jetimpex Dashboard', 'tm-dashboard' ),
					'menu-title' => esc_html__( 'Dashboard', 'tm-dashboard' ),
					'depends'    => array(),
				),
				'tm-updates' => array(
					'page-title' => esc_html__( 'Jetimpex Updates', 'tm-dashboard' ),
					'menu-title' => esc_html__( 'Updates', 'tm-dashboard' ) . tm_update_notices()->menu_bage,
					'depends'    => array(),
				),
			) );

			if ( empty( $this->subpages ) || ! is_array( $this->subpages ) ) {
				return array();
			}

			return $this->subpages;
		}

		/**
		 * Build dashboard submenu page.
		 *
		 * @since 1.0.0
		 */
		public function build_page() {
			$subpages = $this->subpages();

			$pages = array_unique(
				array_merge(
					array( $this->get_menu_slug() ),
					array_keys( $subpages )
				)
			);

			$current = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : false;

			if ( ! in_array( $current, $pages ) ) {
				wp_die( esc_html__( 'Page not exists', 'tm-dashboard' ) );
			}

			$depends = isset( $subpages[ $current ]['depends'] ) ? $subpages[ $current ]['depends'] : false;

			$this->open_page_wrap();
			$this->load_view( $current, $depends );
			$this->close_page_wrap();
		}

		/**
		 * Open admin page wrapper.
		 *
		 * @since 1.0.0
		 */
		public function open_page_wrap() {
			echo '<div class="wrap">';
		}

		/**
		 * Close admin page wrapper.
		 *
		 * @since 1.0.0
		 */
		public function close_page_wrap() {
			echo '</div>';
		}

		/**
		 * Load page view.
		 *
		 * @since  1.0.0
		 * @param  string     $view    Get specific page output.
		 * @param  bool|array $depends View dependencies array.
		 * @return void|bool false
		 */
		public function load_view( $view, $depends = false ) {

			if ( ! $view ) {
				return false;
			}

			do_action( 'tm_dashboard_load_page' );

			if ( false !== $depends ) {
				foreach ( $depends as $file ) {

					if ( ! file_exists( $file ) ) {
						continue;
					}

					$filename = basename( $file );

					do_action( "tm_dashboard_load_{$filename}" );
					include_once $file;
				}
			}

			$view_path = tm_dashboard()->plugin_dir( 'admin/views/' . $view . '.php' );

			if ( file_exists( $view_path ) ) {

				do_action( "tm_dashboard_load_{$view}" );
				include_once $view_path;
			}
		}

		/**
		 * Retrieve a dashboard menu item slug.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_menu_slug() {
			return apply_filters( 'tm_dashboard_menu_slug', $this->menu_slug );
		}

		/**
		 * Check if on dashboard-related page.
		 *
		 * @since 1.0.0
		 * @since 1.0.3 Added a optional argument $slug.
		 * @return bool
		 */
		public function is_related_page( $slug = '' ) {
			$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : false;

			if ( ! $page ) {
				return false;
			}

			if ( ! array_key_exists( $page, $this->subpages() ) ) {
				return false;
			}

			if ( '' !== $slug ) {
				return $page === $slug ? true : false;
			}

			return true;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
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

if ( ! function_exists( 'tm_dashboard_interface' ) ) {

	/**
	 * Returns instanse of the interface class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function tm_dashboard_interface() {
		return TM_Dashboard_Interface::get_instance();
	}
}

tm_dashboard_interface();
