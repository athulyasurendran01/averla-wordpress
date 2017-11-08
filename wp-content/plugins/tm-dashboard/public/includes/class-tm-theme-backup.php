<?php
/**
 * Class for `Theme Backup` logic.
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

// If class 'TM_Theme_Backup' not exists.
if ( ! class_exists( 'TM_Theme_Backup' ) ) {

	/**
	 * Theme Backup class.
	 */
	class TM_Theme_Backup {

		/**
		 * Theme Name.
		 *
		 * @since 1.1.0
		 * @var string
		 */
		private $theme_name = '';

		/**
		 * Arguments.
		 *
		 * @since 1.1.0
		 * @var array
		 */
		private $args = array();

		/**
		 * Class constructor.
		 *
		 * @since 1.1.0
		 */
		public function __construct( $theme_name, $args = array() ) {
			$this->theme_name = sanitize_text_field( $theme_name );

			$this->args = wp_parse_args( $args, array(
				'auto'  => false,
				'limit' => '5',
			) );
		}

		/**
		 * Run a backup.
		 *
		 * @since 1.1.0
		 */
		public function run() {

			if ( empty( $this->theme_name ) ) {
				return false;
			}

			$theme = wp_get_theme( $this->theme_name );

			if ( ! $theme->exists() ) {
				return false;
			}

			$backup_dir = TM_Dashboard_Paths::get_path( $theme->get_stylesheet() );

			if ( $this->args['auto'] ) {
				$backup_dir .= 'auto/';
			}

			// Prepare directory for backup.
			if ( false === wp_mkdir_p( $backup_dir ) ) {
				return false;
			}

			if ( $this->args['auto'] ) {

				// Find all backups.
				$all_backups = $this->scandir( $backup_dir, 'zip', 0 );

				// Check amount of backups and delete that if more than limit.
				$this->check_limit( $all_backups );
			}

			$backup_path = $backup_dir . $this->get_filename( $theme );
			$result      = $this->create( $theme, $backup_path );

			if ( is_wp_error( $result ) ) {
				return false;
			}

			return $backup_path;
		}

		/**
		 * Create backup.
		 *
		 * @since  1.1.0
		 * @param  WP_Theme      $what WP_Theme object for a theme.
		 * @param  string        $to   Full path and filename of zip archive.
		 * @return WP_Error|bool
		 */
		public function create( $what, $to ) {
			$z      = new ZipArchive();
			$opened = $z->open( $to, ZIPARCHIVE::CREATE );

			if ( true !== $opened ) {
				return new WP_Error( 'incompatible_archive', esc_html__( 'Incompatible Archive.', 'tm-dashboard' ), array( 'ziparchive_error' => $z ) );
			}

			$files = $what->get_files( null, -1, false );

			if ( is_array( $files ) && ! empty( $files ) ) {

				foreach ( $files as $relative_path => $absolute_path ) {
					$normalize_path = wp_normalize_path( $absolute_path );

					if ( is_file( $normalize_path ) ) {
						$z->addFile( $normalize_path, $relative_path );

					} elseif ( is_dir( $normalize_path ) ) {
						$z->addEmptyDir( $relative_path );
					}
				}
			}

			// Added to archive a theme mods.
			$stylesheet = $what->get_stylesheet();
			$temp_file  = $this->get_temp_file_with_theme_mods( $stylesheet );

			if ( is_file( $temp_file ) ) {
				$z->addFile( $temp_file, 'theme-mods.json' );
			}

			$closed = $z->close();
			@unlink( $temp_file );

			if ( true !== $closed ) {
				return new WP_Error( 'incompatible_archive', esc_html__( 'Incompatible Archive.', 'tm-dashboard' ), array( 'ziparchive_error' => $z ) );
			}

			return true;
		}

		/**
		 * Check a limit for backup files.
		 *
		 * @since 1.1.0
		 * @param array $all_backups Array of files
		 */
		public function check_limit( $all_backups ) {

			if ( sizeof( $all_backups ) < $this->args['limit'] ) {
				return;
			}

			$cleaned = $this->delete_excess( $all_backups );

			$this->check_limit( $cleaned );
		}

		/**
		 * Remove most older backup file.
		 *
		 * @since  1.1.0
		 * @param  array $backups_data
		 * @return array
		 */
		public function delete_excess( $backups_data ) {
			// $sorted_backups = $this->sort_by_date( $backups_data );

			// wp_delete_file( reset( $sorted_backups ) );
			wp_delete_file( end( $backups_data ) );
			array_shift( $backups_data );

			return $backups_data;
		}

		/**
		 * Sort passed array by file modification time.
		 *
		 * @since  1.1.0
		 * @param  array $backups_data
		 * @return array
		 */
		public function sort_by_date( $backups_data ) {
			$sorted = array();

			foreach ( $backups_data as $name => $abs_path ) {

				$path      = wp_normalize_path( $abs_path );
				$filemtime = filemtime( $path );

				if ( false === $filemtime ) {
					continue;
				}

				$sorted[ $filemtime ] = $path;
			}

			ksort( $sorted );

			return $sorted;
		}

		/**
		 * Retrieve a filename for backup. E.g. `qwerty-vX.Y.Z-Ymd-Hi.zip`.
		 *
		 * @since  1.1.0
		 * @param  WP_Theme $theme Gets a WP_Theme object for a theme.
		 * @return string
		 */
		public function get_filename( $theme ) {
			$filename = sprintf( '%s__v%s__%s.zip', $theme->get_stylesheet(), $theme->get( 'Version' ), current_time( 'Ymd-His' ) );

			return sanitize_file_name( remove_accents( $filename ) );
		}

		/**
		 * Scans a directory for files of a certain extension.
		 * This is a copy from WordPress core - WP_Theme::scandir
		 *
		 * @link  https://developer.wordpress.org/reference/classes/wp_theme/scandir/
		 * @since 1.1.0
		 * @param string            $path          Absolute path to search.
		 * @param array|string|null $extensions    Optional. Array of extensions to find, string of a single extension,
		 *                                         or null for all extensions. Default null.
		 * @param int               $depth         Optional. How many levels deep to search for files. Accepts 0, 1+, or
		 *                                         -1 (infinite depth). Default 0.
		 * @param string            $relative_path Optional. The basename of the absolute path. Used to control the
		 *                                         returned path for the found files, particularly when this function
		 *                                         recurses to lower depths. Default empty.
		 * @return array|false                     Array of files, keyed by the path to the file relative to the
		 *                                         `$path` directory prepended with `$relative_path`, with the values
		 *                                         being absolute paths. False otherwise.
		 */
		public function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {

			if ( ! is_dir( $path ) ) {
				return false;
			}

			$path = untrailingslashit( $path );

			if ( $extensions ) {
				$extensions  = (array) $extensions;
				$_extensions = implode( '|', $extensions );
			}

			$relative_path = trailingslashit( $relative_path );

			if ( '/' == $relative_path ) {
				$relative_path = '';
			}

			$results = scandir( $path );
			$files   = array();

			foreach ( $results as $result ) {

				if ( '.' == $result[0] ){
					continue;
				}

				if ( is_dir( $path . '/' . $result ) ) {

					if ( ! $depth || 'CVS' == $result ) {
						continue;
					}

					$found = $this->scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );
					$files = array_merge_recursive( $files, $found );

				} elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
					$files[ $relative_path . $result ] = $path . '/' . $result;
				}
			}

			if ( ! empty( $files ) ) {
				uasort( $files, array( 'TM_Dashboard_Tools', 'sort_by_date_in_filename' ) );
			}

			return $files;
		}

		public function get_temp_file_with_theme_mods( $stylesheet ) {
			$data = get_option( "theme_mods_{$stylesheet}" );

			if ( ! $data ) {
				return false;
			}

			if ( ! function_exists( 'wp_tempnam' ) ) {
				include_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$temp_file = wp_tempnam();

			$fp = fopen( $temp_file, 'w+' );

			if ( ! $fp ) {
				return false;
			}

			fwrite( $fp, json_encode( $data ) );
			fclose( $fp );

			return wp_normalize_path( $temp_file );
		}
	}
}
