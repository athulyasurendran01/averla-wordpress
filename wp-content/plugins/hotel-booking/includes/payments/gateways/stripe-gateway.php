<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

class StripeGateway extends Gateway {

	/**
	 *
	 * @var string
	 */
	private $secretKey;

	/**
	 *
	 * @var string
	 */
	private $publicKey;

	/**
	 *
	 * @var string
	 */
	private $checkoutImageUrl;

	/**
	 *
	 * @var bool
	 */
	private $allowRememberMe;

	/**
	 *
	 * @var bool
	 */
	private $needBillingAddress;

	/**
	 *
	 * @var bool
	 */
	private $useBitcoin;

	/**
	 *
	 * @var string
	 */
	private $locale;

	/**
	 *
	 * @var string
	 */
	private $token;

	public function __construct(){
		parent::__construct();
		if ( $this->isActive() ) {
			$this->setupAPI();

			add_action( 'admin_notices', array( $this, 'checkSSLNotice' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		}
	}

	private function setupAPI(){
		Stripe\StripeAPI::setSecretKey( $this->secretKey );
	}

	protected function initId(){
		return 'stripe';
	}

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle			 = __( 'Stripe', 'motopress-hotel-booking' );
		$this->secretKey			 = $this->getOption( 'secret_key' );
		$this->publicKey			 = $this->getOption( 'public_key' );
		$this->checkoutImageUrl		 = esc_url( $this->getOption( 'checkout_image_url' ) );
		$this->allowRememberMe		 = (bool) $this->getOption( 'allow_remember_me' );
		$this->needBillingAddress	 = (bool) $this->getOption( 'need_billing_address' );
		$this->useBitcoin			 = (bool) $this->getOption( 'use_bitcoin' );
		$this->locale				 = $this->getOption( 'locale' );

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date or check the documentation "<a href="%s">Testing Stripe</a>" for more card numbers.', 'motopress-hotel-booking' ), 'https://stripe.com/docs/testing' );
			$this->description = trim( $this->description );
		}
	}

	public function enqueueScripts(){
		if ( mphb_is_checkout_page() ) {
			wp_enqueue_script( 'mphb-vendor-stripe-checkout' );
		}
	}

