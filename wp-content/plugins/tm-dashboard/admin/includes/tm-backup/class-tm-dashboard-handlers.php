<?php
/**
 * Class for `TM_Dashboard_Handlers` logic.
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

// If class 'TM_Dashboard_Handlers' not exists.
if ( ! class_exists( 'TM_Dashboard_Handlers' ) ) {

	/**
	 * Handlers class.
	 */
	class TM_Dashboard_Handlers {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.1.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Class constructor.
		 *
		 * @since 1.1.0
		 */
		public function __construct() {
			$this->init_modules();
		}

		/**
		 * Fire module initialization.
		 *
		 * @since 1.1.0
		 */
		public function init_modules() {
			tm_dashboard()->get_core()->init_module( 'cherry-js-core' );

			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_backup_settings',
				'action'       => 'tm_backup_settings',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'save_options_callback' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Oops! Something went wrong.', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, you are not allowed to save options', 'tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'Options are saved successfully','tm-dashboard' ),
				),
			) );

			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_theme_backup',
				'action'       => 'tm_theme_backup',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'backup_callback' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Oops! Something went wrong.', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, you are not allowed to create backup', 'tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'You have just created a new backup','tm-dashboard' ),
				),
			) );

			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_theme_restore',
				'action'       => 'tm_theme_restore',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'restore_callback' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Unable to process the request without nonce or server error', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, you are not allowed to restore backup', 'tm-dashboard' ),
					'empty_settings'    => esc_html__( 'You are chosen nothing parameters for restore','tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'Backup are restored successfully','tm-dashboard' ),
				),
			) );

			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_backup_delete',
				'action'       => 'tm_backup_delete',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'delete_callback' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Oops! Something went wrong.', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, you are not allowed to delete backup', 'tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'Backup are deleted successfully','tm-dashboard' ),
				),
			) );

			tm_dashboard()->get_core()->init_module( 'cherry-handler', array(
				'id'           => 'tm_backup_download',
				'action'       => 'tm_backup_download',
				'type'         => 'POST',
				'capability'   => 'manage_options',
				'callback'     => array( $this, 'download_callback' ),
				'sys_messages' => array(
					'invalid_base_data' => esc_html__( 'Oops! Something went wrong.', 'tm-dashboard' ),
					'no_right'          => esc_html__( 'No capabilities for this action', 'tm-dashboard' ),
					'invalid_nonce'     => esc_html__( 'Sorry, you are not allowed to download backup', 'tm-dashboard' ),
					'access_is_allowed' => esc_html__( 'Backup are downloaded successfully','tm-dashboard' ),
				),
			) );
		}

		/**
		 * Handler for save options action.
		 *
		 * @since 1.1.0
		 */
		public function save_options_callback() {
			$result = array(
				'success' => false,
				'extra'   => false,
				'target'  => '',
			);

			if ( empty( $_POST['data']['theme-name'] ) ) {
				return $result;
			}

			$values       = array_map( 'sanitize_text_field', $_POST['data'] );
			$key          = $values['theme-name'];
			$old_settings = get_option( 'tm_backup_options', array() );

			if ( ! empty( $old_settings[ $key ] ) ) {
				$theme_name = $key;
				$args       = $old_settings[ $key ];

				// Clear old schedules.
				wp_clear_scheduled_hook( 'tm_dashboard_backup_' . $theme_name, array( $theme_name ) );

				$old_settings[ $key ] = $values;
				$new_settings = $old_settings;

			} else {
				$new_settings = array_merge( $old_settings, array( $key => $values ) );
			}

			update_option( 'tm_backup_options', $new_settings );

			$result['success'] = true;
			$result['extra']   = $values;
			$result['target']  = $key;

			return $result;
		}

		/**
		 * Handler for backup action.
		 *
		 * @since 1.1.0
		 */
		public function backup_callback() {
			$result = array(
				'success' => false,
				'extra'   => false,
				'target'  => '',
			);

			if ( empty( $_POST['data'] ) ) {
				return $result;
			}

			$theme_name  = sanitize_text_field( $_POST['data'] );
			$backup_obj  = new TM_Theme_Backup( $theme_name );
			$backup_path = $backup_obj->run();
			$result['target'] = $theme_name;

			if ( false !== $backup_path ) {

				$backup_dir = TM_Dashboard_Paths::get_path( $theme_name );
				$zips       = $backup_obj->scandir( $backup_dir, 'zip', 1, $theme_name );

				if ( empty( $zips ) ) {
					return $result;
				}

				$archives = get_transient( 'tm_backup_archives' );

				if ( false === $archives ) {
					$new_archives = array( $theme_name => $zips );

				} else {
					$new_archives = array_merge( $archives, array( $theme_name => $zips ) );
				}

				set_transient( 'tm_backup_archives', $new_archives, HOUR_IN_SECONDS );

				$result['extra']   = TM_Dashboard_Tools::get_backup_data_by_path( $backup_path );
				$result['success'] = true;
			}

			return $result;
		}

		/**
		 * Handler for download action.
		 *
		 * @since 1.1.0
		 */
		public function download_callback() {
			$result = array(
				'success' => false,
				'target'  => '',
				'url'     => '',
			);

			if ( empty( $_POST['data'] ) ) {
				return $result;
			}

			$data = array_map( 'sanitize_text_field', $_POST['data'] );

			if ( empty( $data['themeName'] ) || empty( $data['archiveName'] ) ) {
				return $result;
			}

			$themename   = $data['themeName'];
			$archivename = $data['archiveName'];
			$is_auto     = false;
			$result['target'] = $archivename;

			if ( ! empty( $data['isAutoBackup'] ) ) {
				$is_auto = filter_var( $data['isAutoBackup'], FILTER_VALIDATE_BOOLEAN );
			}

			if ( $is_auto ) {
				$themename .= '/auto';
			}

			$_url = TM_Dashboard_Paths::get_url( $themename );
			TM_Dashboard_Paths::get_instance()->protect_path( 'reset' );

			$url = add_query_arg(
				array( 'key' => get_option( 'tm_dashboard_generated_key' ) ),
				$_url . $archivename
			);

			$result['success'] = true;
			$result['url']     = $url;

			return $result;
		}

		/**
		 * Handler for restore action.
		 *
		 * @since 1.1.0
		 */
		public function restore_callback() {
			$result = array(
				'success'        => false,
				'empty_settings' => false,
				'target'         => '',
			);

			if ( empty( $_POST['data'] ) ) {
				return $result;
			}

			$data = $_POST['data'];

			array_walk_recursive( $data, 'sanitize_text_field' );

			if ( empty( $data['themeName'] ) || empty( $data['archiveName'] ) ) {
				return $result;
			}

			$themename   = $data['themeName'];
			$archivename = $data['archiveName'];
			$is_auto     = false;
			$result['target'] = $archivename;

			if ( ! empty( $data['isAutoBackup'] ) ) {
				$is_auto = filter_var( $data['isAutoBackup'], FILTER_VALIDATE_BOOLEAN );
			}

			$relative_format  = $is_auto ? '%s/auto/%s' : '%s/%s';
			$relative_path    = sprintf( $relative_format, $themename, $archivename );

			$settings = array(
				'theme-files' => true,
				'theme-mods'  => false,
			);

			if ( ! empty( $data['settings'] ) ) {
				$settings = wp_parse_args( $data['settings'], $settings );
			}

			$settings = array_filter( $settings, 'wp_validate_boolean' );

			if ( empty( $settings ) ) {
				$result['empty_settings'] = true;

				return $result;
			}

			$archives = get_transient( 'tm_backup_archives' );

			if ( false === $archives || empty( $archives[ $themename ][ $relative_path ] ) ) {
				return $result;
			}

			$backup_path      = $archives[ $themename ][ $relative_path ];
			$destination_path = path_join( get_theme_root(), $themename );

			if ( ! class_exists( 'WP_Upgrader' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			}

			if ( ! class_exists( 'TM_Dashboard_Upgrader_Skin' ) ) {
				include_once tm_dashboard()->plugin_dir( 'admin/includes/tm-updates/class-tm-dashboard-upgrader-skin.php' );
			}

			$skin       = new TM_Dashboard_Upgrader_Skin();
			$upgrader   = new WP_Upgrader( $skin );
			$is_connect = $upgrader->fs_connect( array( WP_CONTENT_DIR, $destination_path ) );

			if ( true !== $is_connect ) {
				return $result;
			}

			// Only start maintenance mode if:
			// - running Multisite and there are one or more themes specified, OR
			// - a theme with an update available is currently in use.
			$maintenance = is_multisite() || $themename == get_stylesheet() || $themename == get_template();

			if ( $maintenance ) {
				$upgrader->maintenance_mode( true );
			}

			$working_path = $upgrader->unpack_package( $backup_path, false );

			// Restore a theme files.
			if ( array_key_exists( 'theme-files', $settings ) ) {

				// With the given options, this installs it to the destination directory.
				$res = $upgrader->install_package( array(
					'source'                      => $working_path,
					'destination'                 => $destination_path,
					'clear_destination'           => true,
					'clear_working'               => true,
					'abort_if_destination_exists' => false,
					// 'hook_extra'                  => $options['hook_extra'],
				) );

				if ( ! is_wp_error( $res ) ) {
					$result['success'] = true;
				}

				$working_path = $destination_path;
			}

			$upgrader->maintenance_mode( false );

			// Restore a theme mods.
			if ( array_key_exists( 'theme-mods', $settings ) ) {
				$res = $this->restore_theme_mods( $working_path, $themename );

				if ( $res ) {
					$result['success'] = true;
				}
			}

			return $result;
		}

		/**
		 * Restore a theme modifications.
		 *
		 * @since  1.1.0
		 * @param  string $theme_path Directory path for restored theme.
		 * @return bool
		 */
		public function restore_theme_mods( $theme_path, $theme_name ) {
			$file = path_join( $theme_path, 'theme-mods.json' );

			if ( ! is_file( $file ) ) {
				return false;
			}

			$json = file_get_contents( $file );

			if ( ! $json ) {
				return false;
			}

			$data = json_decode( $json, true );

			if ( ! is_array( $data ) ) {
				return false;
			}

			$theme_mods = get_option( "theme_mods_$theme_name" );

			// Import images.
			$data = $this->import_images( $theme_name, $data );

			// // Don't overwrite a menus.
			// if ( ! empty( $theme_mods['nav_menu_locations'] ) ) {
			// 	$data['nav_menu_locations'] = $theme_mods['nav_menu_locations'];
			// }

			// // Don't overwrite a widgets.
			// if ( ! empty( $theme_mods['sidebars_widgets'] ) ) {
			// 	$data['sidebars_widgets'] = $theme_mods['sidebars_widgets'];
			// }

			update_option( "theme_mods_$theme_name", $data );

			return true;
		}

		/**
		 * Imports images for settings saved as mods.
		 *
		 * @since  1.1.0
		 * @param  string $theme_slug A theme slug.
		 * @param  array  $mods       An array of theme modifications.
		 * @return array              The mods array with any new import data.
		 */
		public function import_images( $theme_slug, $mods ) {
			foreach ( $mods as $key => $val ) {

				if ( $this->is_image_url( $val ) ) {

					$data = $this->sideload_image( $val );

					if ( ! is_wp_error( $data ) ) {

						$mods[ $key ] = $data->url;

						// Handle header image controls.
						if ( isset( $mods[ $key . '_data' ] ) ) {
							$mods[ $key . '_data' ] = $data;
							update_post_meta( $data->attachment_id, '_wp_attachment_is_custom_header', $theme_slug );
						}
					}
				}
			}

			return $mods;
		}

		/**
		 * Checks to see whether a string is an image url or not.
		 *
		 * @since  1.1.0
		 * @param  string $string The string to check.
		 * @return bool Whether the string is an image url or not.
		 */
		public function is_image_url( $string = '' ) {

			if ( is_string( $string ) ) {

				if ( preg_match( '/\.(jpg|jpeg|png|gif)/i', $string ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Taken from the core media_sideload_image function and
		 * modified to return an array of data instead of html.
		 *
		 * @since  1.1.0
		 * @param  string $file The image file path.
		 * @return array An array of image data.
		 */
		public function sideload_image( $file ) {
			$data = new stdClass();

			if ( ! function_exists( 'media_handle_sideload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
			}

			if ( ! empty( $file ) ) {

				// Set variables for storage, fix file filename for query strings.
				preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
				$file_array = array();
				$file_array['name'] = basename( $matches[0] );

				// Download file to temp location.
				$file_array['tmp_name'] = download_url( $file );

				// If error storing temporarily, return the error.
				if ( is_wp_error( $file_array['tmp_name'] ) ) {
					return $file_array['tmp_name'];
				}

				// Do the validation and storage stuff.
				$id = media_handle_sideload( $file_array, 0 );

				// If error storing permanently, unlink.
				if ( is_wp_error( $id ) ) {
					@unlink( $file_array['tmp_name'] );

					return $id;
				}

				// Build the object to return.
				$meta                = wp_get_attachment_metadata( $id );
				$data->attachment_id = $id;
				$data->url           = wp_get_attachment_url( $id );
				$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
				$data->height        = $meta['height'];
				$data->width         = $meta['width'];
			}

			return $data;
		}

		/**
		 * Handler for delete action.
		 *
		 * @since 1.1.0
		 */
		public function delete_callback() {
			$result = array(
				'success' => false,
				'empty'   => false,
				'target'  => '',
			);

			if ( empty( $_POST['data'] ) ) {
				return $result;
			}

			$data = array_map( 'sanitize_text_field', $_POST['data'] );

			if ( empty( $data['themeName'] ) || empty( $data['archiveName'] ) ) {
				return $result;
			}

			$is_auto = false;

			if ( ! empty( $data['isAutoBackup'] ) ) {
				$is_auto = filter_var( $data['isAutoBackup'], FILTER_VALIDATE_BOOLEAN );
			}

			$themename   = $data['themeName'];
			$archivename = $data['archiveName'];

			$relative_format  = $is_auto ? '%s/auto/%s' : '%s/%s';
			$relative_path    = sprintf( $relative_format, $themename, $archivename );
			$result['target'] = $archivename;

			$archives = get_transient( 'tm_backup_archives' );

			if ( false === $archives || empty( $archives[ $themename ][ $relative_path ] ) ) {
				return $result;
			}

			$result['success'] = @unlink( $archives[ $themename ][ $relative_path ] );

			unset( $archives[ $themename ][ $relative_path ] );
			set_transient( 'tm_backup_archives', $archives, HOUR_IN_SECONDS );

			if ( empty( $archives[ $themename ] ) ) {
				$result['empty'] = true;
			}

			return $result;
		}

		/**
		 * Returns the instance.
		 *
		 * @since 1.1.0
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

TM_Dashboard_Handlers::get_instance();
