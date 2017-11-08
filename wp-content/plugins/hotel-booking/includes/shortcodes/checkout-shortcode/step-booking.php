<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;

class StepBooking extends Step {

	/**
	 *
	 * @var int
	 */
	protected $roomId;

	/**
	 *
	 * @var string
	 */
	protected $firstName;

	/**
	 *
	 * @var string
	 */
	protected $lastName;

	/**
	 *
	 * @var string
	 */
	protected $email;

	/**
	 *
	 * @var string
	 */
	protected $phone;

	/**
	 *
	 * @var string
	 */
	protected $note;

	/**
	 *
	 * @var array
	 */
	protected $services = array();

	/**
	 *
	 * @var string
	 */
	protected $gatewayId;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectCustomerData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectPaymentData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isAlreadyBooked = false;

	/**
	 *
	 * @var boolean
	 */
	protected $unableToCreateBooking = false;

	public function setup(){

		$isCorrectBookingData	 = $this->parseBookingData();
		$isCorrectServices		 = $this->parseServices();
		$isCorrectCustomerData	 = $this->parseCustomerData();

		$this->isCorrectData = $isCorrectBookingData && $isCorrectServices && $isCorrectCustomerData;

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$this->isCorrectData = $this->isCorrectData && $this->parsePaymentData();
		}

