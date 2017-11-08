<?php
/**
 * Manages the backup paths.
 *
 * @package   TM_Dashboard
 * @author    Human Made Limited <sales@hmn.md>
 * @author    Cherry Team <cherryframework@gmail.com>
 * @version   1.1.0
 * @license   GPL-3.0+
 */
class TM_Dashboard_Paths {

	/**
	 * The path to the directory that backup files are stored in.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $path = '';

	/**
	 * The URL to the directory that backup files are stored in.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $url = '';

	/**
	 * The custom path to the directory that backup files are stored in.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $custom_path = '';

	/**
	 * The custom URL to the directory that backup files are stored in.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $custom_url = '';

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Class constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {}

	/**
	 * Convenience method for quickly grabbing the path.
	 *
	 * @since 1.1.0
	 */
	public static function get_path( $sub_path = '' ) {
		$path = self::get_instance()->get_calculated_path();

		if ( $sub_path && is_string( $sub_path ) ) {
			$path .= trailingslashit( ltrim( $sub_path, '/' ) );
		}

		return $path;
	}

	/**
	 * Convenience method for quickly grabbing the URL.
	 *
	 * @since 1.1.0
	 */
	public static function get_url( $sub_url = '' ) {
		$url = self::get_instance()->get_calculated_url();

		if ( $sub_url && is_string( $sub_url ) ) {
			$url .= trailingslashit( ltrim( $sub_url, '/' ) );
		}

		return $url;
	}

	/**
	 * Retrieve the calculated path to the directory where backups will be stored.
	 *
	 * @since 1.1.0
	 */
	private function get_calculated_path() {

		// Calculate the path if needed.
		if ( empty( $this->path ) || ! wp_is_writable( $this->path ) ) {
			$this->calculate_path();
		}

		// Ensure the backup directory is protected.
		$this->protect_path();

		return wp_normalize_path( trailingslashit( $this->path ) );
	}

	/**
	 * Retrieve the calculated URL to the directory where backups will be stored.
	 *
	 * @since 1.1.0
	 */
	private function get_calculated_url() {

		// Calculate the URL if needed.
		if ( empty( $this->url ) ) {
			$this->calculate_url();
		}

		return esc_url( trailingslashit( $this->url ) );
	}

	/**
	 * Calculate the backup path and create the directory if it doesn't exist.
	 *
	 * @since 1.1.0
	 */
	public function calculate_path() {
		$paths = array();

		// If we have a custom path then try to use it.
		if ( $this->get_custom_path() ) {
			$paths[] = $this->get_custom_path();
		}

		// If there is already a backups directory then try to use that.
		if ( $this->get_existing_path() ) {
			$paths[] = $this->get_existing_path();
		}

		// If not then default to a new directory in uploads.
		$paths[] = $this->get_default_path();

		// Loop through possible paths, use the first one that exists/can be created and is writable.
		foreach ( $paths as $path ) {
			if ( wp_mkdir_p( $path ) && file_exists( $path ) && wp_is_writable( $path ) ) {
				$this->path = $path;
				break;
			}
		}
	}

	/**
	 * Calculate the backup URL and create the directory if it doesn't exist.
	 *
	 * @since 1.1.0
	 */
	public function calculate_url() {

		// If we have a custom URL then try to use it.
		if ( $this->get_custom_url() ) {
			$this->url = $this->get_custom_url();
		}

		// If not then default URL to a new directory in uploads.
		$this->url = $this->get_default_url();
	}

	/**
	 * Get the path to the custom backup location if it's been set.
	 *
	 * @since 1.1.0
	 */
	public function get_custom_path() {

		if ( $this->custom_path ) {
			return $this->custom_path;
		}

		if ( defined( 'TM_DASHBOARD_BACKUP_PATH' ) && wp_is_writable( TM_DASHBOARD_BACKUP_PATH ) ) {
			return TM_DASHBOARD_BACKUP_PATH;
		}

		return '';
	}

