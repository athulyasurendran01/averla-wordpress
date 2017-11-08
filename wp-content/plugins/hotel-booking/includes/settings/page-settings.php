<?php

namespace MPHB\Settings;

class PageSettings {

	/**
	 * Retrieve checkout page id.
	 * The Checkout Page ID or 0 if checkout page not setted.
	 *
	 * @return int
	 */
	public function getCheckoutPageId(){
		return $this->getPageId( 'checkout' );
	}

	/**
	 * Retrieve checkout page url.
	 * Description:
	 * The permalink URL or false if post does not exist or checkout page not setted.
	 *
	 * @return string|bool
	 */
	public function getCheckoutPageUrl(){
		$url = $this->getUrl( $this->getCheckoutPageId() );

		if ( MPHB()->settings()->payment()->isForceCheckoutSSL() ) {
			$url = preg_replace( '/^http:/', 'https:', $url );
		}

		return $url;
	}

	/**
	 *
	 * @return int
	 */
	public function getBookingConfirmPageId(){
		return $this->getPageId( 'booking_confirmation' );
	}

	/**
	 *
	 * @return string|false
	 */
	public function getBookingConfirmPageUrl(){
		return $this->getUrl( $this->getBookingConfirmPageId() );
	}

	/**
	 *
	 * @return int
	 */
	public function getSearchResultsPageId(){
		return $this->getPageId( 'search_results' );
	}

	/**
	 *
	 * @return string|bool False if search results page was not setted.
	 */
	public function getSearchResultsPageUrl(){
		return $this->getUrl( $this->getSearchResultsPageId() );
	}

	/**
	 *
	 * @return int|false
	 */
	public function getUserCancelRedirectPageId(){
		return $this->getPageId( 'user_cancel_redirect_page' );
	}

	/**
	 *
	 * @return string|false
	 */
	public function getUserCancelRedirectPageUrl(){
		return $this->getUrl( $this->getUserCancelRedirectPageId() );
	}

	/**
	 *
	 * @return int
	 */
	public function getPaymentSuccessPageId(){
		return $this->getPageId( 'payment_success' );
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment Optional.
	 * @return string
	 */
	public function getPaymentSuccessPageUrl( $payment = null, $additionalArgs = array() ){

		$url = $this->getUrl( $this->getPaymentSuccessPageId() );

		if ( !empty( $url ) ) {

			if ( $payment ) {
				$additionalArgs['payment_id'] = $payment->getId();
			}

			$url = add_query_arg( $additionalArgs, $url );
		}

		return $url;
	}

	/**
	 *
	 * @return int
	 */
	public function getPaymentFailedPageId(){
		return $this->getPageId( 'payment_failed' );
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment Optional.
	 * @return string
	 */
	public function getPaymentFailedPageUrl( $payment = null, $additionalArgs = array() ){
		$url = $this->getUrl( $this->getPaymentFailedPageId() );

		if ( !empty( $url ) ) {

			if ( $payment ) {
				$additionalArgs['payment_id'] = $payment->getId();
			}

			$url = add_query_arg( $additionalArgs, $url );
		}

		return $url;
	}

	/**
	 *
	 * @param string|int $id
	 * @return string|false
	 */
	public function getUrl( $id ){
		return get_permalink( $id );
	}

	/**
	 *
	 * @param string $name
	 * @return int
	 */
	private function getPageId( $name ){

		$pageId = get_option( 'mphb_' . $name . '_page' );

		$pageId = apply_filters( '_mphb_translate_page_id', $pageId );

		return (int) $pageId;
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setCheckoutPage( $id ){
		return update_option( 'mphb_checkout_page', $id );
	}

	/**
	 *
	 * @param string $id ID of page
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function setSearchResultsPage( $id ){
		return update_option( 'mphb_search_results_page', $id );
	}

}
