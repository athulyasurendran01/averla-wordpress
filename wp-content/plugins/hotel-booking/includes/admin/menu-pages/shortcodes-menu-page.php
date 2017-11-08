<?php

namespace MPHB\Admin\MenuPages;

class ShortcodesMenuPage extends AbstractMenuPage {

	private $shortcodes = array();

	public function addActions(){
		parent::addActions();
		add_action( 'admin_init', array( $this, 'initShortcodes' ) );
	}

	public function initShortcodes(){

		$this->shortcodes[MPHB()->getShortcodes()->getSearch()->getName()] = array(
			'label'			 => __( 'Availability Search Form', 'motopress-hotel-booking' ),
			'description'	 => __( 'Display search form.', 'motopress-hotel-booking' ),
			'parameters'	 => array(
				'adults'		 => array(
					'label'		 => __( 'The number of adults presetted in the search form.', 'motopress-hotel-booking' ),
					'values'	 => sprintf( '%d...%d', MPHB()->settings()->main()->getMinAdults(), MPHB()->settings()->main()->getSearchMaxAdults() ),
					'default'	 => strval( MPHB()->settings()->main()->getMinAdults() )
				),
				'children'		 => array(
					'label'		 => __( 'The number of children presetted in the search form.', 'motopress-hotel-booking' ),
					'values'	 => sprintf( '%d...%d', 0, MPHB()->settings()->main()->getSearchMaxChildren() ),
					'default'	 => strval( 0 ),
				),
				'check_in_date'	 => array(
					'label'		 => __( 'Check-in date presetted in the search form.', 'motopress-hotel-booking' ),
					'values'	 => sprintf( __( 'date in format %s', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormat() ),
					'default'	 => '',
				),
				'check_out_date' => array(
					'label'		 => __( 'Check-out date presetted in the search form.', 'motopress-hotel-booking' ),
					'values'	 => sprintf( __( 'date in format %s', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormat() ),
					'default'	 => '',
				),
				'class'			 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'		 => array(
				'shortcode' => MPHB()->getShortcodes()->getSearch()->generateShortcode(),
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getSearchResults()->getName()] = array(
			'label'			 => __( 'Availability Search Results', 'motopress-hotel-booking' ),
			'description'	 => __( 'Display listing of accommodation types that meet the search criteria.', 'motopress-hotel-booking' ),
			'parameters'	 => array(
				'title'				 => array(
					'label'		 => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'featured_image'	 => array(
					'label'		 => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'gallery'			 => array(
					'label'		 => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'excerpt'			 => array(
					'label'		 => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'details'			 => array(
					'label'		 => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'price'				 => array(
					'label'		 => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'view_button'		 => array(
					'label'			 => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description'	 => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'		 => 'true | false (yes,1,on | no,0,off)',
					'default'		 => 'true'
				),
				'book_button'		 => array(
					'label'			 => __( 'Show Book button', 'motopress-hotel-booking' ),
					'description'	 => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'		 => 'true | false (yes,1,on | no,0,off)',
					'default'		 => 'true'
				),
				'default_sorting'	 => array(
					'label'		 => __( 'Sort by', 'motopress-hotel-booking' ),
					'values'	 => 'order, price',
					'default'	 => 'order'
				),
				'class'				 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'		 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getSearchResults()->generateShortcode( array(
					'default_sorting' => 'price'
				) ),
				'description'	 => __( 'Search Results sorting by price.' ) . '<br/>' . '<strong>' . __( 'NOTE:', 'motopress-hotel-booking' ) . '</strong>&nbsp;' . sprintf( __( 'Use only on page that you set as Search Results Page in <a href="%s">Settings</a>', 'motopress-hotel-booking' ), MPHB()->getSettingsPageUrl() )
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getRooms()->getName()] = array(
			'label'		 => __( 'Accommodation Types Listing', 'motopress-hotel-booking' ),
			'parameters' => array(
				'title'			 => array(
					'label'		 => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'featured_image' => array(
					'label'		 => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'gallery'		 => array(
					'label'		 => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'excerpt'		 => array(
					'label'		 => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'details'		 => array(
					'label'		 => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'price'			 => array(
					'label'		 => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'view_button'	 => array(
					'label'			 => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description'	 => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'		 => 'true | false (yes,1,on | no,0,off)',
					'default'		 => 'true'
				),
				'book_button'	 => array(
					'label'			 => __( 'Show Book button', 'motopress-hotel-booking' ),
					'description'	 => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'		 => 'true | false (yes,1,on | no,0,off)',
					'default'		 => 'true'
				),
				'class'			 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'	 => array(
				'shortcode' => MPHB()->getShortcodes()->getRooms()->generateShortcode(),
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getServices()->getName()] = array(
			'label'		 => __( 'Services Listing', 'motopress-hotel-booking' ),
			'parameters' => array(
				'ids'	 => array(
					'label'			 => __( 'IDs', 'motopress-hotel-booking' ),
					'values'		 => __( 'Comma-Separated IDs.', 'motopress-hotel-booking' ),
					'description'	 => __( 'IDs of services that will be shown. ', 'motopress-hotel-booking' ),
				),
				'class'	 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'	 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getServices()->generateShortcode(),
				'description'	 => __( 'Show All Services', 'motopress-hotel-booking' )
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getRoom()->getName()] = array(
			'label'		 => __( 'Display Single Accommodation Type', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'				 => array(
					'label'			 => __( 'ID', 'motopress-hotel-booking' ),
					'description'	 => __( 'ID of accommodation type to display.', 'motopress-hotel-booking' ) . $this->getRequiredLabel(),
					'values'		 => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'title'				 => array(
					'label'		 => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false'
				),
				'featured_image'	 => array(
					'label'		 => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'gallery'			 => array(
					'label'		 => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'true'
				),
				'excerpt'			 => array(
					'label'		 => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false'
				),
				'details'			 => array(
					'label'		 => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false'
				),
				'price'				 => array(
					'label'		 => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false'
				),
				'view_button'		 => array(
					'label'			 => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description'	 => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'		 => 'true | false (yes,1,on | no,0,off)',
					'default'		 => 'false'
				),
				'book_button'		 => array(
					'label'		 => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false'
				),
				'class'				 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				),
				'price_per_night'	 => array(
					'label'		 => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'	 => 'true | false (yes,1,on | no,0,off)',
					'default'	 => 'false',
					'deprecated' => '1.2.0'
				),
			),
			'example'	 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getRoom()->generateShortcode( array(
					'id'			 => '777',
					'title'			 => 'true',
					'featured_image' => 'true'
				) ),
				'description'	 => __( 'Display accommodation type with title and image.', 'motopress-hotel-booking' )
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getCheckout()->getName()] = array(
			'label'			 => __( 'Checkout Form', 'motopress-hotel-booking' ),
			'description'	 => __( 'Display checkout form.', 'motopress-hotel-booking' ),
			'parameters'	 => array(
				'class' => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'		 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getCheckout()->generateShortcode(),
				'description'	 => '<strong>' . __( 'NOTE:', 'motopress-hotel-booking' ) . '</strong>&nbsp;' . sprintf( __( 'Use only on page that you set as Checkout Page in <a href="%s">Settings</a>', 'motopress-hotel-booking' ), MPHB()->getSettingsPageUrl() )
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getBookingForm()->getName()] = array(
			'label'		 => __( 'Booking Form', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'	 => array(
					'label'			 => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
					'description'	 => __( 'ID of Accommodation Type to check availability.', 'motopress-hotel-booking' ) . $this->getOptionalLabel(),
					'values'		 => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'class'	 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'	 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getBookingForm()->generateShortcode( array(
					'id' => '777'
				) ),
				'description'	 => __( 'Show Booking Form for Accommodation Type with id 777', 'motopress-hotel-booking' )
			)
		);

		$this->shortcodes[MPHB()->getShortcodes()->getRoomRates()->getName()] = array(
			'label'		 => __( 'Accommodation Rates List', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'	 => array(
					'label'			 => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
					'description'	 => __( 'ID of accommodation type.', 'motopress-hotel-booking' ) . $this->getOptionalLabel(),
					'values'		 => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'class'	 => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'example'	 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getRoomRates()->generateShortcode( array(
					'id' => '777'
				) ),
				'description'	 => __( 'Show Accommodation Rates List for accommodation type with id 777', 'motopress-hotel-booking' )
			)
		);

		$bookingConfirmationPage = get_edit_post_link( MPHB()->settings()->pages()->getBookingConfirmPageId() );

		$this->shortcodes[MPHB()->getShortcodes()->getBookingConfirmation()->getName()] = array(
			'label'			 => __( 'Booking Confirmation.', 'motopress-hotel-booking' ),
			'parameters'	 => array(
				'class' => array(
					'label'		 => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'	 => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default'	 => ''
				)
			),
			'description'	 => __( 'Display booking confirmation message.', 'motopress-hotel-booking' ),
			'example'		 => array(
				'shortcode'		 => MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode(),
				'description'	 => !empty( $bookingConfirmationPage ) ? sprintf( __( 'Use this shortcode on <a href="%s">Booking Confirmation Page</a>', 'motopress-hotel-booking' ), $bookingConfirmationPage ) : __( 'Use this shortcode on Booking Confirmation Page', 'motopress-hotel-booking' ),
			)
		);
	}

	public function render(){
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Shortcodes', 'motopress-hotel-booking' ); ?></h1>
			<table class="widefat striped">
				<thead>
					<tr class="">
						<td><?php _e( 'Shortcode', 'motopress-hotel-booking' ); ?></td>
						<td><?php _e( 'Parameters', 'motopress-hotel-booking' ); ?></td>
						<td><?php _e( 'Example', 'motopress-hotel-booking' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->shortcodes as $name => $details ) { ?>
						<tr valign="top" >
							<th scope="row">
								<?php $this->renderShortcodeCell( $name, $details ); ?>
							</th>
							<td scope="row">
								<?php $this->renderParametersCell( $name, $details ); ?>
							</td>
							<td scope="row">
								<?php $this->renderExampleCell( $name, $details ); ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private function renderShortcodeCell( $name, $details ){
		?>
		<strong>
			<?php echo $details['label']; ?>
		</strong>
		<p>
			<code>[<?php echo $name; ?>]</code>
		</p>
		<?php
	}

	private function renderParametersCell( $name, $details ){
		if ( empty( $details['parameters'] ) ) {
			?>
			<span aria-hidden="true">&#8212;</span>
		<?php } else { ?>
			<?php foreach ( $details['parameters'] as $paramName => $paramDetails ) { ?>
				<p>
					<code><?php echo $paramName; ?></code>
					<?php if ( isset( $paramDetails['deprecated'] ) && $paramDetails['deprecated'] ) { ?>
						<strong><?php printf( __( 'Deprecated since %s', 'motopress-hotel-booking' ), $paramDetails['deprecated'] ); ?></strong>
					<?php } ?>
					<em><?php echo $paramDetails['label']; ?></em>
				</p>
				<?php if ( isset( $paramDetails['description'] ) ) { ?>
					<p class="description">
						<?php echo $paramDetails['description']; ?>
					</p>
				<?php } ?>
				<p>
					<em><?php _e( 'Values:', 'motopress-hotel-booking' ); ?></em>
					<?php echo $paramDetails['values']; ?>
				</p>
				<?php if ( isset( $paramDetails['default'] ) ) { ?>
					<p>
						<em><?php _e( 'Default:', 'motopress-hotel-booking' ); ?></em>

						<?php
						switch ( $paramDetails['default'] ) {
							case '':
								_e( 'empty string', 'motopress-hotel-booking' );
								break;
							default:
								echo $paramDetails['default'];
								break;
						}
						?>

					</p>
				<?php } ?>
				<hr/>
			<?php } ?>
			<?php
		}
	}

	private function renderExampleCell( $name, $details ){
		?>
		<p>
			<code>
				<?php echo $details['example']['shortcode']; ?>
			</code>
		</p>
		<?php if ( isset( $details['example']['description'] ) ) { ?>
			<p class="description">
				<?php echo $details['example']['description']; ?>
			</p>
		<?php } ?>
		<?php
	}

	public function onLoad(){

	}

	/**
	 *
	 * @return string
	 */
	private function getOptionalLabel(){
		return '<em>' . __( 'Optional.', 'motopress-hotel-booking' ) . '</em>';
	}

	/**
	 *
	 * @return string
	 */
	private function getRequiredLabel(){
		return '<strong>' . __( 'Required', 'motopress-hotel-booking' ) . '</strong>';
	}

	protected function getMenuTitle(){
		return __( 'Shortcodes', 'motopress-hotel-booking' );
	}

	protected function getPageTitle(){
		return __( 'MotoPress Hotel Booking Shortcodes Parameters', 'motopress-hotel-booking' );
	}

}
