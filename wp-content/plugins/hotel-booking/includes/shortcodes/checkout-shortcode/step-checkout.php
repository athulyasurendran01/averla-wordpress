<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;

class StepCheckout extends Step {

	protected $booking;

	/**
	 *
	 * @var boolean
	 */
	protected $alreadyBooked = false;

	public function __construct(){
		// templates hooks
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderBookingDetails' ), 10 );
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderServices' ), 20 );
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderPriceBreakdown' ), 30 );
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderCheckoutText' ), 35 );
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderCustomerDetails' ), 40 );
		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			add_action( 'mphb_sc_checkout_form', array( $this, 'renderBillingDetails' ), 40 );
		}
		add_action( 'mphb_sc_checkout_form', array( $this, 'renderTotalPrice' ), 50 );

		// Booking Details
		add_action( 'mphb_sc_checkout_form_booking_details', array( $this, 'renderRoomTypeTitle' ), 10 );
		add_action( 'mphb_sc_checkout_form_booking_details', array( $this, 'renderRoomTypeGuests' ), 30 );
		add_action( 'mphb_sc_checkout_form_booking_details', array( $this, 'renderCheckInDate' ), 40 );
		add_action( 'mphb_sc_checkout_form_booking_details', array( $this, 'renderCheckOutDate' ), 50 );
		add_action( 'mphb_sc_checkout_form_booking_details', array( $this, 'renderRoomRates' ), 60 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function setup(){
		$this->parseBookingData();
		if ( $this->isCorrectBookingData ) {
			$bookingAtts = array(
				'room_rate'		 => $this->roomRate,
				'adults'		 => $this->adults,
				'children'		 => $this->children,
				'check_in_date'	 => $this->checkInDate,
				'check_out_date' => $this->checkOutDate
			);

			$this->booking = Entities\Booking::create( $bookingAtts );

			MPHB()->searchParametersStorage()->save(
				array(
					'mphb_check_in_date'	 => $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
					'mphb_check_out_date'	 => $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
					'mphb_adults'			 => $this->adults,
					'mphb_children'			 => $this->children
				)
			);

			if ( !$this->roomType->hasAvailableRoom( $this->checkInDate->format( 'Y-m-d' ), $this->checkOutDate->format( 'Y-m-d' ) ) ) {
				$this->alreadyBooked = true;
			} else {
				$this->stepValid();
			}
		}
	}

	public function render(){
		if ( !$this->isCorrectBookingData ) {
			$this->showErrorsMessage();
			return;
		}

		if ( $this->alreadyBooked ) {
			$this->showAlreadyBookedMessage();
			return;
		}

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_CHECKOUT );

		do_action( 'mphb_sc_checkout_before_form' );

		echo $this->renderCheckoutForm();

		do_action( 'mphb_sc_checkout_after_form' );
	}

	public function renderCheckoutForm(){
		$actionUrl = add_query_arg( 'step', \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING, MPHB()->settings()->pages()->getCheckoutPageUrl() );
		?>
		<form class="mphb_sc_checkout-form" method="POST" action="<?php echo esc_url( $actionUrl ); ?>">

			<?php wp_nonce_field( \MPHB\Shortcodes\CheckoutShortcode::NONCE_ACTION_BOOKING, \MPHB\Shortcodes\CheckoutShortcode::NONCE_NAME ); ?>

			<input type="hidden" name="mphb_room_type_id" value="<?php echo $this->roomType->getId(); ?>" />
			<input type="hidden" name="mphb_check_in_date" value="<?php echo $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ); ?>" />
			<input type="hidden" name="mphb_check_out_date" value="<?php echo $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ); ?>" />
			<input type="hidden" name="mphb_adults" value="<?php echo $this->adults; ?>" />
			<input type="hidden" name="mphb_children" value="<?php echo $this->children; ?>" />
			<input type="hidden" name="mphb_checkout_step" value="<?php echo \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING; ?>" />

			<?php do_action( 'mphb_sc_checkout_form' ); ?>

			<p class="mphb_sc_checkout-submit-wrapper">
				<input type="submit" class="button" value="<?php _e( 'Book Now', 'motopress-hotel-booking' ); ?>"/>
			</p>

		</form>
		<?php
	}