	/**
	 * Get the URL to the custom backup location if it's been set.
	 *
	 * @since 1.1.0
	 */
	public function get_custom_url() {

		if ( $this->custom_url ) {
			return $this->custom_url;
		}

		if ( defined( 'TM_DASHBOARD_BACKUP_URL' ) ) {
			return TM_DASHBOARD_BACKUP_URL;
		}

		return '';
	}

	/**
	 * Returns the first existing path if there is one
	 *
	 * @since 1.1.0
	 * @return string Backup path if found empty string if not
	 */
	public function get_existing_path() {
		$upload_dir  = wp_upload_dir();
		$backup_path = glob( $upload_dir['basedir'] . '/tm-backups', GLOB_ONLYDIR );

		if ( false !== $backup_path && ! empty( $backup_path[0] ) ) {
			return $backup_path[0];
		}

		return '';
	}

	/**
	 * Get the path to the default backup location in uploads.
	 *
	 * @since 1.1.0
	 */
	public function get_default_path() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( wp_normalize_path( $upload_dir['basedir'] ) ) . 'tm-backups';
	}

	/**
	 * Get the URL to the default backup location in uploads.
	 *
	 * @since 1.1.0
	 */
	public function get_default_url() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['baseurl'] ) . 'tm-backups';
	}

	/**
	 * Protect the directory that backups are stored in.
	 *
	 * - Adds an index.php file in an attempt to disable directory browsing
	 * - Adds a .httaccess file to deny direct access if on Apache
	 *
	 * @since 1.1.0
	 * @param string $reset
	 */
	public function protect_path( $reset = 'no' ) {
		global $is_apache;

		// Calculate the path if needed.
		if ( empty( $this->path ) || ! wp_is_writable( $this->path ) ) {
			$this->calculate_path();
		}

		// Protect against directory browsing by including an index.php file
		$index = $this->path . '/index.php';

		if ( ( 'reset' === $reset ) && file_exists( $index ) ) {
			@unlink( $index );
		}

		if ( ! file_exists( $index ) && wp_is_writable( $this->path ) ) {
			file_put_contents( $index, '' );
		}

		$htaccess = $this->path . '/.htaccess';

		if ( ( 'reset' === $reset ) && file_exists( $htaccess ) ) {
			@unlink( $htaccess );
		}

		// Protect the directory with a .htaccess file on Apache servers
		if ( $is_apache && function_exists( 'insert_with_markers' ) && ! file_exists( $htaccess ) && wp_is_writable( $this->path ) ) {

			$contents   = array();
			$contents[] = '# ' . sprintf( esc_html__( 'This %s file ensures that other people cannot download your backup files.', 'tm-dashboard' ), '.htaccess' );
			$contents[] = '';
			$contents[] = '<IfModule mod_rewrite.c>';
			$contents[] = 'RewriteEngine On';
			$contents[] = 'RewriteCond %{QUERY_STRING} !key=' . get_option( 'tm_dashboard_generated_key' );
			$contents[] = 'RewriteRule (.*) - [F]';
			$contents[] = '</IfModule>';
			$contents[] = '';

			file_put_contents( $htaccess, '' );

			insert_with_markers( $htaccess, 'TM_Dashboard', $contents );
		}
	}

	/**
	 * Reset paths and URLs.
	 *
	 * @since 1.1.0
	 */
	public function reset() {
		$this->path = $this->custom_path = '';
		$this->url  = $this->custom_url = '';
	}

	/**
	 * Set the path directly, overriding the default.
	 *
	 * @since 1.1.0
	 * @param $path
	 */
	public function set_path( $path ) {
		$this->custom_path = $path;

		// Re-calculate the backup path.
		$this->calculate_path();
	}

	/**
	 * Set the URL directly, overriding the default.
	 *
	 * @since 1.1.0
	 * @param $url
	 */
	public function set_url( $url ) {
		$this->custom_url = $url;

		// Re-calculate the backup URL.
		$this->calculate_url();
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
