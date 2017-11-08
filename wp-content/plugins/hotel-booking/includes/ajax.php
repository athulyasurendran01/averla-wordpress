<?php

namespace MPHB;

use \MPHB\Entities;
use \MPHB\Views;
use \MPHB\Repositories;

class Ajax {

	private $nonceName	 = 'mphb_nonce';
	private $ajaxEvents	 = array(
		'check_room_availability'	 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'recalculate_total'			 => array(
			'method' => 'GET',
			'nopriv' => false
		),
		'update_checkout_info'		 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'get_billing_fields'		 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'get_rates_for_room'		 => array(
			'method' => 'GET',
			'nopriv' => false
		),
		'dismiss_license_notice'	 => array(
			'method' => 'POST',
			'nopriv' => false
		),
	);

	public function __construct(){

		foreach ( $this->ajaxEvents as $action => $details ) {
			$noPriv = isset( $details['nopriv'] ) ? $details['nopriv'] : false;
			$this->addAjaxAction( $action, $noPriv );
		}
	}

	/**
	 *
	 * @param string $action
	 * @param bool $noPriv
	 */
	public function addAjaxAction( $action, $noPriv ){

		add_action( 'wp_ajax_mphb_' . $action, array( $this, $action ) );

		if ( $noPriv ) {
			add_action( 'wp_ajax_nopriv_mphb_' . $action, array( $this, $action ) );
		}
	}

	/**
	 *
	 * @param string $action
	 * @return bool
	 */
	public function checkNonce( $action ){

		if ( !isset( $this->ajaxEvents[$action] ) ) {
			return false;
		}

		$method = isset( $this->ajaxEvents[$action]['method'] ) ? $this->ajaxEvents[$action]['method'] : '';

		switch ( $method ) {
			case 'GET':
				$nonce	 = isset( $_GET[$this->nonceName] ) ? $_GET[$this->nonceName] : '';
				break;
			case 'POST':
				$nonce	 = isset( $_POST[$this->nonceName] ) ? $_POST[$this->nonceName] : '';
				break;
			default:
				$nonce	 = isset( $_REQUEST[$this->nonceName] ) ? $_REQUEST[$this->nonceName] : '';
		}

		return wp_verify_nonce( $nonce, 'mphb_' . $action );
	}