	/**
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return int
	 */
	public function retrieveStripeAmount( $amount, $currency = null ){
		if ( is_null( $currency ) ) {
			$currency = MPHB()->settings()->currency()->getCurrencyCode();
		}
		/**
		 * @see https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
		 */
		switch ( strtoupper( $currency ) ) {
			// Zero decimal currencies
			case 'BIF' :
			case 'CLP' :
			case 'DJF' :
			case 'GNF' :
			case 'JPY' :
			case 'KMF' :
			case 'KRW' :
			case 'MGA' :
			case 'PYG' :
			case 'RWF' :
			case 'VND' :
			case 'VUV' :
			case 'XAF' :
			case 'XOF' :
			case 'XPF' :
				$amount	 = absint( $amount );
				break;
			default :
				$amount	 = round( $amount, 2 ) * 100; // In cents
				break;
		}
		return (int) $amount;
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'					 => __( 'Pay by Card (Stripe)', 'motopress-hotel-booking' ),
			'description'			 => __( 'Pay with your credit card via Stripe.', 'motopress-hotel-booking' ),
			'enabled'				 => false,
			'is_sandbox'			 => false,
			'secret_key'			 => '',
			'public_key'			 => '',
			'checkout_image_url'	 => 'https://stripe.com/img/documentation/checkout/marketplace.png',
			'allow_remember_me'		 => false,
			'need_billing_address'	 => false,
			'use_bitcoin'			 => false,
			'locale'				 => 'auto'
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	public function registerOptionsFields( &$subTab ){
		parent::registerOptionsFields( $subTab );

		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group1", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_secret_key", array(
				'type'		 => 'text',
				'label'		 => __( 'Secret Key', 'motopress-hotel-booking' ),
				'default'	 => $this->getDefaultOption( 'secret_key' )
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_public_key", array(
				'type'		 => 'text',
				'label'		 => __( 'Public Key', 'motopress-hotel-booking' ),
				'default'	 => $this->getDefaultOption( 'public_key' )
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_checkout_image_url", array(
				'type'			 => 'text',
				'label'			 => __( 'Image of Your Brand', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'checkout_image_url' ),
				'description'	 => __( 'A relative or absolute URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.', 'motopress-hotel-booking' ),
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_allow_remember_me", array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Enable "Remember Me" Option', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'allow_remember_me' ),
				'description'	 => __( 'Specify whether to include the option to "Remember Me" for future purchases.', 'motopress-hotel-booking' ),
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_need_billing_address", array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Collect Billing Address', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'need_billing_address' ),
				'description'	 => __( 'Specify whether Checkout should collect the user\'s billing address.', 'motopress-hotel-booking' ),
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_use_bitcoin", array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Accept Bitcoin', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'need_billing_address' ),
				'description'	 => __( 'Specify whether to accept Bitcoin.', 'motopress-hotel-booking' ),
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_locale", array(
				'type'			 => 'select',
				'label'			 => __( 'Checkout Locale', 'motopress-hotel-booking' ),
				'list'			 => $this->getAvailableLocales(),
				'default'		 => $this->getDefaultOption( 'locale' ),
				'description'	 => __( 'Display Checkout in the user\'s preferred language, if available.', 'motopress-hotel-booking' ),
			) ),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 * Generate the request for the payment.
	 *
	 * @param string $token
	 * @param \MPHB\Entities\Payment $payment
	 * @return array()
	 */
	protected function generatePaymentRequest( $token, $payment ){
		$post_data					 = array();
		$post_data['currency']		 = strtolower( $payment->getCurrency() );
		$post_data['amount']		 = $this->retrieveStripeAmount( $payment->getAmount(), $payment->getCurrency() );
		$post_data['description']	 = sprintf( __( '%s - Payment %s', 'motopress-hotel-booking' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $payment->getId() );
		$post_data['capture']		 = 'true';

		if ( !empty( $this->postedPaymentFields['mphb_stripe_email'] ) ) {
			$post_data['receipt_email'] = $this->postedPaymentFields['mphb_stripe_email'];
		}

		$post_data['expand[]'] = 'balance_transaction';

		$post_data['source'] = $token;

		return $post_data;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){
		try {

			if ( !$this->checkMinimumAmount( $payment->getAmount(), $payment->getCurrency() ) ) {

				$minimumPrice = mphb_format_price( $this->getMinimumAmount( $payment->getCurrency() ), array(
					'currency_symbol' => MPHB()->settings()->currency()->getBundle()->getSymbol( $payment->getCurrency() )
					)
				);

				$message = sprintf( __( 'Sorry, the minimum allowed payment amount is %1$s to use this payment method.', 'motopress-hotel-booking' ), $minimumPrice );

				throw new \Exception( $message );
			}

			$createChargeRequest = $this->generatePaymentRequest( $this->token, $payment );

			$response = Stripe\StripeAPI::request( $createChargeRequest );

			if ( is_wp_error( $response ) ) {
				$errorMessage = $this->localizeErrorMessage( $response );

				throw new \Exception( $errorMessage );
			}

			$this->processApiResponse( $response, $payment );
			wp_redirect( MPHB()->settings()->pages()->getPaymentSuccessPageUrl( $payment ) );
			exit;
		} catch ( \Exception $e ) {

			$payment->addLog( sprintf( __( 'Stripe Payment Error: %s', 'motopress-hotel-booking' ), $e->getMessage() ) );
			$this->paymentFailed( $payment );

			wp_redirect( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) );
			exit;
		}
		return true;
	}

	/**
	 *
	 * @param \WP_Error s$wpError
	 * @return string
	 */
	private function localizeErrorMessage( $wpError ){
		$messages	 = $this->getLocalizedMessages();
		$code		 = $wpError->get_error_code();
		return isset( $messages[$code] ) ? $messages[$code] : $wpError->get_error_message();
	}

	/**
	 *
	 * @return array
	 */
	private function getLocalizedMessages(){
		return array(
			'invalid_number'		 => __( 'The card number is not a valid credit card number.', 'motopress-hotel-booking' ),
			'invalid_expiry_month'	 => __( 'The card\'s expiration month is invalid.', 'motopress-hotel-booking' ),
			'invalid_expiry_year'	 => __( 'The card\'s expiration year is invalid.', 'motopress-hotel-booking' ),
			'invalid_cvc'			 => __( 'The card\'s security code is invalid.', 'motopress-hotel-booking' ),
			'incorrect_number'		 => __( 'The card number is incorrect.', 'motopress-hotel-booking' ),
			'expired_card'			 => __( 'The card has expired.', 'motopress-hotel-booking' ),
			'incorrect_cvc'			 => __( 'The card\'s security code is incorrect.', 'motopress-hotel-booking' ),
			'incorrect_zip'			 => __( 'The card\'s zip code failed validation.', 'motopress-hotel-booking' ),
			'card_declined'			 => __( 'The card was declined.', 'motopress-hotel-booking' ),
			'missing'				 => __( 'There is no card on a customer that is being charged.', 'motopress-hotel-booking' ),
			'processing_error'		 => __( 'An error occurred while processing the card.', 'motopress-hotel-booking' ),
			'invalid_request_error'	 => __( 'Could not find payment information.', 'motopress-hotel-booking' ),
		);
	}

	/**
	 * Checks Stripe minimum amount value authorized per currency
	 *
	 * @param float $amount
	 * @param string $currency
	 */
	public function checkMinimumAmount( $amount, $currency ){
		$minimumAmount = $this->getMinimumAmount( $currency );
		return $this->retrieveStripeAmount( $amount ) > $this->retrieveStripeAmount( $minimumAmount );
	}

	/**
	 * @see https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe
	 *
	 * @param string $currency
	 * @return float
	 */
	public function getMinimumAmount( $currency ){

		switch ( strtoupper( $currency ) ) {
			case 'USD':
			case 'CAD':
			case 'EUR':
			case 'CHF':
			case 'AUD':
			case 'SGD':
				$minimumAmount	 = 0.50;
				break;
			case 'GBP':
				$minimumAmount	 = 0.30;
				break;
			case 'DKK':
				$minimumAmount	 = 2.50;
				break;
			case 'NOK':
			case 'SEK':
				$minimumAmount	 = 3.00;
				break;
			case 'JPY':
				$minimumAmount	 = 50.00;
				break;
			case 'MXN':
				$minimumAmount	 = 10.00;
				break;
			case 'HKD':
				$minimumAmount	 = 4.00;
				break;
			default:
				$minimumAmount	 = 0.50;
				break;
		}
		return $minimumAmount;
	}

	/**
	 *
	 * @param array $response
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 */
	private function processApiResponse( $response, $payment ){

		if ( isset( $response->balance_transaction ) && isset( $response->balance_transaction->fee ) ) {
			$fee = !empty( $response->balance_transaction->fee ) ? number_format( $response->balance_transaction->fee / 100, 2, '.', '' ) : 0;
			$net = !empty( $response->balance_transaction->net ) ? number_format( $response->balance_transaction->net / 100, 2, '.', '' ) : 0;
			update_post_meta( $payment->getId(), '_mphb_fee', $fee );
			update_post_meta( $payment->getId(), 'Net Revenue From Stripe', $net );
		}

		$type = $response->source->object === 'source' ? $response->source->type : $response->source->object;

		update_post_meta( $payment->getId(), '_mphb_payment_type', $type );

		// Re-get payment to prevent overriding directly updated meta
		$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );

		$payment->setTransactionId( $response->id );

		$message = sprintf( __( 'Stripe charge complete (Charge ID: %s)', 'motopress-hotel-booking' ), $response->id );
		$payment->addLog( $message );

		$this->paymentCompleted( $payment );

		return $response;
	}

