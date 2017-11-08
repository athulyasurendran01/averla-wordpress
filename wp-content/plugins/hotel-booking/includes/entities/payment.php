<?php

namespace MPHB\Entities;

class Payment {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var string
	 */
	private $status;

	/**
	 *
	 * @var DateTime
	 */
	private $date;

	/**
	 *
	 * @var DateTime
	 */
	private $modifiedDate;

	/**
	 *
	 * @var float
	 */
	private $amount;

	/**
	 *
	 * @var string
	 */
	private $currency;

	/**
	 *
	 * @var int
	 */
	private $bookingId;

	/**
	 *
	 * @var string
	 */
	private $gatewayId;

	/**
	 *
	 * @var string
	 */
	private $gatewayMode;

	/**
	 *
	 * @var string
	 */
	private $transactionId;

	/**
	 *
	 * @var string
	 */
	private $email;

	/**
	 *
	 * @param array $args
	 */
	function __construct( $args ){

		if ( isset( $args['id'] ) ) {
			$this->id = $args['id'];
		}

		$this->date			 = isset( $args['date'] ) ? $args['date'] : new \DateTime( current_time( 'mysql' ) );
		$this->modifiedDate	 = isset( $args['modifiedDate'] ) ? $args['modifiedDate'] : new \DateTime( current_time( 'mysql' ) );
		$this->status		 = isset( $args['status'] ) ? $args['status'] : \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING;

		// Gateway Info
		$this->gatewayId	 = $args['gatewayId'];
		$this->gatewayMode	 = $args['gatewayMode'];
		$this->transactionId = isset( $args['transactionId'] ) ? $args['transactionId'] : '';

		// Payment Info
		$this->amount	 = $args['amount'];
		$this->currency	 = $args['currency'];
		$this->bookingId = $args['bookingId'];

		// Billing Fields
		$this->email = !empty( $args['email'] ) ? $args['email'] : '';
	}

	/**
	 *
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getKey(){
		return get_post_meta( $this->id, '_mphb_key', true );
	}

	/**
	 *
	 * @return string
	 */
	public function generateKey(){
		$key = uniqid( "payment_{$this->id}_", true );
		update_post_meta( $this->id, '_mphb_key', $key );
		return $key;
	}

	/**
	 *
	 * @return \DateTime
	 */
	public function getDate(){
		return $this->date;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus(){
		return $this->status;
	}

	/**
	 *
	 * @return float
	 */
	public function getAmount(){
		return $this->amount;
	}

	/**
	 *
	 * @return string
	 */
	public function getGatewayId(){
		return $this->gatewayId;
	}

	/**
	 *
	 * @return DateTime
	 */
	function getModifiedDate(){
		return $this->modifiedDate;
	}

	/**
	 *
	 * @return string
	 */
	function getCurrency(){
		return $this->currency;
	}

	/**
	 *
	 * @return int
	 */
	function getBookingId(){
		return $this->bookingId;
	}

	/**
	 *
	 * @return string
	 */
	function getGatewayMode(){
		return $this->gatewayMode;
	}

	/**
	 *
	 * @return string
	 */
	function getTransactionId(){
		return $this->transactionId;
	}

	/**
	 *
	 * @param int $id
	 */
	public function setId( $id ){
		$this->id = $id;
	}

	/**
	 *
	 * @param string $status
	 */
	public function setStatus( $status ){
		$this->status = $status;
	}

	/**
	 *
	 * @param string $id
	 */
	public function setTransactionId( $id ){
		$this->transactionId = $id;
	}

	/**
	 *
	 * @param array $paymentData
	 * @return Payment
	 */
	public static function create( $paymentData ){
		return new self( $paymentData );
	}

	/**
	 * Set expiration time of pending confirmation for payment
	 *
	 * @param int $expirationTime
	 */
	public function updateExpiration( $expirationTime ){
		update_post_meta( $this->id, '_mphb_pending_expired', $expirationTime );
	}

	/**
	 * Retrieve expiration time of pending confirmation for payment in UTC
	 *
	 * @return int
	 */
	public function retrieveExpiration(){
		return intval( get_post_meta( $this->id, '_mphb_pending_expired', true ) );
	}

	/**
	 * Delete expiration time of pending confirmation for payment
	 *
	 */
	public function deleteExpiration(){
		delete_post_meta( $this->id, '_mphb_pending_expired' );
	}

	/**
	 *
	 * @return string
	 */
	public function getEditLink(){
		$link = '';

		$post_type_object = get_post_type_object( MPHB()->postTypes()->payment()->getPostType() );

		if ( $post_type_object && $post_type_object->_edit_link ) {
			$action	 = '&action=edit';
			$link	 = admin_url( sprintf( $post_type_object->_edit_link . $action, $this->id ) );
		}

		return $link;
	}

	/**
	 *
	 * @param string $message
	 */
	public function addLog( $message ){
		$logs	 = $this->getLogs();
		$logs[]	 = array(
			'date'		 => mphb_current_time( 'mysql' ),
			'message'	 => $message
		);
		update_post_meta( $this->id, '_mphb_logs', $logs );
	}

	/**
	 *
	 * @return array
	 */
	public function getLogs(){
		$logs = get_post_meta( $this->id, '_mphb_logs', true );
		return is_array( $logs ) ? $logs : array();
	}

	/**
	 *
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}

}
