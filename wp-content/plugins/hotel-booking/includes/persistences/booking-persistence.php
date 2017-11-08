<?php

namespace MPHB\Persistences;

class BookingPersistence extends CPTPersistence {

	/**
	 * @param array $atts Optional.
	 * @param bool $atts['room_locked'] Optional. Whether get only bookings that locked room.
	 * @param string $atts['date_from'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period begins at this date.
	 * @param string $atts['date_to'] Optional. Date in 'Y-m-d' format. Retrieve only bookings that consist dates from period ends at this date.
	 * @param bool $atts['period_edge_overlap'] Optional. Whether the edge days of period are overlapping.
	 * @param array $atts['rooms'] Optional. Room Ids.
	 *
	 * @return WP_Post[]|int[] List of posts.
	 */
	public function getPosts( $atts = array() ){
		return parent::getPosts( $atts );
	}

	protected function getDefaultQueryAtts(){
		$defaultAtts = array(
			'post_status' => array_keys( MPHB()->postTypes()->booking()->statuses()->getStatuses() ),
		);

		return array_merge( parent::getDefaultQueryAtts(), $defaultAtts );
	}

	protected function modifyQueryAtts( $atts ){
		$atts = parent::modifyQueryAtts( $atts );

		$atts = $this->_addRoomLockedCriteria( $atts );

		$atts = $this->_addPendingUserExpiredCriteria( $atts );

		$atts = $this->_addPendingPaymentExpiredCriteria( $atts );

		$atts = $this->_addPeriodCriteria( $atts );

		$atts = $this->_addRoomsCriteria( $atts );

		return $atts;
	}

	private function _addRoomLockedCriteria( $atts ){
		if ( isset( $atts['room_locked'] ) && $atts['room_locked'] ) {
			$atts['post_status'] = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
			unset( $atts['room_locked'] );
		}
		return $atts;
	}

	private function _addPendingUserExpiredCriteria( $atts ){
		if ( isset( $atts['pending_user_expired'] ) && $atts['pending_user_expired'] ) {
			$atts['post_status'] = array( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER );

			$queryPart = array(
				'key'		 => 'mphb_pending_user_expired',
				'value'		 => current_time( 'timestamp', true ),
				'type'		 => 'NUMERIC',
				'compare'	 => '<='
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['pending_user_expired'] );
		}
		return $atts;
	}

	private function _addPendingPaymentExpiredCriteria( $atts ){
		if ( isset( $atts['pending_payment_expired'] ) && $atts['pending_payment_expired'] ) {
			$atts['post_status'] = array( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT );

			$queryPart = array(
				'key'		 => 'mphb_pending_payment_expired',
				'value'		 => current_time( 'timestamp', true ),
				'type'		 => 'NUMERIC',
				'compare'	 => '<='
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

			unset( $atts['pending_payment_expired'] );
		}
		return $atts;
	}

	private function _addPeriodCriteria( $atts ){
		if ( isset( $atts['date_from'], $atts['date_to'] ) ) {

			$isEdgeOverlap = isset( $atts['period_edge_overlap'] ) ? (bool) $atts['period_edge_overlap'] : false;

			$queryPart = array(
				'relation' => 'AND',
				array(
					'key'		 => 'mphb_check_in_date',
					'value'		 => $atts['date_to'],
					'compare'	 => $isEdgeOverlap ? '<=' : '<'
				),
				array(
					'key'		 => 'mphb_check_out_date',
					'value'		 => $atts['date_from'],
					'compare'	 => $isEdgeOverlap ? '>=' : '>'
				),
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );
			unset( $atts['date_from'], $atts['date_to'] );
		}
		return $atts;
	}

	private function _addRoomsCriteria( $atts ){
		if ( isset( $atts['rooms'] ) ) {
			$queryPart = array(
				'key'		 => 'mphb_room_id',
				'value'		 => (array) $atts['rooms'],
				'compare'	 => 'IN'
			);

			$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );
			unset( $atts['rooms'] );
		}
		return $atts;
	}

	public function create( \MPHB\Entities\WPPostData $postData ){

		if ( $postData->getStatus() !== 'auto-draft' ) {

			$postStatus = $postData->getStatus();

			$postData->setStatus( 'auto-draft' );

			$postId = parent::create( $postData );

			$postData->setStatus( $postStatus );

			return $postId ? $this->updateStatus( $postId, $postStatus ) : $postId;
		} else {
			return parent::create( $postData );
		}
	}

	protected function updateStatus( $postId, $status ){
		$postAtts = array(
			'ID'			 => $postId,
			'post_status'	 => $status
		);
		return wp_update_post( $postAtts );
	}

}
