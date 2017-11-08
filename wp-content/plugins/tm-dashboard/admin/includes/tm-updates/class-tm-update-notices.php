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

// If class `Tm_Update_Notices` doesn't exists yet.
if ( ! class_exists( 'Tm_Update_Notices' ) ) {

	/**
	 * Tm_Update_Notices class.
	 */
	class Tm_Update_Notices {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Marker informing about the update.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $menu_bage = '';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->menu_bage = $this->menu_notification();

			add_action( 'admin_notices', array( $this, 'page_notification' ) );
			add_filter( 'tm_update_image_notification', array( $this, 'image_notification' ) );
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_update() {
			$updates  = get_option( 'tm_updates_themes', array() );
			$verified = get_option( 'verified_themes', array() );

			if ( empty( $updates ) || empty( $verified ) ) {
				return false;
			}

			$update_count = 0;

			foreach ( $updates as $slug => $value ) {

				if ( version_compare( $value['version'], $value['update'], '>=' ) ) {
					continue;
				}

				$update_count++;
			}

			return $update_count;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function image_notification( $notice ) {
			return sprintf( '<div class="update-message notice inline notice-warning notice-alt tm-notification-image-label"><p>%1$s</p></div>', esc_html__( 'New Update', 'tm-dashboard' ) );
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function page_notification() {
			$dashboard_pages = tm_dashboard_interface()->is_related_page();
			$update_count    = $this->get_update();

			if ( $dashboard_pages || ! $update_count ) {
				return;
			}

			printf(
				'<div class="notice notice-warning notice-alt"><p>%1$s <a href="admin.php?page=tm-updates">%2$s</a></p></div>',
				esc_html__( 'New version available.', 'tm-dashboard' ),
				esc_html__( 'View', 'tm-dashboard' )
			);
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function menu_notification() {
			$update_count = $this->get_update();

			if ( ! $update_count ) {
				return '';
			}

			return '<span class="tm-notification-label">' . $update_count . '</span>';
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

	if ( ! function_exists( 'tm_update_notices' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_update_notices() {
			return Tm_Update_Notices::get_instance();
		}
	}

	tm_update_notices();
}