		if ( !$this->isCorrectData ) {
			return;
		}

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING );

		$roomId = $this->roomType->getNextAvailableRoom( $this->checkInDate->format( 'Y-m-d' ), $this->checkOutDate->format( 'Y-m-d' ) );

		if ( !$roomId ) {
			$this->isAlreadyBooked = true;
			return;
		}

		$booking = $this->createBooking( $roomId );

		if ( is_null( $booking ) ) {
			$this->unableToCreateBooking = true;
			return;
		}

		do_action( 'mphb_create_booking_by_user', $booking );

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_COMPLETE );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$payment = $this->createPayment( $booking );
			$booking->waitPayment( $payment->getId() );
			MPHB()->gatewayManager()->getGateway( $this->gatewayId )->processPayment( $booking, $payment );
		}

		$this->stepValid();
	}

	/**
	 *
	 * @return boolean
	 */
	public function parseRoomRate(){
		$roomRateId = filter_input( INPUT_POST, 'mphb_room_rate_id', FILTER_VALIDATE_INT );

		if ( !$roomRateId ) {
			$this->errors[] = __( 'Accommodation Rate ID is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$rate = null;
		foreach ( $this->allowedRates as $allowedRate ) {
			if ( $allowedRate->getId() === $roomRateId ) {
				$rate = $allowedRate;
			}
		}

		if ( is_null( $rate ) ) {
			$this->errors[] = __( 'Accommodation Rate ID is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->roomRate = $rate;

		return true;
	}

	protected function parseCustomerData(){
		$isCorrectFirstName	 = $this->parseFirstName();
		$isCorrectLastName	 = $this->parseLastName();
		$isCorrectEmail		 = $this->parseEmail();
		$isCorrectPhone		 = $this->parsePhone();
		$isCorrectNote		 = $this->parseNote();

		$this->isCorrectCustomerData = ( $isCorrectFirstName && $isCorrectLastName && $isCorrectEmail && $isCorrectPhone && $isCorrectNote );

		return $this->isCorrectCustomerData;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseFirstName(){
		$this->firstName = null;
		if ( !isset( $_POST['mphb_first_name'] ) ) {
			$this->errors[] = __( 'First name is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->firstName = sanitize_text_field( $_POST['mphb_first_name'] );
		return true;
	}

	protected function parseLastName(){
		$this->lastName = isset( $_POST['mphb_last_name'] ) ? sanitize_text_field( $_POST['mphb_last_name'] ) : '';
		return true;
	}

	protected function parseEmail(){
		$this->email = null;
		$email		 = isset( $_POST['mphb_email'] ) ? sanitize_email( $_POST['mphb_email'] ) : '';
		if ( !empty( $email ) ) {
			$this->email = $email;
			return true;
		} else {
			$this->errors[] = __( 'Email is incorrect.', 'motopress-hotel-booking' );
			return false;
		}
	}

	protected function parsePhone(){
		$this->phone = null;
		if ( isset( $_POST['mphb_phone'] ) ) {
			$this->phone = sanitize_text_field( $_POST['mphb_phone'] );
			return true;
		} else {
			$this->errors[] = __( 'Phone is incorrect.', 'motopress-hotel-booking' );
			return false;
		}
	}

	protected function parseNote(){
		$this->note = isset( $_POST['mphb_note'] ) ? sanitize_text_field( $_POST['mphb_note'] ) : '';
		return true;
	}

	protected function parseServices(){
		$this->services = array();
		if ( isset( $_POST['mphb_services'] ) && is_array( $_POST['mphb_services'] ) ) {
			foreach ( $_POST['mphb_services'] as $serviceDetails ) {

				if ( !isset( $serviceDetails['id'], $serviceDetails['count'] ) ) {
					// service checkbox is not checked
					continue;
				}

				$service = MPHB()->getServiceRepository()->findById( absint( $serviceDetails['id'] ) );

				if ( !$service ) {
					continue;
				}

				$count = filter_var( $serviceDetails['count'], FILTER_VALIDATE_INT );
				if ( $count === false || $count < 1 ) {
					continue;
				}

				$this->services[] = array(
					'id'	 => $service->getId(),
					'count'	 => $count
				);
			}
		}
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parsePaymentData(){
		$this->isCorrectPaymentData = $this->parseGatewayId() && $this->parsePaymentMethodFields();
		return $this->isCorrectPaymentData;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseGatewayId(){
		if ( !isset( $_POST['mphb_gateway_id'] ) ) {
			return false;
		}
		$gatewayId = $_POST['mphb_gateway_id'];

		if ( !array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			$this->errors[] = __( 'Payment method is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->gatewayId = $gatewayId;

		return true;
	}

	protected function parsePaymentMethodFields(){
		$errors = array();

		MPHB()->gatewayManager()->getGateway( $this->gatewayId )->parsePaymentFields( $_POST, $errors );

		if ( !empty( $errors ) ) {
			$this->errors = array_merge( $this->errors, $errors );
			return false;
		}

		return true;
	}

	public function render(){

		if ( !$this->isCorrectData ) {
			$this->showErrorsMessage();
		} else if ( $this->isAlreadyBooked ) {
			$this->showAlreadyBookedMessage();
		} else if ( $this->unableToCreateBooking ) {
			_e( 'Unable to create booking. Please try again.', 'motopress-hotel-booking' );
		} else {
			$this->showSuccessMessage();
		}
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 * @return Entities\Payment|null
	 */
	protected function createPayment( $booking ){

		$gateway = MPHB()->gatewayManager()->getGateway( $this->gatewayId );

		$paymentData = array(
			'gatewayId'		 => $gateway->getId(),
			'gatewayMode'	 => $gateway->getMode(),
			'bookingId'		 => $booking->getId(),
			'amount'		 => $booking->calcDepositAmount(),
			'currency'		 => MPHB()->settings()->currency()->getCurrencyCode(),
		);

		$payment	 = Entities\Payment::create( $paymentData );
		$isCreated	 = MPHB()->getPaymentRepository()->save( $payment );

		if ( $isCreated ) {
			$gateway->storePaymentFields( $payment );
			// Re-get payment. Some gateways may update metadata without entity update.
			$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );
		}

		return $isCreated ? $payment : null;
	}

	/**
	 *
	 * @param int $roomId
	 * @return Entities\Booking|null
	 */
	public function createBooking( $roomId ){
		$customerAtts = array(
			'first_name' => $this->firstName,
			'last_name'	 => $this->lastName,
			'email'		 => $this->email,
			'phone'		 => $this->phone
		);

		$customer = new Entities\Customer( $customerAtts );

		$bookingAtts = array(
			'check_in_date'	 => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
			'adults'		 => $this->adults,
			'children'		 => $this->children,
			'services'		 => $this->services,
			'customer'		 => $customer,
			'note'			 => $this->note,
			'room'			 => MPHB()->getRoomRepository()->findById( $roomId ),
			'room_rate'		 => $this->roomRate,
			'status'		 => MPHB()->postTypes()->booking()->statuses()->getDefaultNewBookingStatus()
		);

		$booking	 = Entities\Booking::create( $bookingAtts );
		$isCreated	 = MPHB()->getBookingRepository()->save( $booking );

		return $isCreated ? $booking : null;
	}

}
