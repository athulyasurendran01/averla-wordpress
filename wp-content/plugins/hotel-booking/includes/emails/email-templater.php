<?php

namespace MPHB\Emails;

use \MPHB\Views;

class EmailTemplater {

	private $tags		 = array();
	private $tagGroups	 = array();

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	private $booking;

	/**
	 *
	 * @var \MPHB\Entities\Payment
	 */
	private $payment;

	/**
	 *
	 * @param array $tagGroups
	 * @param bool $tagGroups['global'] Global site tags. Default TRUE.
	 * @param bool $tagGroups['booking'] Booking tags. Default FALSE.
	 * @param bool $tagGroups['user_confirmation'] User confirmation tags. Default FALSE.
	 */
	public function __construct( $tagGroups = array() ){

		$defaultTagGroups = array(
			'global'			 => true,
			'booking'			 => false,
			'user_confirmation'	 => false,
			'user_cancellation'	 => false,
			'payment'			 => false
		);

		$tagGroups = array_merge( $defaultTagGroups, $tagGroups );

		$this->tagGroups = $tagGroups;

		add_action( 'plugins_loaded', array( $this, 'setupTags' ) );
	}

	/**
	 *
	 * @param array $tagGroups
	 */
	public function setupTags(){

		$tags = array();

		if ( $this->tagGroups['global'] ) {
			$this->_fillGlobalTags( $tags );
		}

		if ( $this->tagGroups['booking'] ) {
			$this->_fillBookingTags( $tags );
		}

		if ( $this->tagGroups['user_confirmation'] ) {
			$this->_fillUserConfirmationTags( $tags );
		}

		if ( $this->tagGroups['user_cancellation'] && MPHB()->settings()->main()->canUserCancelBooking() ) {
			$this->_fillUserCancellationTags( $tags );
		}

		if ( $this->tagGroups['payment'] ) {
			$this->_fillPaymentTags( $tags );
		}

		$tags = apply_filters( 'mphb_email_tags', $tags );

		foreach ( $tags as $tag ) {
			$this->addTag( $tag['name'], $tag['description'] );
		}
	}

	private function _fillGlobalTags( &$tags ){
		$globalTags = array(
			array(
				'name'			 => 'site_title',
				'description'	 => __( 'Site title (set in Settings > General)', 'motopress-hotel-booking' ),
			)
		);

		$tags = array_merge( $tags, $globalTags );
	}