	public function initPaymentFields(){

		$fields = array(
			'mphb_stripe_token'	 => array(
				'type'		 => 'hidden',
				'required'	 => true,
			),
			'mphb_stripe_email'	 => array(
				'type'		 => 'hidden',
				'meta_id'	 => '_mphb_email',
				'required'	 => true,
				'validate'	 => array( 'email' )
			),
		);

		if ( $this->needBillingAddress ) {

			$billingFields = array(
				'mphb_stripe_billing_address_city'			 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_city'
				),
				'mphb_stripe_billing_address_country'		 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_country'
				),
				'mphb_stripe_billing_address_country_code'	 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_country_code'
				),
				'mphb_stripe_billing_address_line1'			 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_address1'
				),
				'mphb_stripe_billing_address_line2'			 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_address2'
				),
				'mphb_stripe_billing_address_zip'			 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_zip'
				),
				'mphb_stripe_billing_address_state'			 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_state'
				),
				'mphb_stripe_billing_name'					 => array(
					'type'		 => 'hidden',
					'meta_id'	 => '_mphb_first_name'
				),
			);

			$fields = array_merge( $fields, $billingFields );
		}

		return $fields;
	}

	public function parsePaymentFields( $input, &$errors ){
		$isParsed = parent::parsePaymentFields( $input, $errors );

		if ( $isParsed ) {
			$this->token = $this->postedPaymentFields['mphb_stripe_token'];

			unset( $this->postedPaymentFields['mphb_stripe_token'] );
		}

		return $isParsed;
	}

	/**
	 *
	 * @return array
	 */
	public function getAvailableLocales(){
		return array(
			'auto'	 => __( 'Auto', 'motopress-hotel-booking' ),
			'zh'	 => __( 'Simplified Chinese', 'motopress-hotel-booking' ),
			'da'	 => __( 'Danish', 'motopress-hotel-booking' ),
			'nl'	 => __( 'Dutch', 'motopress-hotel-booking' ),
			'en'	 => __( 'English', 'motopress-hotel-booking' ),
			'fi'	 => __( 'Finnish', 'motopress-hotel-booking' ),
			'fr'	 => __( 'French', 'motopress-hotel-booking' ),
			'de'	 => __( 'German', 'motopress-hotel-booking' ),
			'it'	 => __( 'Italian', 'motopress-hotel-booking' ),
			'ja'	 => __( 'Japanese', 'motopress-hotel-booking' ),
			'no'	 => __( 'Norwegian', 'motopress-hotel-booking' ),
			'es'	 => __( 'Spanish', 'motopress-hotel-booking' ),
			'sv'	 => __( 'Swedish', 'motopress-hotel-booking' ),
		);
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function getCheckoutData( $booking ){
		$data = array(
			'publicKey'			 => $this->publicKey,
			'checkoutImageUrl'	 => $this->checkoutImageUrl,
			'allowRememberMe'	 => $this->allowRememberMe,
			'needBillingAddress' => $this->needBillingAddress,
			'useBitcoin'		 => $this->useBitcoin,
			'locale'			 => $this->locale,
			'amount'			 => $this->retrieveStripeAmount( $booking->calcDepositAmount() ),
		);
		return array_merge( parent::getCheckoutData( $booking ), $data );
	}

	/**
	 * Check if SSL is enabled and notify the user
	 */
	public function checkSSLNotice(){

		if ( !MPHB()->getSettingsMenuPage()->isCurrentPage() ) {
			return;
		}

		// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected.
		if ( !MPHB()->isSiteSSL() && (!MPHB()->settings()->payment()->isForceCheckoutSSL() && !class_exists( 'WordPressHTTPS' ) ) ) {
			$message = __( 'Stripe is enabled, but the <a href="%s">Force Secure Checkout</a> option is disabled. Please enable SSL and ensure your server has a valid SSL certificate. Otherwise, Stripe will only work in Test Mode.', 'motopress-hotel-booking' );
			$message = sprintf( $message, MPHB()->getSettingsPageUrl( 'payments' ) );
			echo '<div class=" notice notice-warning is-dismissible stripe-ssl-message"><p>' . $message . '</p></div>';
		}
	}

}
