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

// If class `Tm_Notification_Popup` doesn't exists yet.
if ( ! class_exists( 'Tm_Notification_Popup' ) ) {

	/**
	 * Tm_Notification_Popup class.
	 */
	class Tm_Notification_Popup {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
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
		 * Class constructor.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			if ( ! defined( 'DOING_AJAX' ) ) {
				add_action( 'admin_print_footer_scripts', array( $this, 'render_update_popup' ), 0 );
				add_action( 'admin_print_footer_scripts', array( $this, 'render_restore_popup' ), 1 );

				$this->builder = tm_dashboard()->get_core()->init_module( 'cherry-interface-builder' );
			}
		}

		public function render_update_popup() {
			$this->builder->reset_structure();

			$this->builder->register_html( array(
				'view_wrapping' => false,
				'id'            => 'tm-restore-title',
				'html'          => '<div class="tm-dashboard-popup__header"><h1 class="tm-dashboard-popup__title tm-dashboard-h3-like">' . esc_html__( 'Attention! In order to prepare your theme backup, please click on the button "Backup"', 'tm-dashboard' ) . '</h1></div>',
			) );

			$this->builder->register_settings( array(
				'id' => 'tm-dashboard-popup__buttons',
			) );

			$this->builder->register_control( array(
				'update-theme-continue'  => array(
					'type'          => 'button',
					'style'         => 'success',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Backup', 'tm-dashboard' ),
					'parent'        => 'tm-dashboard-popup__buttons',
				),
				'update-theme-cancel'  => array(
					'type'          => 'button',
					'style'         => 'primary',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Cancel', 'tm-dashboard' ),
					'parent'        => 'tm-dashboard-popup__buttons',
				)
			) );

			$content = $this->builder->render( false );

			printf( '<section id="tm-dashboard-update-popup" class="tm-dashboard-popup tm-dashboard-popup--update"><div class="tm-dashboard-popup__inner">%1$s</div><div class="tm-dashboard-popup__background"></div></section>', $content );
		}

		public function render_restore_popup() {
			$this->builder->reset_structure();

			$this->builder->register_form( array(
				'id' => 'tm-restore-form',
			) );

			$this->builder->register_html( array(
				'id'            => 'tm-restore-title',
				'view_wrapping' => false,
				'html'          => '<div class="tm-dashboard-popup__header"><h1 class="tm-dashboard-popup__title tm-dashboard-h3-like">' . esc_html__( 'Attention! All current theme files &amp; settings will be overwritten.', 'tm-dashboard' ) . '</h1></div>',
				'parent'        => 'tm-restore-form',
			) );

			$this->builder->register_settings( array(
				'id'     => 'tm-dashboard-popup__content',
				'parent' => 'tm-restore-form',
			) );

			$this->builder->register_control( array(
				'tm-restore-settings'  => array(
					'type'  => 'checkbox',
					'value' => array(
						'theme-files' => 'true',
					),
					'options' => array(
						'theme-files' => esc_html__( 'Theme Files', 'tm-dashboard' ),
						'theme-mods'  => esc_html__( 'Customizer Settings', 'tm-dashboard' ),
					),
					'view_wrapping' => false,
					'label'         => esc_html__( 'By default you will restore Theme Files and Customizer Settings. You can choose this option when needed:', 'tm-dashboard' ),
					'parent'        => 'tm-dashboard-popup__content',
				),
			) );

			$this->builder->register_settings( array(
				'id'     => 'tm-dashboard-popup__buttons',
				'parent' => 'tm-restore-form',
			) );

			$this->builder->register_control( array(
				'tm-dashboard-restore-start'  => array(
					'type'          => 'button',
					'style'         => 'success',
					'view_wrapping' => false,
					'content'       => esc_html__( 'OK', 'tm-dashboard' ),
					'parent'        => 'tm-dashboard-popup__buttons',
				),
				'tm-dashboard-restore-cancel'  => array(
					'type'          => 'button',
					'style'         => 'primary',
					'view_wrapping' => false,
					'content'       => esc_html__( 'Cancel', 'tm-dashboard' ),
					'parent'        => 'tm-dashboard-popup__buttons',
				)
			) );

			$content = $this->builder->render( false );

			printf( '<section id="tm-dashboard-restore-popup" class="tm-dashboard-popup tm-dashboard-popup--restore"><div class="tm-dashboard-popup__inner">%1$s</div><div class="tm-dashboard-popup__background"></div></section>', $content );
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

	if ( ! function_exists( 'tm_notification_popup' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_notification_popup() {
			return Tm_Notification_Popup::get_instance();
		}
	}

	tm_notification_popup();
}
