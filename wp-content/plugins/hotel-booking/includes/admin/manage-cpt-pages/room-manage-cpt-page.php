<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\PostTypes\BookingCPT;
use \MPHB\Views;
use \MPHB\Entities;

class RoomManageCPTPage extends AbstractManageCPTPage {

	protected function addActionsAndFilters(){
		parent::addActionsAndFilters();
		add_action( 'restrict_manage_posts', array( $this, 'editPostsFilters' ) );

		add_action( 'parse_query', array( $this, 'parseQuery' ) );
		add_filter( 'request', array( $this, 'filterCustomOrderBy' ) );
		add_action( 'admin_footer', array( $this, 'outputScript' ) );
	}

	/**
	 *
	 * @param array $columns
	 * @return array
	 */
	public function filterColumns( $columns ){
		$customColumns	 = array(
			'room_type' => __( 'Accommodation Type', 'motopress-hotel-booking' )
		);
		$offset			 = array_search( 'date', array_keys( $columns ) ); // Set custom columns position before "DATE" column
		$columns		 = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		return $columns;
	}

	/**
	 *
	 * @param array $columns
	 * @return array
	 */
	public function filterSortableColumns( $columns ){
		$columns['room_type'] = 'mphb_room_type_id';

		return $columns;
	}

	/**
	 *
	 * @param string $column
	 * @param int $postId
	 */
	public function renderColumns( $column, $postId ){
		$room = MPHB()->getRoomRepository()->findById( $postId );
		switch ( $column ) {
			case 'room_type' :
				$roomType = MPHB()->getRoomTypeRepository()->findById( $room->getRoomTypeId() );
				if ( $roomType ) {
					printf( '<a href="%s">%s</a>', add_query_arg( 'mphb_room_type_id', $roomType->getId() ), $roomType->getTitle() );
				} else {
					echo '<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>';
				}
				break;
		}
	}

	public function editPostsFilters(){
		global $typenow;
		if ( $typenow === $this->postType ) {
			$selectedId	 = isset( $_GET['mphb_room_type_id'] ) ? $_GET['mphb_room_type_id'] : '';
			$roomTypes	 = MPHB()->getRoomTypeRepository()->getIdTitleList();
			echo '<select name="mphb_room_type_id">';
			echo '<option value="">' . __( 'All Accommodation Types', 'motopress-hotel-booking' ) . '</option>';
			foreach ( $roomTypes as $id => $title ) {
				echo '<option value="' . $id . '" ' . selected( $selectedId, $id, false ) . '>' . $title . '</option>';
			}
			echo '</select>';
		}
	}

	/**
	 *
	 * @param \WP_Query $query
	 */
	public function parseQuery( $query ){
		if ( $this->isCurrentPage() && $query->is_main_query() ) {
			if ( isset( $_GET['mphb_room_type_id'] ) && $_GET['mphb_room_type_id'] != '' ) {
				$query->query_vars['meta_key']		 = 'mphb_room_type_id';
				$query->query_vars['meta_value']	 = sanitize_text_field( $_GET['mphb_room_type_id'] );
				$query->query_vars['meta_compare']	 = '=';
			}
			remove_action( 'parse_query', array( $this, 'parseQuery' ) );
		}
	}

	public function filterCustomOrderBy( $vars ){
		if ( $this->isCurrentPage() ) {
			if ( isset( $vars['orderby'] ) ) {
				switch ( $vars['orderby'] ) {
					case 'mphb_room_type_id':
						$vars = array_merge( $vars, array(
							'meta_key'	 => 'mphb_room_type_id',
							'orderby'	 => 'meta_value_num'
							) );
						break;
				}
			}
		}
		return $vars;
	}

	public function outputScript(){
		if ( $this->isCurrentPage() ) {
			?>
			<script type="text/javascript">
				(function( $ ) {
					$( function() {

						var addGenerateRoomsButton = function() {
							var headerButtonClass = '<?php echo MPHB()->isWPVersion( '4.3', '>=' ) ? 'page-title-action' : 'add-new-h2' ?>';

							var generateRoomsButton = $( '<a />', {
								'class': headerButtonClass,
								'text': '<?php _e( 'Generate Accommodations', 'motopress-hotel-booking' ); ?>',
								'href': '<?php echo MPHB()->getRoomsGeneratorMenuPage()->getUrl(); ?>'
							} );

							$( '.' + headerButtonClass ).after( generateRoomsButton.clone() );
						}

						var addDescription = function() {
							var description = $( '<p />', {
								'text': '<?php _e( 'These are real accommodations like rooms, apartments, houses, villas, beds (for hostels) etc.', 'motopress-hotel-booking' ); ?>',
							} );
							//$( '#wpbody-content>.wrap>h1' ).first().after( description );
							//$( '.wp-header-end' ).first().before( description );
							$( '#wpbody-content>.wrap>ul.subsubsub' ).first().before( description );
						}

						addGenerateRoomsButton();
						addDescription();

					} );
				})( jQuery );
			</script>
			<?php
		}
	}

}