	/**
	 *
	 * @param string $action
	 */
	public function verifyNonce( $action ){
		if ( !$this->checkNonce( $action ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking' )
			) );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAdminNonces(){
		$nonces = array();
		foreach ( $this->ajaxEvents as $evtName => $evtDetails ) {
			$nonces['mphb_' . $evtName] = wp_create_nonce( 'mphb_' . $evtName );
		}
		return $nonces;
	}

	/**
	 *
	 * @return arrray
	 */
	public function getFrontNonces(){
		$nonces = array();
		foreach ( $this->ajaxEvents as $evtName => $evtDetails ) {
			if ( isset( $evtDetails['nopriv'] ) && $evtDetails['nopriv'] ) {
				$nonces['mphb_' . $evtName] = wp_create_nonce( 'mphb_' . $evtName );
			}
		}
		return $nonces;
	}

	public function check_room_availability(){

		$this->verifyNonce( __FUNCTION__ );

		// Check is request parameters setted
		if ( !( isset( $_GET['roomTypeId'] ) && isset( $_GET['checkInDate'] ) && isset( $_GET['checkOutDate'] ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' ),
			) );
		}

		$checkInDate = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $_GET['checkInDate'] );
		if ( !$checkInDate ) {
			wp_send_json_error( array(
				'message' => __( 'Check-in date is not valid.', 'motopress-hotel-booking' ),
			) );
		}
		$todayDate = \DateTime::createFromFormat( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );
		if ( \MPHB\Utils\DateUtils::calcNights( $todayDate, $checkInDate ) < 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' ),
			) );
		}

		$checkOutDate = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $_GET['checkOutDate'] );
		if ( !$checkOutDate ) {
			wp_send_json_error( array(
				'message' => __( 'Check-out date is not valid.', 'motopress-hotel-booking' ),
			) );
		}
		if ( !MPHB()->getRulesChecker()->verify( $checkInDate, $checkOutDate ) ) {
			wp_send_json_error( array(
				'message' => __( 'Dates do not satisfy booking rules.', 'motopress-hotel-booking' ),
			) );
		}

		$roomType = MPHB()->getRoomTypeRepository()->findById( absint( $_GET['roomTypeId'] ) );

		if ( !$roomType ) {
			wp_send_json_error( array(
				'message' => __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ),
			) );
		}

		$ratesAtts = array(
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate,
		);

		$rates = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomType->getId(), $ratesAtts );
		if ( empty( $rates ) ) {
			wp_send_json_error( array(
				'message' => __( 'There are not available rates for current accommodation for requested period.', 'motopress-hotel-booking' ),
			) );
		}

		if ( !$roomType->hasAvailableRoom( $checkInDate, $checkOutDate ) ) {
			wp_send_json_error( array(
				'message'		 => __( 'Accommodation is unavailable for requested dates.', 'motopress-hotel-booking' ),
				'updatedData'	 => array(
					'dates'				 => array(
						'booked'	 => $roomType->getBookingsCountByDay(),
						'havePrice'	 => $roomType->getDatesHavePrice()
					),
					'activeRoomsCount'	 => count( MPHB()->getRoomRepository()->findAllByRoomType( $roomType->getId() ) )
				)
			) );
		}

		wp_send_json_success();
	}

	public function recalculate_total(){

		$this->verifyNonce( __FUNCTION__ );

		if ( !( isset( $_GET['formValues'] ) && is_array( $_GET['formValues'] ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'An error has occurred, please try again later.', 'motopress-hotel-booking' ),
			) );
		}

		$atts = MPHB()->postTypes()->booking()->getEditPage()->getAttsFromRequest( $_GET['formValues'] );

		// Check Required Fields
		if ( !isset( $atts['mphb_room_id'] ) || empty( $atts['mphb_room_id'] ) ||
			!isset( $atts['mphb_room_rate_id'] ) || $atts['mphb_room_rate_id'] === '' ||
			!isset( $atts['mphb_check_in_date'] ) || empty( $atts['mphb_check_in_date'] ) ||
			!isset( $atts['mphb_check_out_date'] ) || empty( $atts['mphb_check_out_date'] )
		) {
			wp_send_json_error( array(
				'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' )
			) );
		}

		$room = MPHB()->getRoomRepository()->findById( $atts['mphb_room_id'] );
		if ( !$room ) {
			wp_send_json_error( array(
				'message' => __( 'Accommodation is not valid.', 'motopress-hotel-booking' )
			) );
		}

		$roomType = MPHB()->getRoomTypeRepository()->findById( $room->getRoomTypeId() );
		if ( !$roomType ) {
			wp_send_json_error( array(
				'message' => __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' )
			) );
		}

		$roomRate = MPHB()->getRateRepository()->findById( $atts['mphb_room_rate_id'] );
		if ( !$roomRate ) {
			wp_send_json_error( array(
				'message' => __( 'Rate is not valid.', 'motopress-hotel-booking' )
			) );
		}

		$adults			 = absint( $atts['mphb_adults'] );
		$children		 = absint( $atts['mphb_children'] );
		$checkInDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_in_date'] );
		$checkOutDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_out_date'] );

		$services = array();
		if ( isset( $atts['mphb_services'] ) && is_array( $atts['mphb_services'] ) ) {
			foreach ( $atts['mphb_services'] as $serviceDetails ) {

				if ( !isset( $serviceDetails['id'], $serviceDetails['count'] ) ) {
					continue;
				}

				$service = MPHB()->getServiceRepository()->findById( $serviceDetails['id'] );
				if ( !$service ) {
					continue;
				}

				$count = filter_var( $serviceDetails['count'], FILTER_VALIDATE_INT );
				if ( $count === false || $count < 1 ) {
					continue;
				}

				$services[] = array(
					'id'	 => $service->getId(),
					'count'	 => $count
				);
			}
		}

		$bookingAtts = array(
			'room'			 => $room,
			'room_rate'		 => $roomRate,
			'adults'		 => $adults,
			'children'		 => $children,
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate,
			'services'		 => $services
		);

		$booking = Entities\Booking::create( $bookingAtts );

		wp_send_json_success( array(
			'total' => $booking->calcPrice()
		) );
	}

	/**
	 *
	 * @return Entities\Booking
	 */
	private function parseCheckoutFormBooking(){

		$isSetedRequiredFields = isset( $_GET['formValues'] ) &&
			is_array( $_GET['formValues'] ) &&
			isset( $_GET['formValues']['mphb_room_type_id'] ) &&
			$_GET['formValues']['mphb_room_type_id'] !== '' &&
			isset( $_GET['formValues']['mphb_room_rate_id'] ) &&
			$_GET['formValues']['mphb_room_rate_id'] !== '' &&
			isset( $_GET['formValues']['mphb_check_in_date'] ) &&
			$_GET['formValues']['mphb_check_in_date'] !== '' &&
			isset( $_GET['formValues']['mphb_check_out_date'] ) &&
			$_GET['formValues']['mphb_check_out_date'] !== '';

		if ( !$isSetedRequiredFields ) {
			wp_send_json_error( array(
				'message' => __( 'An error has occurred. Please try again later.', 'motopress-hotel-booking' ),
			) );
		}

		$atts = MPHB()->postTypes()->booking()->getEditPage()->getAttsFromRequest( $_GET['formValues'] );

		$atts['mphb_room_type_id']	 = absint( $_GET['formValues']['mphb_room_type_id'] );
		$atts['mphb_room_rate_id']	 = absint( $atts['mphb_room_rate_id'] );

		$roomType		 = MPHB()->getRoomTypeRepository()->findById( $atts['mphb_room_type_id'] );
		$roomRate		 = MPHB()->getRateRepository()->findById( absint( $atts['mphb_room_rate_id'] ) );
		$checkInDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_in_date'] );
		$checkOutDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_out_date'] );
		$adults			 = absint( $atts['mphb_adults'] );
		$children		 = absint( $atts['mphb_children'] );

		$allowedServices = $roomType->getServices();

		$services = array();

		if ( !empty( $atts['mphb_services'] ) ) {
			foreach ( $atts['mphb_services'] as $serviceDetails ) {

				if ( empty( $serviceDetails['id'] ) || !in_array( $serviceDetails['id'], $allowedServices ) ) {
					continue;
				}

				$count = filter_var( $serviceDetails['count'], FILTER_VALIDATE_INT );
				if ( $count === false || $count < 1 ) {
					continue;
				}

				$services[] = array(
					'id'	 => $serviceDetails['id'],
					'count'	 => $count
				);
			}
		}

		$bookingAtts = array(
			'room_rate'		 => $roomRate,
			'adults'		 => $adults,
			'children'		 => $children,
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate,
			'services'		 => $services
		);

		$booking = Entities\Booking::create( $bookingAtts );

		return $booking;
	}

	public function update_checkout_info(){

		$this->verifyNonce( __FUNCTION__ );

		$booking = $this->parseCheckoutFormBooking();

		$responseData = array(
			'total'			 => mphb_format_price( $booking->calcPrice() ),
			'priceBreakdown' => Views\BookingView::generatePriceBreakdown( $booking ),
		);

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$responseData['deposit'] = mphb_format_price( $booking->calcDepositAmount() );

			$responseData['gateways'] = array_map( function($gateway) use ($booking) {
				return $gateway->getCheckoutData( $booking );
			}, MPHB()->gatewayManager()->getListActive() );
		}

		wp_send_json_success( $responseData );
	}

	public function get_billing_fields(){

		$this->verifyNonce( __FUNCTION__ );

		$gatewayId = !empty( $_GET['mphb_gateway_id'] ) ? mphb_clean( $_GET['mphb_gateway_id'] ) : '';

		if ( !array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			wp_send_json_error( array(
				'message' => __( 'Chosen payment method is not available. Please refresh the page and try one more time.', 'motopress-hotel-booking' ),
			) );
		}

		$booking = $this->parseCheckoutFormBooking();

		ob_start();
		MPHB()->gatewayManager()->getGateway( $gatewayId )->renderPaymentFields( $booking );
		$fields = ob_get_clean();

		wp_send_json_success( array(
			'fields'			 => $fields,
			'hasVisibleFields'	 => MPHB()->gatewayManager()->getGateway( $gatewayId )->hasVisiblePaymentFields()
		) );
	}

	public function get_rates_for_room(){

		$this->verifyNonce( __FUNCTION__ );

		$titlesList = array();

		if (
			isset( $_GET['formValues'] ) &&
			is_array( $_GET['formValues'] ) &&
			!empty( $_GET['formValues']['mphb_room_id'] )
		) {
			$roomId	 = absint( $_GET['formValues']['mphb_room_id'] );
			$room	 = MPHB()->getRoomRepository()->findById( $roomId );

			if ( !$room ) {
				wp_send_json_success( array(
					'options' => array()
				) );
			}

			foreach ( MPHB()->getRateRepository()->findAllActiveByRoomType( $room->getRoomTypeId() ) as $rate ) {
				$titlesList[$rate->getId()] = $rate->getTitle();
			}
		}

		wp_send_json_success( array(
			'options' => $titlesList
		) );
	}

	public function dismiss_license_notice(){

		$this->verifyNonce( __FUNCTION__ );

		MPHB()->settings()->license()->setNeedHideNotice( true );

		wp_send_json_success();
	}

}
