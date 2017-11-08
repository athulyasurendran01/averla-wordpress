<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 2Checkout
 */
class TwoCheckoutGateway extends Gateway {

	/**
	 *
	 * @var array
	 */
	private $supportedCurrencies = array();

	/**
	 *
	 * @var string
	 */
	private $accountNumber;

	/**
	 *
	 * @var string
	 */
	private $secretWord;

	/**
	 *
	 * @var TwoCheckout\InsListener
	 */
	private $insListener;

	protected function setupProperties(){
		parent::setupProperties();
		$this->accountNumber	 = trim( $this->getOption( 'account_number' ) );
		$this->secretWord		 = trim( $this->getOption( 'secret_word' ) );
		$this->setupInsListener();
		$this->adminTitle		 = __( '2Checkout', 'motopress-hotel-booking' );
		$this->adminDescription	 = $this->generateAdminDescription();
		$this->setupSuppportedCurrencies();
	}

	private function setupInsListener(){

		$insListenerArgs = array(
			'gatewayId'		 => $this->getId(),
			'sandbox'		 => $this->isSandbox,
			'accountNumber'	 => $this->accountNumber,
			'secretWord'	 => $this->secretWord
		);

		$this->insListener = new TwoCheckout\InsListener( $insListenerArgs );
	}

	private function generateAdminDescription(){
		$description = __( 'To setup the callback process for 2Checkout to automatically mark payments completed, you will need to', 'motopress-hotel-booking' );
		$description .= '<ol>';
		$description .= '<li>' . __( 'Login to your 2Checkout account and click the Notifications tab', 'motopress-hotel-booking' ) . '</li>';
		$description .= '<li>' . __( 'Click Enable All Notifications', 'motopress-hotel-booking' ) . '</li>';
		$description .= '<li>' . sprintf( __( 'In the Global URL field, enter the url %s' ), '<code>' . esc_url( $this->insListener->getNotifyUrl() ) . '</code>' ) . '</li>';
		$description .= '<li>' . __( 'Click Apply', 'motopress-hotel-booking' ) . '</li>';
		$description .= '</ol>';

		return $description;
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'			 => __( '2Checkout', 'motopress-hotel-booking' ),
			'description'	 => 'Pay via 2Checkout',
			'enabled'		 => false,
			'account_number' => '',
			'secret_word'	 => '',
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return '2checkout';
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){
		$url = $this->getPaymentUrl( $booking, $payment );

		// Redirect to 2checkout checkout
		wp_redirect( $url );
		exit;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	public function getPaymentUrl( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){

		$args = http_build_query( $this->getRequestArgs( $booking, $payment ), '', '&' );

		if ( $this->isSandbox ) {
			$url = 'https://sandbox.2checkout.com/checkout/purchase?' . $args;
		} else {
			$url = 'https://2checkout.com/checkout/purchase?' . $args;
		}

		return $url;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	private function getRequestArgs( $booking, $payment ){

		$returnUrlArgs = array(
			'payment-confirmation' => '2checkout',
		);

		$returnUrl = MPHB()->settings()->pages()->getPaymentSuccessPageUrl( $payment, $returnUrlArgs );

		$args = array(
			'sid'				 => $this->accountNumber,
			'mode'				 => '2CO', // Should always be passed as ‘2CO’. @see https://www.2checkout.com/documentation/checkout/parameters
			'x_receipt_link_url' => esc_url( $returnUrl ),
			'currency_code'		 => $payment->getCurrency(),
			'merchant_order_id'	 => $payment->getId(),
		);

		$args['li_0_type']		 = 'product';
		$args['li_0_name']		 = $this->generateItemName( $booking );
		$args['li_0_price']		 = $payment->getAmount();
		$args['li_0_product_id'] = $payment->getBookingId();
		$args['li_0_quantity']	 = 1;
		$args['li_0_tangible']	 = 'N';

		return $args;
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ){
		parent::registerOptionsFields( $subTab );
		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group2", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_account_number", array(
				'type'		 => 'text',
				'label'		 => sprintf( __( 'Account Number', 'motopress-hotel-booking' ), $this->title ),
				'default'	 => $this->getDefaultOption( 'account_number' )
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_secret_word", array(
				'type'		 => 'text',
				'label'		 => sprintf( __( 'Secret Word', 'motopress-hotel-booking' ), $this->title ),
				'default'	 => $this->getDefaultOption( 'secret_word' )
			) ),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive(){
		return parent::isActive() && $this->isSupportCurrency( MPHB()->settings()->currency()->getCurrencyCode() );
	}

	/**
	 *
	 * @param string $currency Currency code.
	 * @return bool
	 */
	public function isSupportCurrency( $currency ){
		return in_array( $currency, $this->supportedCurrencies );
	}

	public function setupSuppportedCurrencies(){
		$this->supportedCurrencies = include('two-checkout/supported-currencies.php');
	}

}