	private function _fillBookingTags( &$tags ){
		$bookingTags = array(
			// Booking
			array(
				'name'			 => 'booking_id',
				'description'	 => __( 'Booking ID', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'booking_edit_link',
				'description'	 => __( 'Booking Edit Link', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'booking_total_price',
				'description'	 => __( 'Booking Total Price', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_in_date',
				'description'	 => __( 'Check-in Date', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_out_date',
				'description'	 => __( 'Check-out Date', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_in_time',
				'description'	 => __( 'Check-in Time', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'check_out_time',
				'description'	 => __( 'Check-out Time', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'adults',
				'description'	 => __( 'Adults', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'children',
				'description'	 => __( 'Children', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'services',
				'description'	 => __( 'Services', 'motopress-hotel-booking' ),
			),
			// Customer
			array(
				'name'			 => 'customer_first_name',
				'description'	 => __( 'Customer First Name', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_last_name',
				'description'	 => __( 'Customer Last Name', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_email',
				'description'	 => __( 'Customer Email', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_phone',
				'description'	 => __( 'Customer Phone', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'customer_note',
				'description'	 => __( 'Customer Note', 'motopress-hotel-booking' ),
			),
			// Room Type
			array(
				'name'			 => 'room_type_id',
				'description'	 => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'room_type_link',
				'description'	 => __( 'Accommodation Type Link', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'room_type_title',
				'description'	 => __( 'Accommodation Type Title', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'room_type_categories',
				'description'	 => __( 'Accommodation Type Categories', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'room_type_bed_type',
				'description'	 => __( 'Accommodation Type Bed', 'motopress-hotel-booking' ),
			),
			array(
				'name'			 => 'room_rate_title',
				'description'	 => __( 'Accommodation Rate Title', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'room_rate_description',
				'description'	 => __( 'Accommodation Rate Description', 'motopress-hotel-booking' )
			)
		);

		$tags = array_merge( $tags, $bookingTags );
	}

	private function _fillUserConfirmationTags( &$tags ){
		$userConfirmationTags = array(
			array(
				'name'			 => 'user_confirm_link',
				'description'	 => __( 'Confirmation Link', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'user_confirm_link_expire',
				'description'	 => __( 'Confirmation Link Expiration Time ( UTC )', 'motopress-hotel-booking' )
			)
		);

		$tags = array_merge( $tags, $userConfirmationTags );
	}

	private function _fillUserCancellationTags( &$tags ){
		$userCancellationTags = array(
			array(
				'name'			 => 'user_cancel_link',
				'description'	 => __( 'User Cancellation Link', 'motopress-hotel-booking' )
			)
		);

		$tags = array_merge( $tags, $userCancellationTags );
	}

	private function _fillPaymentTags( &$tags ){
		$paymentTags = array(
			array(
				'name'			 => 'payment_amount',
				'description'	 => __( 'The total price of payment', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'payment_id',
				'description'	 => __( 'The unique ID of payment', 'motopress-hotel-booking' )
			),
			array(
				'name'			 => 'payment_method',
				'description'	 => __( 'The method of payment', 'motopress-hotel-booking' )
			),
		);

		$tags = array_merge( $tags, $paymentTags );
	}

	/**
	 *
	 * @param string $name
	 * @param string $description
	 */
	public function addTag( $name, $description ){
		if ( !empty( $name ) ) {
			$this->tags[$name] = array(
				'name'			 => $name,
				'description'	 => $description,
			);
		}
	}

	/**
	 *
	 * @param string $content
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	public function replaceTags( $content ){

		if ( !empty( $this->tags ) ) {
			$content = preg_replace_callback( $this->_generateTagsFindString(), array( $this, 'replaceTag' ), $content );
		}

		return $content;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function setupBooking( $booking ){
		$this->booking = $booking;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function setupPayment( $payment ){
		$this->payment = $payment;
	}

	/**
	 *
	 * @return string
	 */
	private function _generateTagsFindString(){

		$tagNames = array();
		foreach ( $this->tags as $tag ) {
			$tagNames[] = $tag['name'];
		}

		$find = '/%' . join( '%|%', $tagNames ) . '%/s';
		return $find;
	}

	/**
	 *
	 * @param array $match
	 * @param string $match[0] Tag
	 *
	 * @return string
	 */
	public function replaceTag( $match ){

		$tag = str_replace( '%', '', $match[0] );

		$replaceText = '';

		switch ( $tag ) {

			// Global
			case 'site_title':
				$replaceText = get_bloginfo( 'name' );
				break;
			case 'check_in_time':
				$replaceText = MPHB()->settings()->dateTime()->getCheckInTimeWPFormatted();
				break;
			case 'check_out_time':
				$replaceText = MPHB()->settings()->dateTime()->getCheckOutTimeWPFormatted();
				break;

			// Booking
			case 'booking_id':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getId();
				}
				break;
			case 'booking_edit_link':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getEditLink();
				}
				break;
			case 'booking_total_price':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderTotalPriceHTML( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'check_in_date':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderCheckInDateWPFormatted( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'check_out_date':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderCheckOutDateWPFormatted( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;
			case 'adults':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getAdults();
				}
				break;
			case 'children':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getChildren();
				}
				break;
			case 'services':
				if ( isset( $this->booking ) ) {
					ob_start();
					Views\BookingView::renderServicesList( $this->booking );
					$replaceText = ob_get_clean();
				}
				break;

			// Customer
			case 'customer_first_name':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getFirstName();
				}
				break;
			case 'customer_last_name':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getLastName();
				}
				break;
			case 'customer_email':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getEmail();
				}
				break;
			case 'customer_phone';
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getCustomer()->getPhone();
				}
				break;
			case 'customer_note':
				if ( isset( $this->booking ) ) {
					$replaceText = $this->booking->getNote();
				}
				break;
			case 'user_confirm_link':
				if ( isset( $this->booking ) ) {
					$replaceText = MPHB()->userActions()->getBookingConfirmationAction()->generateLink( $this->booking );
				}
				break;
			case 'user_confirm_link_expire':
				if ( isset( $this->booking ) ) {
					$expireTime	 = $this->booking->retrieveExpiration( 'user' );
					$replaceText = date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $expireTime );
				}
				break;
			case 'user_cancel_link':
				if ( isset( $this->booking ) ) {
					$replaceText = MPHB()->userActions()->getBookingCancellationAction()->generateLink( $this->booking );
				}
				break;

			// Room Type
			case 'room_type_id':
				if ( isset( $this->booking ) ) {
					$roomType	 = MPHB()->getRoomTypeRepository()->findById( $this->booking->getRoom()->getRoomTypeId() );
					$replaceText = ( $roomType ) ? $roomType->getId() : '';
				}
				break;
			case 'room_type_link':
				if ( isset( $this->booking ) ) {
					$roomTypeId	 = $this->booking->getRoom()->getRoomTypeId();
					$roomType	 = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
					$roomType	 = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getLink() : '';
				}
				break;
			case 'room_type_title':
				if ( isset( $this->booking ) ) {
					$roomType	 = MPHB()->getRoomTypeRepository()->findById( $this->booking->getRoom()->getRoomTypeId() );
					$roomType	 = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getTitle() : '';
				}
				break;
			case 'room_type_categories':
				if ( isset( $this->booking ) ) {
					$roomType	 = MPHB()->getRoomTypeRepository()->findById( $this->booking->getRoom()->getRoomTypeId() );
					$roomType	 = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getCategories() : '';
				}
				break;
			case 'room_type_bed_type':
				if ( isset( $this->booking ) ) {
					$roomType	 = MPHB()->getRoomTypeRepository()->findById( $this->booking->getRoom()->getRoomTypeId() );
					$roomType	 = apply_filters( '_mphb_translate_room_type', $roomType, $this->booking->getLanguage() );
					$replaceText = ( $roomType ) ? $roomType->getBedType() : '';
				}
				break;
			case 'room_rate_title':
				if ( isset( $this->booking ) ) {
					$roomRate	 = $this->booking->getRoomRate();
					$roomRate	 = apply_filters( '_mphb_translate_rate', $roomRate, $this->booking->getLanguage() );
					$replaceText = $roomRate->getTitle();
				}
				break;
			case 'room_rate_description':
				if ( isset( $this->booking ) ) {
					$roomRate	 = $this->booking->getRoomRate();
					$roomRate	 = apply_filters( '_mphb_translate_rate', $roomRate, $this->booking->getLanguage() );
					$replaceText = $roomRate->getDescription();
				}
				break;

			// Payment
			case 'payment_amount':
				if ( isset( $this->payment ) ) {
					$replaceText = mphb_format_price( $this->payment->getAmount(), array(
						'currency_symbol' => MPHB()->settings()->currency()->getBundle()->getSymbol( $this->payment->getCurrency() )
						) );
				}
				break;
			case 'payment_id':
				if ( isset( $this->payment ) ) {
					$replaceText = $this->payment->getId();
				}
				break;
			case 'payment_method':
				if ( isset( $this->payment ) ) {
					$gateway	 = MPHB()->gatewayManager()->getGateway( $this->payment->getGatewayId() );
					$replaceText = $gateway ? $gateway->getTitle() : '';
				}
				break;
		}

		$replaceText = apply_filters( 'mphb_email_replace_tag', $replaceText, $tag );

		return $replaceText;
	}

	/**
	 *
	 * @return string
	 */
	public function getTagsDescription(){
		$description = __( 'Possible tags:', 'motopress-hotel-booking' );
		$description .= '<br/>';
		if ( !empty( $this->tags ) ) {
			foreach ( $this->tags as $tagDetails ) {
				$description .= sprintf( '<em>%%%s%%</em> - %s<br/>', $tagDetails['name'], $tagDetails['description'] );
			}
		} else {
			$description .= '<em>' . __( 'none', 'motopress-hotel-booking' ) . '</em>';
		}

		return $description;
	}

}
