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

// If class `Tm_Themes_Forms` doesn't exists yet.
if ( ! class_exists( 'Tm_Themes_Forms' ) ) {

	/**
	 * Tm_Themes_Forms class.
	 */
	class Tm_Themes_Forms {

		/**
		 * A reference to an instance of `Cherry_Interface_Builder` class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $builder = null;

		public $button_element = '<span class="tm-dashboard-btn__loader"></span><span class="tm-dashboard-btn__icon tm-dashboard-btn__icon--yes dashicons dashicons-yes"></span><span class="tm-dashboard-btn__icon tm-dashboard-btn__icon--no dashicons dashicons-no"></span>';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			tm_dashboard()->get_core()->init_module( 'cherry-js-core' );
			$this->builder = tm_dashboard()->get_core()->init_module( 'cherry-interface-builder' );
		}

		public function get_theme_info_block( $args ) {
			$current_version     = $args['version'];
			$version_update      = $args['update'] ? $args['update'] : $current_version;
			$disabled            = version_compare( $current_version, $version_update, '<' ) ? false : true;
			$update_button_class = $disabled ? 'disabled': 'tm-update-theme utb-js';
			$available_class     = $disabled ? '': 'available-version--new';

			$this->builder->reset_structure();
			$this->builder->register_control( array(
				'check-theme-' . $args['slug']  => array(
					'type'          => 'button',
					'style'         => 'success',
					'view_wrapping' => false,
					'disabled'      => $disabled,
					'content'       => '<span class="tm-dashboard-btn__text">' . esc_html__( 'Update', 'tm-dashboard' ) . '</span>' . $this->button_element,
					'form'          => $args['slug'],
					'child_class'   => 'tm-dashboard-btn updater-theme-button ' . $update_button_class,
				),
			) );

			$this->builder->register_html( array(
				'view_wrapping' => false,
				'id'            => 'update-version',
				'html'          => '<input type="hidden" name="update-version" value="' . $version_update . '"><input type="hidden" name="version" value="' . $current_version . '">',
			) );

			$buttons = $this->builder->render( false );

			return sprintf(
				'<h2 class="tm-dashboard-h2-like">%6$s</h2>
				<table class="tm-updates__theme-info-table">
					<tr><td>%1$s</td><td class="current-version">%2$s</td></tr>
					<tr><td>%3$s</td><td class="available-version %7$s">%4$s</td></tr>
				</table>
				%5$s',
				esc_html__( 'Theme version:', 'tm-dashboard' ),
				$current_version,
				esc_html__( 'Updates available:', 'tm-dashboard' ),
				$version_update,
				$buttons,
				esc_html__( 'Update' ),
				esc_attr( $available_class )
			);
		}

		public function get_verificaton_block( $args ) {
			$hidden_fields = array();
			$form_fields   = array();
			$notice        = array(
				'id'   => 'tm-updater-notice',
				'html' => esc_html__( 'In order to get your theme updatings please enter the order ID and your theme ID.', 'tm-dashboard' ),
			);
			$title = array(
				'id'   => 'tm-verification-title',
				'html' => sprintf( '<h2 class="tm-dashboard-h2-like">%s</h2>', esc_html__( 'Verification', 'tm-dashboard' ) ),
				'view_wrapping' => false,
			);

			if ( $args['template_id'] ) {
				$hidden_fields['product-id'] = array(
					'view_wrapping' => false,
					'html'          => '<input type="hidden" name="product-id" value="' . $args['template_id'] . '">',
				);

				$notice = array(
					'id'   => 'tm-updates-notice',
					'html' => esc_html__( 'In order to get your theme updatings please enter the order ID.', 'tm-dashboard' ),
				);

			} else {
				$form_fields['product-id'] = array(
					'type'          => 'text',
					'value'         => '',
					'view_wrapping' => false,
					'placeholder'   => esc_html__( 'Template ID', 'tm-dashboard' ),
					'class'         => '',
					'label'         => '',
				);
			}

			$form_fields['order-id'] = array(
				'type'          => 'text',
				'value'         => '',
				'view_wrapping' => false,
				'placeholder'   => esc_html__( 'Order ID', 'tm-dashboard' ),
				'class'         => '',
				'label'         => '',
			);

			$form_fields[ 'verified-theme-' . $args['slug'] ] = array(
				'type'          => 'button',
				'style'         => 'primary',
				'view_wrapping' => false,
				'content'       => '<span class="tm-dashboard-btn__text">' . esc_html__( 'Submit', 'tm-dashboard' ) . '</span>' . $this->button_element,
				'form'          => $args['slug'],
				'child_class'   => 'tm-dashboard-btn updater-theme-button utb-js verified-theme',
			);

			$hidden_fields['theme-slug'] = array(
				'view_wrapping' => false,
				'html'          => '<input type="hidden" name="slug" value="' . $args['slug'] . '">',
			);

			$this->builder->reset_structure();

			$this->builder->register_html( $title );
			$this->builder->register_html( $notice );
			$this->builder->register_control( $form_fields );
			$this->builder->register_html( $hidden_fields );

			return $this->builder->render( false );
		}
	}
}
