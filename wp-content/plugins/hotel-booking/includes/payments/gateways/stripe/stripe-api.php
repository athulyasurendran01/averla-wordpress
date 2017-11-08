<?php

namespace MPHB\Payments\Gateways\Stripe;

class StripeAPI {

	/**
	 * Stripe API Endpoint
	 */
	const ENDPOINT = 'https://api.stripe.com/v1/';

	/**
	 * Stripe API Version
	 * @see changes at https://stripe.com/docs/upgrades
	 */
	const API_VERSION = '2017-01-27';

	/**
	 * Secret API Key.
	 * @var string
	 */
	private static $secretKey = '';

	/**
	 * Set secret API Key.
	 * @param string $key
	 */
	public static function setSecretKey( $secretKey ){
		self::$secretKey = $secretKey;
	}

	/**
	 * Get secret key.
	 * @return string
	 */
	public static function getSecretKey(){
		return self::$secretKey;
	}

	/**
	 * Send the request to Stripe's API
	 *
	 * @param array $request
	 * @param string $api
	 * @return array|WP_Error
	 */
	public static function request( $request, $api = 'charges', $method = 'POST' ){

		$response = wp_safe_remote_post(
			self::ENDPOINT . $api, array(
			'method'	 => $method,
			'headers'	 => array(
				'Authorization'	 => 'Basic ' . base64_encode( self::getSecretKey() . ':' ),
				'Stripe-Version' => self::API_VERSION
			),
			'body'		 => $request,
			'timeout'	 => 70,
			'user-agent' => 'MPHB ' . MPHB()->getVersion()
			)
		);

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			return new \WP_Error( 'stripe_error', __( 'There was a problem connecting to the payment gateway.', 'motopress-hotel-booking' ) );
		}

		$parsedResponse = json_decode( $response['body'] );
		// Handle response
		if ( !empty( $parsedResponse->error ) ) {
			if ( !empty( $parsedResponse->error->param ) ) {
				$code = $parsedResponse->error->param;
			} elseif ( !empty( $parsedResponse->error->code ) ) {
				$code = $parsedResponse->error->code;
			} else {
				$code = 'stripe_error';
			}
			return new \WP_Error( $code, $parsedResponse->error->message );
		} else {
			return $parsedResponse;
		}
	}

}
