<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

abstract class Step {

	protected $isValidStep = false;

	/**
	 *
	 * @var bool
	 */
	protected $isCorrectBookingData;

	/**
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 *
	 * @var \MPHB\Entities\RoomType
	 */
	protected $roomType;

	/**
	 *
	 * @var int
	 */
	protected $adults;

	/**
	 *
	 * @var int
	 */
	protected $children;

	/**
	 *
	 * @var \DateTime
	 */
	protected $checkInDate;

	/**
	 *
	 * @var \DateTime
	 */
	protected $checkOutDate;

	/**
	 *
	 * @var \MPHB\Entities\Rate[]
	 */
	protected $allowedRates;

	abstract public function setup();

	abstract public function render();

	/**
	 *
	 * @return boolean
	 */
	protected function parseBookingData(){
		$isCorrectRoomType = $this->parseRoomType();

		// Other booking attributes are depend on correct room type
		if ( !$isCorrectRoomType ) {
			return false;
		}

		$isCorrectCheckInDate	 = $this->parseCheckInDate();
		$isCorrectCheckOutDate	 = $this->parseCheckOutDate();
		$isCorrectAdults		 = $this->parseAdults();
		$isCorrectChildren		 = $this->parseChildren();
		$hasAllowedRates		 = $this->setupAllowedRates();

		$this->isCorrectBookingData = $isCorrectCheckInDate &&
			$isCorrectCheckOutDate &&
			$isCorrectAdults &&
			$isCorrectChildren &&
			$hasAllowedRates &&
			$this->parseRoomRate();

		return $this->isCorrectBookingData;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseRoomType(){
		$filterOptions = array(
			'options' => array(
				'min_range' => 1
			)
		);

		$roomTypeId = filter_input( INPUT_POST, 'mphb_room_type_id', FILTER_VALIDATE_INT, $filterOptions );

		if ( !$roomTypeId || get_post_status( $roomTypeId ) !== 'publish' ) {
			$this->errors[] = __( 'Accommodation Type ID is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
		if ( !$roomType ) {
			$this->errors[] = __( 'Accommodation Type ID is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->roomType = $roomType;

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseAdults(){
		$filterOptions = array(
			'options' => array(
				'min_range'	 => MPHB()->settings()->main()->getMinAdults(),
				'max_range'	 => $this->roomType->getAdultsCapacity()
			)
		);

		$adults = filter_input( INPUT_POST, 'mphb_adults', FILTER_VALIDATE_INT, $filterOptions );

		if ( is_null( $adults ) || false === $adults ) {
			$this->errors[] = __( 'Adults number is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->adults = $adults;

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseChildren(){
		$filterOptions = array(
			'options' => array(
				'min_range'	 => 0,
				'max_range'	 => $this->roomType->getChildrenCapacity()
			)
		);

		$children = filter_input( INPUT_POST, 'mphb_children', FILTER_VALIDATE_INT, $filterOptions );

		if ( is_null( $children ) || false === $children ) {
			$this->errors[] = __( 'Children number is incorrect.', 'motopress-hotel-booking' );
			return false;
		}

		$this->children = $children;
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseCheckInDate(){
		$this->checkInDate	 = null;
		$date				 = filter_input( INPUT_POST, 'mphb_check_in_date' );
		$checkInDate		 = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );
		$todayDate			 = \DateTime::createFromFormat( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );

		if ( !$checkInDate ) {
			$this->errors[] = __( 'Check-in date is incorrect.', 'motopress-hotel-booking' );
			return false;
		} else if ( \MPHB\Utils\DateUtils::calcNights( $todayDate, $checkInDate ) < 0 ) {
			$this->errors[] = __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkInDate = $checkInDate;
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseCheckOutDate(){
		$this->checkOutDate	 = null;
		$date				 = filter_input( INPUT_POST, 'mphb_check_out_date' );
		$dateObj			 = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );

		if ( !$dateObj ) {
			$this->errors[] = __( 'Check-out date is incorrect.', 'motopress-hotel-booking' );
			return false;
		} else if ( isset( $this->checkInDate ) && !MPHB()->getRulesChecker()->verify( $this->checkInDate, $dateObj ) ) {
			$this->errors[] = __( 'Dates do not satisfy booking rules.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkOutDate = $dateObj;
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseRoomRate(){
		$this->roomRate = reset( $this->allowedRates );
		return true;
	}

	protected function setupAllowedRates(){

		$rateArgs = array(
			'check_in_date'	 => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
			'mphb_language'	 => 'original'
		);

		$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType( $this->roomType->getId(), $rateArgs );

		$this->allowedRates = $allowedRates;

		return count( $this->allowedRates ) > 0;
	}

	protected function showAlreadyBookedMessage(){
		$message = apply_filters( 'mphb_sc_checkout_already_booked_message', __( 'Accommodation is already booked.', 'motopress-hotel-booking' ) );
		echo $message;
	}

	protected function showSuccessMessage(){
		switch ( MPHB()->settings()->main()->getConfirmationMode() ) {
			case 'auto':
				ob_start();
				?>
				<h4><?php _e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php _e( 'Details of your reservation have just been sent to you in a confirmation email. Please check your inbox to complete booking.', 'motopress-hotel-booking' ); ?></p>
				<?php
				echo apply_filters( 'mphb_sc_checkout_auto_mode_success_message', ob_get_clean() );
				break;
			case 'manual':
				ob_start();
				?>
				<h4><?php _e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php _e( 'We received your booking request. Once it is confirmed we will notify you via email.', 'motopress-hotel-booking' ); ?></p>
				<?php
				echo apply_filters( 'mphb_sc_checkout_manual_mode_success_message', ob_get_clean() );
				break;
			case 'payment':
				ob_start();
				?>
				<h4><?php _e( 'Reservation submitted', 'motopress-hotel-booking' ); ?></h4>
				<p class="mphb_sc_checkout-success-reservation-message"><?php _e( 'We received your booking request. Once it is confirmed we will notify you via email.', 'motopress-hotel-booking' ); ?></p>
				<?php
				echo apply_filters( 'mphb_sc_checkout_payment_mode_success_message', ob_get_clean() );
				break;
		}
	}

	public function showErrorsMessage(){
		?>
		<p class="mphb-data-incorrect">
			<?php do_action( 'mphb_sc_checkout_errors_content', $this->errors ); ?>
		</p>
		<?php
	}

	protected function stepValid(){
		$this->isValidStep = true;
	}

}
