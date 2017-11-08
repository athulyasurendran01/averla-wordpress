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

// If class `Tm_Themes_List` doesn't exists yet.
if ( ! class_exists( 'Tm_Themes_List' ) ) {

	/**
	 * Tm_Themes_List class.
	 */
	class Tm_Themes_List extends Tm_Themes_Forms {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of TM_Check_Themes class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $check_themes = null;

		/**
		 * Class constructor.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->check_themes = tm_check_themes();
		}
		/**
		 * Html render.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function render() {
			$tm_themes = $this->check_themes->get_tm_themes();

			if ( empty( $tm_themes ) ) {
				printf( esc_html__( 'You don\'t have themes from the company %1$sTemplateMonsters%2$s', 'tm-dashboard' ), '<a href="//www.templatemonster.com" target="_blank">', '</a>' );

				return;
			}

			$themes_list = $current_theme = '';
			$settings    = get_option( 'tm_backup_options', array() );

			if ( function_exists( 'wp_list_sort' ) ) {
				$tm_themes = wp_list_sort( $tm_themes, 'wait_update', 'DESC', true );

			} else {
				usort( $tm_themes, array( 'TM_Dashboard_Tools', 'sort_by_wait_update' ) );
			}

			foreach ( $tm_themes as $theme ) {
				$slug  = $theme['slug'];
				$value = wp_parse_args( $theme, array(
					'child_theme' => false,
				) );

				$update_form = $notification = '';

				if ( ! $value['child_theme'] ) {
					$auto_backup = ! empty( $settings[ $slug ]['auto-backup'] ) ? $settings[ $slug ]['auto-backup'] : 'true';


					if ( $value['verificaton'] ) {
						$notification = ( version_compare( $value['version'], $value['update'], '<' ) ) ? apply_filters( 'tm_update_image_notification', '' ) : '';
						$inner = $this->get_theme_info_block( $value );

					} else {
						$inner = $this->get_verificaton_block( $value );
					}

					$update_form = sprintf( '<form class="cherry-form tm-updates__theme-form" id="%1$s" name="%1$s" accept-charset="utf-8" autocomplete="on" enctype="application/x-www-form-urlencoded" method="get" data-auto-backup="%2$s">
										<div class="tm-updates__theme-form-controls">
											%3$s
										</div>
									</form>',
						$value['slug'],
						$auto_backup,
						$inner
					);
				}

				if ( ! empty( $value['screenshot'] ) ) {
					$screenshot = sprintf(
						'<img src="%1$s" alt="%2$s">',
						esc_url( $value['screenshot'] ),
						esc_attr( $value['name'] )
					);

				} else {
					$screenshot = '<div class="theme-screenshot theme-screenshot--blank"></div>';
				}

				$update_section = sprintf(
					'<div class="cherry-ui-kit cherry-section%8$s">
						<div class="tm-updates__theme">
							<h2 class="tm-updates__theme-name tm-dashboard-h2-like">%3$s</h2>
							<div class="tm-updates__theme-content">
								<div class="tm-updates__theme-image">
									%5$s
									%2$s
								</div>
								<div class="tm-updates__theme-wrap">
									%4$s
									<button type="button" class="tm-dashboard-toggle-backup-section%9$s" data-toggle="%1$s"><span class="screen-reader-text">Toggle</span></button>
									%6$s
									%7$s
								</div>
							</div>
						</div>
					</div>',
					$value['slug'],
					$screenshot,
					$value['name'],
					$update_form,
					$notification,
					$this->backup_section( $slug, $value['activate'] ),
					$this->restore_section( $slug ),
					$value['activate'] ? ' cherry-section--active' : '',
					$value['activate'] ? '' : ' tm-dashboard-toggle-backup-section--hide'
				);

				if ( $value['activate'] ) {
					$current_theme .= $update_section;

				} else {
					$themes_list .= $update_section;
				}
			}

			echo $current_theme . $themes_list;
		}

		public function backup_section( $theme_name, $is_current = false ) {
			$instance = $theme_name;

			$all_options   = get_option( 'tm_backup_options', array() );
			$theme_options = ! empty( $all_options[ $theme_name ] ) ? array_map( 'sanitize_text_field', $all_options[ $theme_name ] ) : '';
			$builder       = tm_dashboard()->get_core()->modules['cherry-interface-builder'];

			$builder->register_form( array(
				'tm-backup-theme-' . $instance => array(
					'type'  => 'form',
					'class' => 'tm-backup-theme',
				),
			) );

			$builder->register_html( array(
				'id'            => 'tm-backup-theme-name-' . $instance,
				'parent'        => 'tm-backup-theme-' . $instance,
				'html'          => sprintf( '<input type="hidden" name="theme-name" value="%s" class="tm-backup-theme-name">', esc_attr( $theme_name ) ),
				'view_wrapping' => false,
			) );

			$builder->register_html( array(
				'id'            => 'tm-backup-theme-title-' . $instance,
				'parent'        => 'tm-backup-theme-' . $instance,
				'html'          => sprintf( '<h2 class="tm-dashboard-h2-like">%s</h2>', esc_html__( 'Backup', 'tm-dashboard' ) ),
				'view_wrapping' => false,
			) );

			$builder->register_control( array(
				'tm-backup-theme-btn-' . $instance => array(
					'type'        => 'button',
					'parent'      => 'tm-backup-theme-' . $instance,
					'form'        => 'tm-backup-theme-' . $instance,
					'content'     => '<span class="tm-dashboard-btn__text">' . esc_html__( 'Backup theme', 'tm-dashboard' ) . '</span>' . $this->button_element,
					'button_type' => 'button',
					'style'       => 'primary',
					'class'       => 'tm-dashboard-btn tm-backup-theme-btn tm-backup-schedule-wrap',
				),
			) );

			$builder->register_html( array(
				'id'            => 'tm-backup-advanced-' . $instance,
				'parent'        => 'tm-backup-theme-' . $instance,
				'html'          => sprintf( '<input type="checkbox" id="%1$s" class="tm-backup-advanced-check screen-reader-text" value="1" %3$s><label for="%1$s" class="tm-backup-advanced-text"><span class="tm-backup-advanced-text__inner">%2$s</span></label>',
					'tm-backup-advanced-' . $instance,
					esc_html__( 'Advanced Options', 'tm-dashboard' ),
					checked( $is_current, false, false )
				),
				'view_wrapping' => false,
			) );

			$builder->register_control( array(
				'tm-auto-backup-' . $instance => array(
					'type'   => 'switcher',
					'parent' => 'tm-backup-theme-' . $instance,
					'title'  => esc_html__( 'Backup before Update:', 'tm-dashboard' ),
					'name'   => 'auto-backup',
					'value'  => ! empty( $theme_options['auto-backup'] ) ? $theme_options['auto-backup'] : 'true',
					'toggle' => array(
						'true_toggle'  => esc_html__( 'ON', 'tm-dashboard' ),
						'false_toggle' => esc_html__( 'OFF', 'tm-dashboard' ),
					),
					'style' => 'normal',
					'class' => 'tm-backup-schedule-wrap',
					'hint'  => __( 'An option enables backup before update (a backup is made automatically right before the theme update changes will take place)', 'tm-dashboard' ),
					'view'  => tm_dashboard()->plugin_dir( 'admin/views/control.php' ),
				),
				'tm-backup-schedule-on' . $instance => array(
					'type'   => 'switcher',
					'parent' => 'tm-backup-theme-' . $instance,
					'title'  => esc_html__( 'Scheduled Backup:', 'tm-dashboard' ),
					'name'   => 'schedule-on',
					'value'  => ! empty( $theme_options['schedule-on'] ) ? $theme_options['schedule-on'] : 'true',
					'toggle' => array(
						'true_toggle'  => esc_html__( 'ON', 'tm-dashboard' ),
						'false_toggle' => esc_html__( 'OFF', 'tm-dashboard' ),
						'true_slave'   => 'tm-backup-schedule-rel-' . $instance,
						'false_slave'  => '',
					),
					'style' => 'normal',
					'class' => 'tm-backup-schedule-wrap',
					'hint'  => __( 'Here you can enable a sheduled backup', 'tm-dashboard' ),
					'view'  => tm_dashboard()->plugin_dir( 'admin/views/control.php' ),
				),
				'tm-backup-schedule-day-' . $instance => array(
					'type'     => 'select',
					'parent'   => 'tm-backup-theme-' . $instance,
					'title'    => esc_html__( 'Backup:', 'tm-dashboard' ),
					'multiple' => false,
					'filter'   => false,
					'name'     => 'schedule-day',
					'value'    => ! empty( $theme_options['schedule-day'] ) ? $theme_options['schedule-day'] : 'daily',
					'options'  => array(
						'hourly'      => esc_html__( 'Once Hourly', 'tm-dashboard' ),
						'twicedaily'  => esc_html__( 'Twice Daily', 'tm-dashboard' ),
						'daily'       => esc_html__( 'Once Daily', 'tm-dashboard' ),
						'weekly'      => esc_html__( 'Once Weekly', 'tm-dashboard' ),
						'fortnightly' => esc_html__( 'Once Every Two Weeks', 'tm-dashboard' ),
						'monthly'     => esc_html__( 'Once Monthly', 'tm-dashboard' ),
					),
					'master' => 'tm-backup-schedule-rel-' . $instance,
					'class'  => 'tm-backup-schedule-wrap tm-backup-schedule-wrap--day',
					'hint'   => __( 'In this field you can set the specific frequency when the backup is to be made', 'tm-dashboard' ),
					'view'   => tm_dashboard()->plugin_dir( 'admin/views/control.php' ),
				),
				'tm-backup-schedule-time-' . $instance => array(
					'type'     => 'select',
					'parent'   => 'tm-backup-theme-' . $instance,
					'title'    => esc_html__( 'Time of Backup:', 'tm-dashboard' ),
					'multiple' => false,
					'filter'   => false,
					'name'     => 'schedule-time',
					'value'    => ! empty( $theme_options['schedule-time'] ) ? $theme_options['schedule-time'] : '15:00',
					'options'  => TM_Dashboard_Tools::get_day_times(),
					'master'   => 'tm-backup-schedule-rel-' . $instance,
					'class'    => 'tm-backup-schedule-wrap tm-backup-schedule-wrap--time',
					'hint'     => __( 'Here you can set particular time for backup', 'tm-dashboard' ),
					'view'     => tm_dashboard()->plugin_dir( 'admin/views/control.php' ),
				),
				'tm-backup-limit-' . $instance => array(
					'type'       => 'stepper',
					'parent'     => 'tm-backup-theme-' . $instance,
					'title'      => esc_html__( 'Number of Backup Copies:', 'tm-dashboard' ),
					'name'       => 'limit',
					'value'      => ! empty( $theme_options['limit'] ) ? $theme_options['limit'] : 5,
					'max_value'  => 10,
					'min_value'  => 1,
					'step_value' => 1,
					'master'     => 'tm-backup-schedule-rel-' . $instance,
					'class'      => 'tm-backup-schedule-wrap tm-backup-schedule-wrap--limit',
					'hint'       => __( 'The setting will define only the number of automatically made backups, you still can make as many backup copies manually, as you need to', 'tm-dashboard' ),
					'view'       => tm_dashboard()->plugin_dir( 'admin/views/control.php' ),
				),
			) );

			$builder->register_control( array(
				'tm-backup-save-btn-' . $instance => array(
					'type'        => 'button',
					'parent'      => 'tm-backup-theme-' . $instance,
					'form'        => 'tm-backup-theme-' . $instance,
					'content'     => '<span class="tm-dashboard-btn__text">' . esc_html__( 'Save Options', 'tm-dashboard' ) . '</span>' . $this->button_element,
					'button_type' => 'button',
					'style'       => 'normal',
					'class'       => 'tm-dashboard-btn tm-backup-schedule-wrap tm-backup-schedule-wrap--save-control',
				),
			) );

			return $builder->render( false );
		}

		public function restore_section( $theme_name ) {
			return tm_backups_table()->build( $theme_name );
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
}
