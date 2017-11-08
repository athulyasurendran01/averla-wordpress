<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

class PaypalGateway extends Gateway {

	/**
	 *
	 * @var Paypal\IpnListener
	 */
	protected $ipnListener;

	/**
	 *
	 * @var string
	 */
	protected $businessEmail;

	/**
	 *
	 * @var array
	 */
	private $countriesList;
	// Billing Fields
	protected $billingFirstName;
	protected $billingLastName;
	protected $billingEmail;
	protected $billingPhone;
	protected $fieldsErrors = array();

	public function __construct(){
		$this->setupCountries();
		parent::__construct();
		$this->setupNotificationListener();
		$this->setupSupportedCurrencies();
	}

	protected function setupNotificationListener(){
		$ipnListnerArgs		 = array(
			'gatewayId'				 => $this->getId(),
			'sandbox'				 => $this->isSandbox,
			'verificationDisabled'	 => (bool) $this->getOption( 'disable_ipn_verification' ),
			'businessEmail'			 => $this->businessEmail
		);
		$this->ipnListener	 = new Paypal\IpnListener( $ipnListnerArgs );
	}

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle	 = __( 'PayPal', 'motopress-hotel-booking' );
		$this->businessEmail = sanitize_email( $this->getOption( 'business_email' ) );
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'						 => __( 'PayPal', 'motopress-hotel-booking' ),
			'description'				 => __( 'Pay via PayPal', 'motopress-hotel-booking' ),
			'enabled'					 => false,
			'is_sandbox'				 => false,
			'business_email'			 => '',
			'disable_ipn_verification'	 => false
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return 'paypal';
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive(){
		return parent::isActive() && $this->isSupportCurrency();
	}

	public function initPaymentFields(){
		return array(
			'mphb_billing_country'		 => array(
				'type'		 => 'select',
				'label'		 => __( 'Country', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_country',
				'list'		 => array( '' => __( 'Select a country', 'motopress-hotel-booking' ) ) + $this->countriesList,
			),
			'mphb_billing_state'		 => array(
				'type'		 => 'text',
				'label'		 => __( 'State', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_state',
			),
			'mphb_billing_address1'		 => array(
				'type'		 => 'text',
				'label'		 => __( 'Address line 1', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_address1',
			),
			'mphb_billing_address2'		 => array(
				'type'		 => 'text',
				'label'		 => __( 'Address line 2 (optional)', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_address2',
			),
			'mphb_billing_city'			 => array(
				'type'		 => 'text',
				'label'		 => __( 'City', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_city',
			),
			'mphb_billing_first_name'	 => array(
				'type'		 => 'text',
				'label'		 => __( 'First Name', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_first_name',
			),
			'mphb_billing_last_name'	 => array(
				'type'		 => 'text',
				'label'		 => __( 'Last Name', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_last_name',
			),
			'mphb_billing_email'		 => array(
				'type'		 => 'text',
				'label'		 => __( 'Email', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_email',
				'validate'	 => 'email'
			),
			'mphb_billing_phone'		 => array(
				'type'		 => 'text',
				'label'		 => __( 'Phone', 'motopress-hotel-booking' ),
				'meta_id'	 => '_mphb_phone',
			),
		);
	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){

		$url = $this->getPaymentUrl( $booking, $payment );

		// Redirect to paypal checkout
		wp_redirect( $url );
		exit;
	}

	/**
	 * Get the PayPal request URL for an booking.
	 *
	 */
	public function getPaymentUrl( $booking, $payment ){
		$paypalArgs = http_build_query( $this->getRequestArgs( $booking, $payment ), '', '&' );

		if ( $this->isSandbox ) {
			$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?test_ipn=1&' . $paypalArgs;
		} else {
			$url = 'https://www.paypal.com/cgi-bin/webscr?' . $paypalArgs;
		}

		return $url;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	public function getRequestArgs( $booking, $payment ){

		$args = array(
			'cmd'			 => '_xclick',
			'business'		 => $this->businessEmail,
			'currency_code'	 => $payment->getCurrency(),
			'charset'		 => 'utf-8',
			'rm'			 => 2, // Return method 1 - GET, 2 - POST
			'notify_url'	 => $this->ipnListener->getNotifyUrl(),
			'return'		 => esc_url_raw( MPHB()->settings()->pages()->getPaymentSuccessPageUrl( $booking ) ),
			'cancel_return'	 => esc_url_raw( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $booking ) ),
			'bn'			 => 'MPHB_BuyNow', //  build notation
			'invoice'		 => $payment->getKey(),
			'custom'		 => $payment->getId(),
			'cbt'			 => get_bloginfo( 'name' ), // Return to Merchant button text
			'no_shipping'	 => '1', // Do not prompt buyers for a shipping address.
			'no_note'		 => '1', // Do not prompt buyers to include a note // Deprecated
		);

		$args = array_merge( $args, $this->getBillingInfoArgs(), $this->getItemArgs( $booking ) );

		return $args;
	}

	private function getBillingInfoArgs(){
		$args = array();

		$fields = array(
			'address1',
			'address2',
			'city',
			'country',
			'email',
			'first_name',
			'last_name',
			'state',
//			'company',
//			'zip',
		);

		foreach ( $fields as $field ) {
			if ( !empty( $this->postedPaymentFields['mphb_billing_' . $field] ) ) {
				$args[$field] = $this->postedPaymentFields['mphb_billing_' . $field];
			}
		}

		return $args;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @return array
	 */
	public function getItemArgs( $booking ){
		$itemName = $this->generateItemName( $booking );

		return array(
			'item_name'	 => $itemName,
			'amount'	 => $booking->calcDepositAmount()
		);
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ){
		parent::registerOptionsFields( $subTab );
		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group2", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_business_email", array(
				'type'		 => 'email',
				'label'		 => __( 'Paypal Business Email', 'motopress-hotel-booking' ),
				'default'	 => $this->getDefaultOption( 'business_email' )
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_disable_ipn_verification", array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Disable IPN Verification', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'disable_ipn_verification' ),
				'description'	 => __( 'Specify an IPN listener for a specific payment instead of the listeners specified in your PayPal Profile.', 'motopress-hotel-booking' ),
			) ),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 *
	 * @return string
	 */
	public function getBusinessEmail(){
		return $this->businessEmail;
	}

	private function setupSupportedCurrencies(){
		$supportedCurrencies		 = include('paypal/supported-currencies.php');
		$supportedCurrencies		 = apply_filters( 'mphb_paypal_supported_currencies', $supportedCurrencies );
		$this->supportedCurrencies	 = $supportedCurrencies;
	}

	private function setupCountries(){
		$countriesList		 = include('paypal/countries.php');
		$countriesList		 = apply_filters( 'mphb_paypal_countries_list', $countriesList );
		$this->countriesList = $countriesList;
	}

	public function isSupportCurrency(){
		return in_array( MPHB()->settings()->currency()->getCurrencyCode(), $this->supportedCurrencies );
	}

}