	public function renderCheckoutText(){
		?>
		<section class="mphb-checkout-text-wrapper">
			<?php echo MPHB()->settings()->main()->getCheckoutText(); ?>
		</section>
		<?php
	}

	public function renderCustomerDetails(){
		?>
		<section id="mphb-customer-details">
			<h3><?php _e( 'Customer Details', 'motopress-hotel-booking' ); ?></h3>
			<p class="mphb-required-fields-tip"><small><?php printf( __( 'Required fields are followed by %s', 'motopress-hotel-booking' ), '<abbr title="required">*</abbr>' ); ?></small></p>
			<?php do_action( 'mphb_sc_checkout_form_customer_details' ); ?>
			<p class="mphb-customer-name">
				<label for="mphb_first_name">
					<?php _e( 'First Name', 'motopress-hotel-booking' ); ?>
					<abbr title="<?php _e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				</label>
				<br />
				<input type="text" id="mphb_first_name" name="mphb_first_name" required="required" />
			</p>
			<p class="mphb-customer-last-name">
				<label for="mphb_last_name">
					<?php _e( 'Last Name', 'motopress-hotel-booking' ); ?>
				</label>
				<br />
				<input type="text" name="mphb_last_name" id="mphb_last_name" />
			</p>
			<p class="mphb-customer-email">
				<label for="mphb_email">
					<?php _e( 'Email', 'motopress-hotel-booking' ); ?>
					<abbr title="<?php _e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				</label>
				<br />
				<input type="email" name="mphb_email" required="required" id="mphb_email" />
			</p>
			<p class="mphb-customer-phone">
				<label for="mphb_phone">
					<?php _e( 'Phone', 'motopress-hotel-booking' ); ?>
					<abbr title="<?php _e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				</label>
				<br />
				<input type="text" name="mphb_phone" required="required" id="mphb_phone" />
			</p>
			<p class="mphb-customer-note">
				<label for="mphb_note"><?php _e( 'Notes', 'motopress-hotel-booking' ); ?></label><br />
				<textarea name="mphb_note" id="mphb_note" rows="4"></textarea>
			</p>
		</section>
		<?php
	}

	public function renderBookingDetails(){
		?>
		<section class="mphb-booking-details">
			<h3><?php _e( 'Booking Details', 'motopress-hotel-booking' ); ?></h3>
			<?php do_action( 'mphb_sc_checkout_form_booking_details' ); ?>
		</section>
		<?php
	}

	public function renderRoomTypeTitle(){
		?>
		<p class="mphb-room-type-title">
			<span><?php _e( 'Accommodation Type:', 'motopress-hotel-booking' ); ?></span>
			<strong><?php echo $this->roomType->getTitle(); ?></strong>
		</p>
		<?php
	}

	public function renderRoomTypeCategories(){
		if ( $this->roomType->getCategories() ) {
			?>
			<p class="mphb-room-type-categories">
				<span><?php _e( 'Categories:', 'motopress-hotel-booking' ); ?></span>
				<strong><?php echo $this->roomType->getCategories(); ?></strong>
			</p>
			<?php
		}
	}

	public function renderRoomTypeGuests(){
		?>
		<p class="mphb-guests-number">
			<span><?php _e( 'Guests:', 'motopress-hotel-booking' ); ?></span>
			<strong>
				<?php
				printf( _n( '%d Adult', '%d Adults', $this->adults, 'motopress-hotel-booking' ), $this->adults );
				if ( $this->children > 0 ) {
					printf( _n( ', %d Child', ', %d Children', $this->children, 'motopress-hotel-booking' ), $this->children );
				}
				?>
			</strong>
		</p>
		<?php
	}

