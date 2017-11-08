<?php

namespace MPHB\Emails\Booking\Customer;

class ConfirmedEmail extends BaseEmail {

	public function getDefaultMessageHeaderText(){
		return __( 'Confirm your booking', 'motopress-hotel-booking' );
	}

	public function getDefaultSubject(){
		return __( '%site_title% - Confirm your booking #%booking_id%', 'motopress-hotel-booking' );
	}

	protected function initDescription(){
		$userConfirmationNote	 = '&nbsp<strong>' . __( 'This email is sent when "Booking Confirmation Mode" is set to Admin confirmation.', 'motopress-hotel-booking' ) . '</strong>';
		$this->description		 = __( 'Email that will be sent to customer after booking is place.', 'motopress-hotel-booking' ) . $userConfirmationNote;
	}

	protected function initLabel(){
		$this->label = __( 'New Booking Email (Confirmation by User)', 'motopress-hotel-booking' );
	}

}
