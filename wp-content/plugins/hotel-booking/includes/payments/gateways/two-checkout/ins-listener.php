<?php

namespace MPHB\Payments\Gateways\TwoCheckout;

use \MPHB\Payments\Gateways;

class InsListener extends Gateways\AbstractNotificationListener {

	/**
	 *
	 * @var string
	 */
	protected $accountNumber;

	/**
	 *
	 * @var string
	 */
	protected $secretWord;

	public function __construct( $args = array() ){
		parent::__construct( $args );
		$this->accountNumber = $args['accountNumber'];
		$this->secretWord	 = $args['secretWord'];
	}

	protected function initUrlIdentificationValue(){
		return '2checkout';
	}

	protected function validate( $input ){

		if ( !isset( $input['sale_id'], $input['invoice_id'], $input['md5_hash'] ) ) {
			return false;
		}

		if ( empty( $input['message_type'] ) ) {
			return false;
		}

		// Check is account number exists
		if ( empty( $input['vendor_id'] ) ) {
			return false;
		}

		// Check is payment id exists
		if ( empty( $input['vendor_order_id'] ) ) {
			return false;
		}

		$hash = strtoupper( md5( $input['sale_id'] . $this->accountNumber . $input['invoice_id'] . $this->secretWord ) );

		return mphb_hash_equals( $hash, $input['md5_hash'] );
	}

	protected function retrievePayment(){
		$paymentId = absint( $this->input['vendor_order_id'] );
		return MPHB()->getPaymentRepository()->findById( $paymentId );
	}

	protected function process(){

		$this->storePaymentMetaData();

		switch ( strtoupper( $this->input['message_type'] ) ) {

			case 'ORDER_CREATED' :

				$this->paymentCompleted( __( '2Checkout "Order Created" notification received.', 'motopress-hotel-booking' ) );

				break;

			case 'REFUND_ISSUED' :

				$this->paymentRefunded( __( 'Payment refunded in 2Checkout', 'motopress-hotel-booking' ) );

				break;

			case 'FRAUD_STATUS_CHANGED' :

				switch ( $this->input['fraud_status'] ) {
					case 'pass':
						$this->payment->addLog( __( '2Checkout fraud review passed', 'motopress-hotel-booking' ) );
						break;
					case 'fail':
						$this->paymentFailed( __( '2Checkout fraud review failed', 'motopress-hotel-booking' ) );
						break;
					case 'wait':
						$this->paymentOnHold( __( '2Checkout fraud review in progress', 'motopress-hotel-booking' ) );
						break;
				}

				break;
		}
	}

	/**
	 *
	 * @param string $log Optional.
	 * @return
	 */
	protected function paymentCompleted( $log = '' ){

		if ( !MPHB()->postTypes()->payment()->statuses()->canStatusCompleted( $this->payment->getStatus() ) ) {
			return false;
		}

		if ( !empty( $log ) ) {
			$this->payment->addLog( $log );
		}

		$this->storePaymentMetaData();

		$this->payment->setTransactionId( mphb_clean( $this->input['sale_id'] ) );

		$this->payment->setStatus( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED );

		MPHB()->getPaymentRepository()->save( $this->payment );

		return true;
	}

	/**
	 *
	 * @param string $log Optional.
	 * @return boolean
	 */
	protected function paymentRefunded( $log = '' ){

		if ( !MPHB()->postTypes()->payment()->statuses()->canStatusRefunded( $this->payment->getStatus() ) ) {
			return false;
		}

		if ( !empty( $log ) ) {
			$this->payment->addLog( $log );
		}

		$this->payment->setStatus( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_REFUNDED );
		MPHB()->getPaymentRepository()->save( $this->payment );

		return true;
	}

	/**
	 *
	 * @param string $log Optional.
	 * @return boolean
	 */
	protected function paymentOnHold( $log = '' ){

		if ( !MPHB()->postTypes()->payment()->statuses()->canStatusOnHolded( $this->payment->getStatus() ) ) {
			return false;
		}

		if ( !empty( $log ) ) {
			$this->payment->addLog( $log );
		}

		$this->payment->setStatus( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_ON_HOLD );
		MPHB()->getPaymentRepository()->save( $this->payment );

		return true;
	}

	/**
	 *
	 * @param string $log Optional.
	 * @return boolean
	 */
	protected function paymentFailed( $log = '' ){

		if ( !MPHB()->postTypes()->payment()->statuses()->canStatusFailed( $this->payment->getStatus() ) ) {
			return false;
		}

		if ( !empty( $log ) ) {
			$this->payment->addLog( $log );
		}

		$this->payment->setStatus( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_FAILED );
		MPHB()->getPaymentRepository()->save( $this->payment );

		return true;
	}

	private function storePaymentMetaData(){

		$isStored = get_post_meta( $this->payment->getId(), '_mphb_2checkout_meta_stored', true );

		if ( $isStored ) {
			return;
		}

		$metas = array(
			'customer_email'		 => '_mphb_email',
			'customer_first_name'	 => '_mphb_first_name',
			'customer_last_name'	 => '_mphb_last_name',
			'bill_street_address'	 => '_mphb_address1',
			'bill_street_address2'	 => '_mphb_address2',
			'bill_city'				 => '_mphb_city',
			'bill_state'			 => '_mphb_state',
			'bill_country'			 => '_mphb_country',
			'bill_postal_code'		 => '_mphb_zip',
		);

		foreach ( $metas as $inputName => $metaName ) {
			$metaValue = mphb_clean( isset( $this->input[$inputName] ) ? $this->input[$inputName] : ''  );
			if ( !empty( $metaValue ) ) {
				update_post_meta( $this->payment->getId(), $metaName, $metaValue );
			}
		}

		update_post_meta( $this->payment->getId(), '_mphb_2checkout_meta_stored', true );

		// Re-get payment.
		$this->payment = MPHB()->getPaymentRepository()->findById( $this->payment->getId(), true );
	}

}
