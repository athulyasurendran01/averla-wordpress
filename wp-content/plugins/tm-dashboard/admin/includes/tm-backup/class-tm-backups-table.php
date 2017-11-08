<?php
/**
 * Table builder class.
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

if ( ! class_exists( 'TM_Backups_Table' ) ) {

	/**
	 * TM_Backups_Table class.
	 */
	class TM_Backups_Table {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.1.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Action buttons html.
		 *
		 * @since 1.1.0
		 * @var string
		 */
		private $action_buttons = '';

		/**
		 * Class constructor.
		 *
		 * @since 1.1.0
		 */
		public function __construct() {}

		public function build( $stylesheet ) {
			$archives    = get_transient( 'tm_backup_archives' );
			$extra_class = $tbody = '';

			if ( empty( $archives[ $stylesheet ] ) ) {

				$backup_path  = TM_Dashboard_Paths::get_path( $stylesheet );
				$backup_obj   = new TM_Theme_Backup( $stylesheet );
				$zips         = $backup_obj->scandir( $backup_path, 'zip', 1, $stylesheet );

				if ( false === $archives ) {
					$new_archives = array( $stylesheet => $zips );

				} else {
					$new_archives = array_merge( $archives, array( $stylesheet => $zips ) );
				}

				if ( ! empty( $zips ) ) {
					set_transient( 'tm_backup_archives', $new_archives, HOUR_IN_SECONDS );
				}

				$archives = $new_archives;
			}

			if ( ! empty( $archives[ $stylesheet ] ) ) {
				foreach ( $archives[ $stylesheet ] as $name => $path ) {
					$data    = TM_Dashboard_Tools::get_backup_data_by_path( $path );
					$explode = explode( '/auto/', $name );
					$is_auto = sizeof( $explode ) > 1 ? true : false;

					$args = array(
						'archivename' => $data['name'],
						'date'        => $data['date'],
						'version'     => $data['version'],
						'actions'     => $this->get_action_buttons(),
						'hint'        => $is_auto ? $this->get_auto_hint() : '',
					);

					$tbody .= $this->get_row( $args );
				}

			} else {
				$extra_class = 'hide';
			}

			$table = $this->open( array(
				'themename' => $stylesheet,
				'class'     => $extra_class,
			) );
				$table .= $this->get_thead();
				$table .= $tbody;
			$table .= $this->close();

			return $table;
		}

		public function open( $args) {
			$options = $this->get_options( $args['themename'] );
			$limit   = ! empty( $options['limit'] ) ? $options['limit'] : 5;

			return sprintf( '<table class="tm-dashboard-table tm-dashboard-table--striped %s" data-themename="%s" data-backup-limit="%s">',
				esc_attr( $args['class'] ),
				esc_attr( $args['themename'] ),
				absint( $limit )
			);
		}

		public function close() {
			return sprintf( '</table>' );
		}

		public function get_thead() {
			$path = tm_dashboard()->plugin_dir( 'admin/views/table/thead.php' );

			return Cherry_Toolkit::render_view( $path );
		}

		public function get_row( $args ) {
			$path = tm_dashboard()->plugin_dir( 'admin/views/table/row.php' );

			return Cherry_Toolkit::render_view( $path, $args );
		}

		public function get_auto_hint() {
			$path = tm_dashboard()->plugin_dir( 'admin/views/table/auto-hint.php' );

			return Cherry_Toolkit::render_view( $path );
		}

		/**
		 * Retrieve an action buttons html.
		 *
		 * @since 1.1.0
		 * @return string
		 */
		public function get_action_buttons() {

			if ( '' === $this->action_buttons ) {

				$path = tm_dashboard()->plugin_dir( 'admin/views/table/action-buttons.php' );
				$args = $this->get_action_args();

				$this->action_buttons = Cherry_Toolkit::render_view( $path, $args );
			}

			return $this->action_buttons;
		}

		/**
		 * Retrieve an action buttons arguments.
		 *
		 * @since 1.1.0
		 * @return string
		 */
		public function get_action_args() {
			return apply_filters( 'tm_backups_table_action_args', array(
				'restore' => array(
					'icon' => 'dashicons dashicons-image-rotate',
					'hint' => __( 'Restore - restore the specific backup copy', 'tm-dashboard' ),
				),
				'download' => array(
					'icon' => 'dashicons dashicons-download',
					'hint' => __( 'Download - download a backup copy on your hard drive', 'tm-dashboard' ),
				),
				'delete' => array(
					'icon' => 'dashicons dashicons-trash',
					'hint' => __( 'Delete - delete this backup copy', 'tm-dashboard' ),
				),
			) );
		}

		public function get_options( $stylesheet ) {
			$settings = get_option( 'tm_backup_options', array() );

			return ! empty( $settings[ $stylesheet ] ) ? $settings[ $stylesheet ] : array();
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.1.0
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

if ( ! function_exists( 'tm_backups_table' ) ) {

	/**
	 * Returns instanse of the interface class.
	 *
	 * @since  1.1.0
	 * @return object
	 */
	function tm_backups_table() {
		return TM_Backups_Table::get_instance();
	}
}

tm_backups_table();
