<?php
/**
 * TM Dashboard Cron.
 *
 * @package   TM_Dashboard
 * @author    Cherry Team
 * @version   1.1.0
 * @license   GPL-3.0+
 * @copyright 2012-2017, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'TM_Dashboard_Cron' not exists.
if ( ! class_exists( 'TM_Dashboard_Cron' ) ) {

	class TM_Dashboard_Cron {

		/**
		 * URL to the Tracker API endpoint.
		 *
		 * @since 1.1.0
		 * @var string
		 */
		private $api_url = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/v1/clients';

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.1.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Sets up needed actions/filters for the cron to initialize.
		 *
		 * @since 1.1.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'add_backup_events' ) );
			add_action( 'tm_dashboard_activation', array( $this, 'add_tracker_events' ) );

			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				add_action( 'plugins_loaded', array( $this, 'register_backup_hooks' ) );
				add_action( 'tm_dashboard_run_tracker', array( $this, 'send_tracking_data' ) );
			}

			add_action( 'tm_dashboard_deactivation', array( $this, 'clear_backup_events' ) );
			add_filter( 'cron_schedules', array( $this, 'custom_schedules' ) );
		}

		/**
		 * Add a schedules for backup event.
		 *
		 * @since 1.1.0
		 */
		public function add_backup_events() {
			$options = $this->get_backup_options();

			if ( empty( $options ) ) {
				return;
			}

			foreach ( $options as $theme_name => $settings ) {

				if ( empty( $settings['schedule-on'] ) || 'true' != $settings['schedule-on'] ) {
					continue;
				}

				$recurrence = $settings['schedule-day'];
				$schedules  = wp_get_schedules();

				if ( ! array_key_exists( $recurrence, $schedules ) ) {
					$recurrence = 'daily';
				}

				if ( false !== wp_next_scheduled( 'tm_dashboard_backup_' . $theme_name, array( $theme_name ) ) ) {
					continue;
				}

				$schedule_time = $settings['schedule-time'] . ':00';
				$date          = date( 'Y-m-d ' ) . $schedule_time;
				$timestamp     = get_gmt_from_date( $date );

				wp_schedule_event(
					strtotime( $timestamp ),
					$recurrence,
					'tm_dashboard_backup_' . $theme_name,
					array( $theme_name )
				);
			}
		}

		/**
		 * Add a schedule for tracker event.
		 *
		 * @since 1.1.0
		 */
		public function add_tracker_events() {

			if ( 'yes' === get_option( 'tm_dashboard_allow_tracking', 'yes' ) ) {
				wp_clear_scheduled_hook( 'tm_dashboard_run_tracker' );
				wp_schedule_single_event( time(), 'tm_dashboard_run_tracker' );
			}
		}

		/**
		 * Remove a schedules for backup events.
		 *
		 * @since 1.1.0
		 */
		public function clear_backup_events() {
			$options = $this->get_backup_options();

			if ( empty( $options ) ) {
				return;
			}

			foreach ( $options as $theme_name => $settings ) {
				wp_clear_scheduled_hook( 'tm_dashboard_backup_' . $theme_name, array( $theme_name ) );
			}
		}

		/**
		 * Hooks a callback on to a backup action.
		 *
		 * @since 1.1.0
		 */
		public function register_backup_hooks() {
			$options = $this->get_backup_options();

			if ( empty( $options ) ) {
				return;
			}

			foreach ( $options as $theme_name => $settings ) {
				add_action( 'tm_dashboard_backup_' . $theme_name, array( $this, 'backup_callback' ) );
			}
		}

		/**
		 * Callback function on to run backup.
		 *
		 * @since  1.1.0
		 * @param  string $theme_name
		 * @return void
		 */
		public function backup_callback( $theme_name ) {

			// Don't trigger this on AJAX requests.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			$options = $this->get_backup_options();

			if ( empty( $options[ $theme_name ] ) ) {
				return;
			}

			$limit = ! empty( $options[ $theme_name ]['limit'] ) ? absint( $options[ $theme_name ]['limit'] ) : 5;
			$backup_obj = new TM_Theme_Backup( $theme_name, array(
				'auto'  => true,
				'limit' => $limit,
			) );

			if ( false !== $backup_obj->run() ) {

				$backup_path = TM_Dashboard_Paths::get_path( $theme_name );
				$zips        = $backup_obj->scandir( $backup_path, 'zip', 1, $theme_name );

				if ( empty( $zips ) ) {
					return;
				}

				$archives = get_transient( 'tm_backup_archives' );

				if ( false === $archives ) {
					$new_archives = array( $theme_name => $zips );

				} else {
					$new_archives = array_merge( $archives, array( $theme_name => $zips ) );
				}

				set_transient( 'tm_backup_archives', $new_archives, HOUR_IN_SECONDS );
			}
		}

		/**
		 * Decide whether to send tracking data or not.
		 *
		 * @since 1.1.0
		 */
		public function send_tracking_data() {

			// Don't trigger this on AJAX requests.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( false !== get_option( 'tm_dashboard_tracker_last_send', false ) ) {
				return;
			}

			// Update time first before sending to ensure it is set.
			update_option( 'tm_dashboard_tracker_last_send', time() );

			$result = array();
			$params = $this->get_tracking_data();
			$args   = array(
				'method' => 'POST',
				'body'   => $params,
			);

			return wp_remote_post( $this->api_url, $args );
		}

		/**
		 * Get all the tracking data.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_tracking_data() {
			$data = array();

			$data['site']     = wp_list_pluck( TM_Dashboard_Tools::get_site_info(), 'value' );
			$data['template'] = wp_list_pluck( TM_Dashboard_Tools::get_theme_info(), 'value' );
			$data['themes']   = TM_Dashboard_Tools::get_installed_themes();

			$plugins = TM_Dashboard_Tools::get_installed_plugins();
			$data['plugins']['active']   = $plugins['active'];
			$data['plugins']['inactive'] = $plugins['inactive'];

			$data['server'] = wp_list_pluck( TM_Dashboard_Tools::get_server_params(), 'value' );

			return apply_filters( 'tm_dashboard_tracker_data', $data );
		}

		/**
		 * Retrieve a backup options.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_backup_options() {
			return get_option( 'tm_backup_options', array() );
		}

		/**
		 * Adds a custom cron schedules.
		 *
		 * @since  1.1.0
		 * @param  array $schedules An array of non-default cron schedules.
		 * @return array            Filtered array of non-default cron schedules.
		 */
		public function custom_schedules( $schedules ) {
			$schedules['weekly'] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => esc_html__( 'Once Weekly', 'tm-dashboard' ),
			);

			$schedules['fortnightly'] = array(
				'interval' => 2 * WEEK_IN_SECONDS,
				'display'  => esc_html__( 'Once Every Two Weeks', 'tm-dashboard' ),
			);

			$schedules['monthly'] = array(
				'interval' => MONTH_IN_SECONDS,
				'display'  => esc_html__( 'Once Monthly', 'tm-dashboard' ),
			);

			return $schedules;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.1.0
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

	TM_Dashboard_Cron::get_instance();
}
