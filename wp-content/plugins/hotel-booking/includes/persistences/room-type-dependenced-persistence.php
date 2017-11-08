<?php

namespace MPHB\Persistences;

class RoomTypeDependencedPersistence extends CPTPersistence {

	protected function modifyQueryAtts( $atts ){
		$atts = parent::modifyQueryAtts( $atts );

		$atts	 = $this->_changeToOriginalsRoomTypeIds( $atts );
		$atts	 = $this->_addRoomTypeCriteria( $atts );

		return $atts;
	}

	protected function _changeToOriginalsRoomTypeIds( $atts ){
		if ( !isset( $atts['room_type'] ) ) {
			return $atts;
		}

		if ( is_array( $atts['room_type'] ) ) {
			$atts['room_type'] = array_map( function( $id ) {
				return MPHB()->translation()->getOriginalId( $id, MPHB()->postTypes()->roomType()->getPostType() );
			}, $atts['room_type'] );
		} else {
			$atts['room_type'] = MPHB()->translation()->getOriginalId( $atts['room_type'], MPHB()->postTypes()->roomType()->getPostType() );
		}
		return $atts;
	}

	protected function _addRoomTypeCriteria( $atts ){

		if ( !isset( $atts['room_type'] ) ) {
			return $atts;
		}

		if ( is_array( $atts['room_type'] ) ) {
			$queryPart = array(
				'key'		 => 'mphb_room_type_id',
				'value'		 => $atts['room_type'],
				'compare'	 => 'IN'
			);
		} else {
			$queryPart = array(
				'key'		 => 'mphb_room_type_id',
				'value'		 => $atts['room_type'],
				'compare'	 => '='
			);
		}

		$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null  );

		unset( $atts['room_type'] );

		return $atts;
	}

}
