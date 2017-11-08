<?php
/**
 * Plugin installer skin class.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class `TM_Check_Themes` doesn't exists yet.
if ( ! class_exists( 'TM_Check_Themes' ) ) {

	class TM_Check_Themes extends TM_Remote_Query {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Contains a link to the update.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var string
		 */
		private $api_upd = 'http://cloud.cherryframework.com/cherry5-update/wp-json/tm-dashboard-api/check-update?template=%1$s&current_version=%2$s';

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $check_count = 0;

		/**
		 *.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private $pattern = '/[www\.]*template\s*monster[\.com]*/';

		public function add_action() {
			/**
			 * Need for test update - set_site_transient( 'update_themes', null );
			 */
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_themes' ), 11, 2 );
			add_filter( 'pre_set_site_transient_theme_roots', array( $this, 'theme_roots' ), 11, 2 );
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_tm_themes() {
			$result     = array();
			$active     = get_option( 'stylesheet' );
			$all_themes = $this->search_tm_themes();

			foreach ( $all_themes as $stylesheet => $theme_obj ) {
				$name           = $theme_obj->__get( 'name' );
				$version        = $theme_obj->__get( 'version' );
				$template_dir   = $theme_obj->__get( 'template_dir' );
				$template       = $theme_obj->get( 'Template' );
				$screenshot     = $theme_obj->get_screenshot();
				$template_id    = $this->get_template_id( $stylesheet, $template_dir );
				$update         = $this->get_version_update( $stylesheet );
				$is_child_theme = empty( $template ) ? false: true;

				$result[ $stylesheet ] = array(
					'template_id' => $template_id,
					'name'        => $name,
					'slug'        => $stylesheet,
					'screenshot'  => $screenshot,
					'activate'    => ( $active === $stylesheet ) ? true : false,
					'verificaton' => $this->get_verified( $stylesheet ),
					'update'      => $update,
					'version'     => $version,
					'wait_update' => version_compare( $update, $version, '>' ) ? true : false,
					'child_theme' => $is_child_theme,
				);
			}

			return $result;
		}

		/**
		 * .
		 *
		 * @since  1.1.0
		 * @access public
		 * @return array
		 */
		public function search_tm_themes() {
			$themes = wp_get_themes();
			$search = array();

			foreach ( $themes as $stylesheet => $theme_obj ) {

				$аuthor     = strtolower( $theme_obj->get( 'Author' ) );
				$аuthor_uri = strtolower( $theme_obj->get( 'AuthorURI' ) );
				$theme_uri  = strtolower( $theme_obj->get( 'ThemeURI' ) );

				if ( preg_match( $this->pattern, $аuthor )
					|| preg_match( $this->pattern, $аuthor_uri )
					|| preg_match( $this->pattern, $theme_uri )
				) {
					$search[ $stylesheet ] = $theme_obj;
				}
			}

			return $search;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_verified( $slug ) {
			$verified_themes = get_option( 'verified_themes', array() );

			if ( empty( $verified_themes[ $slug ] ) ) {
				return false;
			}

			return $verified_themes[ $slug ];
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_version_update( $slug ) {
			$updates = get_option( 'tm_updates_themes', array() );

			if ( empty( $updates[ $slug ] ) ) {
				return false;
			}

			return $updates[ $slug ]['update'];
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_template_id( $slug, $tim_path ) {
			$template_id_in_file = get_file_data(
				$tim_path. '/' . 'style.css',
				array( 'templateID' => 'Template Id', )
			);

			if ( ! empty( $template_id_in_file['templateID'] ) ) {
				return $template_id_in_file['templateID'];
			}

			$verified_themes = get_option( 'verified_themes', array() );

			if ( ! empty( $verified_themes[ $slug ] ) ) {
				return $verified_themes[ $slug ]['product-id'];
			}

			return false;
		}

		/**
		 * .
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function update_themes( $value, $transient ) {

			if ( 1 > self::$check_count ) {
				$tm_themes = $this->get_tm_themes();

				if ( empty( $tm_themes ) ) {
					return;
				}

				$new_update = array();

				foreach ( $tm_themes as $key => $theme ) {

					if ( empty( $theme['verificaton'] ) ) {
						continue;
					}

					$new_update[ $key ] = array(
						'version' => $theme['version'],
						'update'  => $theme['update'],
					);

					$response = $this->check_theme_update( $theme['template_id'], $theme['version'] );

					if ( $response && empty( $response->error ) ) {
						$new_update[ $key ]['update'] = $response->version;
					}
				}

				if ( ! empty( $new_update ) ) {
					update_option( 'tm_updates_themes', $new_update );
				}

				self::$check_count++;
			}

			return $value;
		}

		/**
		 * Update plugin options when theme roots changed.
		 *
		 * @since  1.0.0
		 * @param  mixed  $value
		 * @param  string $transient
		 * @return mixed
		 */
		public function theme_roots( $value, $transient ) {
			$last_update = $new_update = get_option( 'tm_updates_themes', array() );

			if ( empty( $last_update ) ) {
				return;
			}

			$tm_themes      = $this->search_tm_themes();
			$verified_theme = get_option( 'verified_themes', array() );

			foreach ( $last_update as $stylesheet => $versions ) {

				if ( array_key_exists( $stylesheet, $tm_themes ) ) {
					continue;
				}

				unset( $new_update[ $stylesheet ] );

				if ( ! empty( $verified_theme[ $stylesheet ] ) ) {
					unset( $verified_theme[ $stylesheet ] );
				}
			}

			if ( sizeof( $new_update ) != sizeof( $last_update  ) ) {
				update_option( 'tm_updates_themes', $new_update );
				update_option( 'verified_themes', $verified_theme );
			}

			return $value;
		}

		/**
		 * .
		 *
		 * @since  1.0.0
		 * @access public
		 * @return json
		 */
		public function check_theme_update( $theme_id, $current_version ) {
			$url      = sprintf( $this->api_upd, $theme_id, $current_version );
			$response = json_decode( $this->get_request( $url ) );

			if ( ! empty( $response->error ) && true === $response->error ) {
				$response = new stdClass();
				$response->version = $current_version;
				$response->error   = true;
			}

			return $response;
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

	if ( ! function_exists( 'tm_check_themes' ) ) {

		/**
		 * Returns instanse of the plugin class.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function tm_check_themes() {
			return TM_Check_Themes::get_instance();
		}
	}

	tm_check_themes()->add_action();
}