	public function renderCheckInDate(){
		?>
		<p class="mphb-check-in-date">
			<span><?php _e( 'Check-in Date:', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo $this->checkInDate->format( 'Y-m-d' ); ?>"><strong><?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkInDate ); ?></strong></time>,
			<span><?php _ex( 'from', 'from 10:00 am', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo get_option( 'time_format' ); ?>"><?php echo MPHB()->settings()->dateTime()->getCheckInTimeWPFormatted() ?></time>
		</p>
		<?php
	}

	public function renderCheckOutDate(){
		?>
		<p class="mphb-check-out-date">
			<span><?php _e( 'Check-out Date:', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo $this->checkOutDate->format( 'Y-m-d' ); ?>"><strong><?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkOutDate ); ?></strong></time>,
			<span><?php _ex( 'until', 'until 10:00 am', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo get_option( 'time_format' ); ?>"><?php echo MPHB()->settings()->dateTime()->getCheckOutTimeWPFormatted() ?></time>
		</p>
		<?php
	}

	public function renderRoomRates(){
		ob_start();

		if ( count( $this->allowedRates ) > 1 ) {
			echo '<h3>' . __( 'Choose Rate', 'motopress-hotel-booking' ) . '</h3>';
			$isFirst = true;
			foreach ( $this->allowedRates as $rate ) {
				$rate	 = apply_filters( '_mphb_translate_rate', $rate );
				?>
				<p class="mphb-room-rate-variant">
					<label>
						<input type="radio" name="mphb_room_rate_id" value="<?php echo $rate->getOriginalId(); ?>" <?php checked( $isFirst ) ?>/>
						<strong>
							<?php
							echo esc_html( $rate->getTitle() ) . ', ' . mphb_format_price( $rate->calcPrice( $this->checkInDate, $this->checkOutDate ) );
							?>
						</strong>
					</label>
					<br />
					<?php echo esc_html( $rate->getDescription() ); ?>
				</p>
				<?php
				$isFirst = false;
			}
		} else {
			$defaultRate = reset( $this->allowedRates );
			echo '<input type="hidden" name="mphb_room_rate_id" value="' . esc_attr( $defaultRate->getOriginalId() ) . '">';
		}

		echo ob_get_clean();
	}

	public function renderPriceBreakdown(){
		?>
		<section class="mphb-room-price-breakdown-wrapper">
			<h3>
				<?php _e( 'Price Breakdown', 'motopress-hotel-booking' ); ?>
			</h3>
			<?php \MPHB\Views\BookingView::renderPriceBreakdown( $this->booking ); ?>
		</section>
		<?php
	}

	public function renderServices(){

		if ( !$this->roomType->hasServices() ) {
			return;
		}

		$servicesAtts = array(
			'post__in' => $this->roomType->getServices()
		);

		$services = MPHB()->getServiceRepository()->findAll( $servicesAtts );
		?>
		<section id="mphb-services-details">
			<h3><?php _e( 'Choose Additional Services', 'motopress-hotel-booking' ); ?></h3>
			<ul class="mphb_sc_checkout-services-list">

				<?php foreach ( $services as $key => $service ) { ?>
					<?php apply_filters( '_mphb_translate_service', $service ); ?>
					<li>
						<label for="mphb_service-id-<?php echo $service->getId(); ?>">
							<input type="checkbox"
								   id="mphb_service-id-<?php echo $service->getId(); ?>"
								   name="mphb_services[<?php echo $key; ?>][id]"
								   class="mphb_sc_checkout-service"
								   value="<?php echo $service->getOriginalId(); ?>"
								   />
								   <?php echo $service->getTitle(); ?>
							<em>(<?php echo $service->getPriceWithConditions( false ); ?>)</em>
						</label>
						<?php if ( $service->isPayPerAdult() && $this->adults > 1 ) { ?>
							<label>
								<?php _e( 'for ', 'motopress-hotel-booking' ); ?>
								<select name="mphb_services[<?php echo $key; ?>][count]">
									<?php for ( $i = 1; $i <= $this->adults; $i++ ) { ?>
										<option value="<?php echo $i; ?>" <?php selected( $this->adults, $i ); ?> ><?php echo $i; ?></option>
									<?php } ?>
								</select>
								<?php _e( ' adult(s)', 'motopress-hotel-booking' ); ?>
							</label>
						<?php } else { ?>
							<input type="hidden" name="mphb_services[<?php echo $key; ?>][count]" value="1" />
						<?php } ?>
					</li>
				<?php } ?>
			</ul>
		</section>
		<?php
	}

	public function renderTotalPrice(){
		?>
		<?php if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' && MPHB()->settings()->payment()->getAmountType() === 'deposit' ) { ?>
			<p class="mphb-deposit-amount">
				<output>
					<?php _e( 'Deposit:', 'motopress-hotel-booking' ); ?>
					<strong class="mphb-deposit-amount-field">
						<?php echo mphb_format_price( $this->booking->calcDepositAmount() ); ?>
					</strong>
				</output>
			</p>
		<?php } ?>
		<p class="mphb-total-price">
			<output>
				<?php _e( 'Total Price:', 'motopress-hotel-booking' ); ?>
				<strong class="mphb-total-price-field">
					<?php echo mphb_format_price( $this->booking->getTotalPrice() ); ?>
				</strong>
				<span class="mphb-preloader mphb-hide"></span>
			</output>
		</p>
		<p class="mphb-errors-wrapper mphb-hide"></p>
		<?php
	}

	public function renderBillingDetails(){
		$gateways = MPHB()->gatewayManager()->getListActive();
		?>
		<section id="mphb-billing-details">
			<h3><?php _e( 'Billing Details', 'motopress-hotel-booking' ); ?></h3>
			<?php if ( empty( $gateways ) ) : ?>
				<p>
					<?php _e( 'Sorry, it seems that there are no available payment methods.', 'motopress-hotel-booking' ); ?>
				</p>
			<?php else: ?>
				<?php
				$defaultGatewayId = MPHB()->settings()->payment()->getDefaultGateway();
				if ( !empty( $gateways ) && !array_key_exists( $defaultGatewayId, $gateways ) ) {
					$defaultGatewayId = current( array_keys( $gateways ) );
				}
				if ( count( $gateways ) > 1 ) {
					?>
					<label>
						<?php _e( 'Select Payment Method:', 'motopress-hotel-booking' ); ?>
					</label>
					<ul class="mphb-gateways-list">
						<?php foreach ( $gateways as $gateway ) { ?>
							<li>
								<input id="mphb_gateway_<?php echo $gateway->getId(); ?>" type="radio" name="mphb_gateway_id" value="<?php echo esc_attr( $gateway->getId() ); ?>" <?php checked( $defaultGatewayId, $gateway->getId() ); ?> />

								<label for="mphb_gateway_<?php echo $gateway->getId(); ?>">
									<?php echo $gateway->getTitle(); ?>
								</label>

								<?php
								$gatewayDescription = $gateway->getDescription();
								if ( !empty( $gatewayDescription ) ) {
									echo '<br/>';
									echo $gatewayDescription;
								}
								?>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<?php $gateway = reset( $gateways ); ?>
					<input id="mphb_gateway_<?php echo $gateway->getId(); ?>" type="hidden" name="mphb_gateway_id" value="<?php echo esc_attr( $gateway->getId() ); ?>" />
				<?php } ?>
				<?php $billingFieldsWrapperClass = $gateways[$defaultGatewayId]->hasVisiblePaymentFields() ? '' : 'mphb-billing-fields-hidden'; ?>
				<fieldset class="mphb-billing-fields <?php echo $billingFieldsWrapperClass; ?>">
					<?php $gateways[$defaultGatewayId]->renderPaymentFields( $this->booking ); ?>
				</fieldset>
			<?php endif; // empty( $gateways )     ?>
		</section>
		<?php
	}

	public function enqueueScripts(){

		if ( !$this->isValidStep ) {
			return;
		}

		$checkoutData = array();

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$checkoutData['deposit_amount'] = $this->booking->calcDepositAmount();
		}

		$checkoutData['total'] = $this->booking->calcPrice();

		MPHB()->getPublicScriptManager()->addCheckoutData( $checkoutData );

		foreach ( MPHB()->gatewayManager()->getListActive() as $gateway ) {
			MPHB()->getPublicScriptManager()->addGatewayData( $gateway->getId(), $gateway->getCheckoutData( $this->booking ) );
		}

		wp_enqueue_script( 'mphb-jquery-serialize-json' );
		MPHB()->getPublicScriptManager()->enqueue();
	}

}
