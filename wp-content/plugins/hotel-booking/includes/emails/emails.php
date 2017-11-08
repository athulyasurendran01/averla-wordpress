<?php

namespace MPHB\Emails;

use \MPHB\PostTypes\BookingCPT;

class Emails {

	/**
	 *
	 * @var Mailer
	 */
	private $mailer;

	/**
	 *
	 * @var Booking\Customer\BaseEmail
	 */
	private $customerPendingBooking;

	/**
	 *
	 * @var Booking\Admin\BaseEmail
	 */
	private $adminPendingBooking;

	/**
	 *
	 * @var Booking\Customer\BaseEmail
	 */
	private $customerApprovedBooking;

	/**
	 *
	 * @var Booking\Admin\BaseEmail
	 */
	private $adminCustomerConfirmedBooking;

	/**
	 *
	 * @var Booking\Customer\BaseEmail
	 */
	private $customerCancelledBooking;

	/**
	 *
	 * @var Booking\Admin\BaseEmail
	 */
	private $adminCustomerCancelledBooking;

	/**
	 *
	 * @var Booking\Admin\ConfirmedByPaymentEmail
	 */
	private $adminConfirmedByPaymentEmail;

	/**
	 *
	 * @var Booking\Customer\BaseEmail
	 */
	private $customerConfirmationBooking;

	public function __construct(){

		$this->mailer = new Mailer();

		$this->initEmails();

		$this->addActions();
	}

	private function initEmails(){


		$this->adminPendingBooking = new Booking\Admin\PendingEmail( array(
			'id' => 'admin_pending_booking'
			), new EmailTemplater( array(
			'booking' => true
			) )
		);


		$this->customerPendingBooking = new Booking\Customer\PendingEmail( array(
			'id' => 'customer_pending_booking'
			), new EmailTemplater( array(
			'booking'			 => true,
			'user_cancellation'	 => true
			) )
		);

		$this->customerConfirmationBooking = new Booking\Customer\ConfirmedEmail( array(
			'id' => 'customer_confirmation_booking',
			), new EmailTemplater( array(
			'booking'			 => true,
			'user_confirmation'	 => true,
			'user_cancellation'	 => true
			) )
		);

		$this->customerApprovedBooking = new Booking\Customer\ApprovedEmail( array(
			'id' => 'customer_approved_booking',
			), new EmailTemplater( array(
			'booking'			 => true,
			'user_cancellation'	 => true
			) )
		);

		$this->adminCustomerConfirmedBooking = new Booking\Admin\ConfirmedEmail( array(
			'id' => 'admin_customer_confirmed_booking'
			), new EmailTemplater( array(
			'booking'			 => true,
			'user_cancellation'	 => true
			) )
		);

		$this->customerCancelledBooking = new Booking\Customer\CancelledEmail( array(
			'id' => 'customer_cancelled_booking'
			), new EmailTemplater( array(
			'booking' => true
			) )
		);

		$this->adminCustomerCancelledBooking = new Booking\Admin\CancelledEmail( array(
			'id' => 'admin_customer_cancelled_booking'
			), new EmailTemplater( array(
			'booking' => true
			) )
		);

		$this->adminConfirmedByPaymentEmail = new Booking\Admin\ConfirmedByPaymentEmail( array(
			'id' => 'admin_payment_confirmed_booking'
			), new EmailTemplater( array(
			'booking'	 => true,
			'payment'	 => true
			) )
		);
	}

	private function addActions(){
		add_action( 'mphb_booking_status_changed', array( $this, 'sendBookingMails' ), 10, 2 );

		add_action( 'mphb_booking_confirmed_with_payment', array( $this, 'sendBookingConfirmedWithPaymentEmail' ), 10, 2 );
		add_action( 'mphb_customer_confirmed_booking', array( $this->adminCustomerConfirmedBooking, 'trigger' ) );
		add_action( 'mphb_customer_cancelled_booking', array( $this->adminCustomerCancelledBooking, 'trigger' ) );
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function sendBookingConfirmedWithPaymentEmail( $booking, $payment ){
		$this->adminConfirmedByPaymentEmail->trigger( $booking, array(
			'payment' => $payment
		) );
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function sendBookingMails( $booking, $oldStatus ){
		switch ( $booking->getStatus() ) {
			case BookingCPT\Statuses::STATUS_PENDING:
				$this->adminPendingBooking->trigger( $booking );
				$this->customerPendingBooking->trigger( $booking );
				break;
			case BookingCPT\Statuses::STATUS_PENDING_USER:
				$this->customerConfirmationBooking->trigger( $booking );
				break;
			case BookingCPT\Statuses::STATUS_CONFIRMED:
				$this->customerApprovedBooking->trigger( $booking );
				break;
			case BookingCPT\Statuses::STATUS_CANCELLED:
				$this->customerCancelledBooking->trigger( $booking );
				break;
		}
	}

	public function getMailer(){
		return $this->mailer;
	}

}
