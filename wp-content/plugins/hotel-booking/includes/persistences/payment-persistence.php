<?php

namespace MPHB\Persistences;

class PaymentPersistence extends CPTPersistence {

	/**
	 * @param array $atts Optional.
	 * @param int $atts['booking_id'] Optional. Retrieve payments for booking.
	 * @param bool $atts['abandon_ready'] Optional.
	 *
	 * @return WP_Post[]|int[] List of posts.
	 */
	public function getPosts( $atts = array() ){
		return parent::getPosts( $atts );
	}

	protected function getDefaultQueryAtts(){
		$defaultAtts = array(
			'post_status' => array_keys( MPHB()->postTypes()->payment()->statuses()->getStatuses() ),
		);

		return array_merge( parent::getDefaultQueryAtts(), $defaultAtts );
	}

	protected function modifyQueryAtts( $atts ){
		$atts = parent::modifyQueryAtts( $atts );

		$atts = $this->_addBookingCriteria( $atts );

		$atts = $this->_addPendingExpiredCriteria( $atts );

		$atts = $this->_addGatewayCriteria( $atts );

		$atts = $this->_addTransactionIdCriteria( $atts );

		return $atts;
	}

	private function _addBookingCriteria( $atts ){
		if ( !empty( $atts['booking_id'] ) ) {

			$queryPart = array(
				'key'		 => '_mphb_booking_id',
				'value'		 => (int) $atts['booking_id'],
				'type'		 => 'NUMERIC',
				'compare'	 => '='
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['booking_id'] );
		}
		return $atts;
	}

	private function _addPendingExpiredCriteria( $atts ){

		if ( isset( $atts['pending_expired'] ) && $atts['pending_expired'] ) {

			$atts['post_status'] = array(
				\MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING
			);

			$queryPart = array(
				'key'		 => '_mphb_pending_expired',
				'value'		 => current_time( 'timestamp', true ),
				'type'		 => 'NUMERIC',
				'compare'	 => '<='
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['pending_expired'] );
		}

		return $atts;
	}

	private function _addGatewayCriteria( $atts ){

		if ( isset( $atts['gateway'] ) && $atts['gateway'] ) {

			$queryPart = array(
				'key'	 => '_mphb_gateway_id',
				'value'	 => $atts['gateway']
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['gateway'] );
		}

		return $atts;
	}

	private function _addTransactionIdCriteria( $atts ){

		if ( isset( $atts['transaction_id'] ) && $atts['transaction_id'] ) {

			$queryPart = array(
				'key'	 => '_mphb_transaction_id',
				'value'	 => $atts['transaction_id']
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['transaction_id'] );
		}

		return $atts;
	}

}
