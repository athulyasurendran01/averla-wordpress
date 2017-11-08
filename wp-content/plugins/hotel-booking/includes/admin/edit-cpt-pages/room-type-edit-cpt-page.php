<?php

namespace MPHB\Admin\EditCPTPages;

use \MPHB\Entities;

class RoomTypeEditCPTPage extends EditCPTPage {

	public function customizeMetaBoxes(){
		if ( !MPHB()->translation()->isTranslationPage() ) {
			add_meta_box( 'rooms', __( 'Generate Accommodations', 'motopress-hotel-booking' ), array( $this, 'renderRoomMetaBox' ), $this->postType, 'normal' );
		}
	}

	public function renderRoomMetaBox( $post, $metabox ){
		$roomType = MPHB()->getRoomTypeRepository()->findById( $post->ID );
		?>
		<table class="form-table">
			<tbody>
				<?php if ( $this->isCurrentAddNewPage() ) { ?>
					<tr>
						<th>
							<label for="mphb_generate_rooms_count"><?php _e( 'Number of Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<input type="number" required="required" name="mphb_generate_rooms_count" min="0" step="1" value="1" class="small-text"/>
								<p class="description"><?php _e( 'Count of real accommodations of this type in your hotel.', 'motopress-hotel-booking' ); ?></p>
							</div>
						</td>
					</tr>
					<?php
				} else {

					$roomTypeOriginalId = $roomType->getOriginalId();

					$allRoomsLink = MPHB()->postTypes()->room()->getManagePostsLink(
						array(
							'mphb_room_type_id' => $roomTypeOriginalId
						)
					);

					$activeRoomsLink = MPHB()->postTypes()->room()->getManagePostsLink(
						array(
							'mphb_room_type_id'	 => $roomTypeOriginalId,
							'post_status'		 => 'publish'
						)
					);

					$generateRoomsLink = MPHB()->getRoomsGeneratorMenuPage()->getUrl(
						array(
							'mphb_room_type_id' => $roomTypeOriginalId
						)
					);

					$totalRoomsCount = MPHB()->getRoomPersistence()->getCount(
						array(
							'room_type'		 => $roomTypeOriginalId,
							'post_status'	 => 'all'
						)
					);

					$activeRoomsCount = MPHB()->getRoomPersistence()->getCount(
						array(
							'room_type'		 => $roomTypeOriginalId,
							'post_status'	 => 'publish'
						)
					);
					?>
					<tr>
						<th>
							<label><?php _e( 'Total Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<span>
									<?php echo $totalRoomsCount; ?>
								</span>
								<span class="description">
									<a href="<?php echo $allRoomsLink; ?>" target="_blank">
										<?php _e( 'Show Accommodations', 'motopress-hotel-booking' ); ?>
									</a>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php _e( 'Active Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<span>
									<?php echo $activeRoomsCount; ?>
								</span>
								<span class="description">
									<a href="<?php echo $activeRoomsLink; ?>" target="_blank">
										<?php _e( 'Show Accommodations', 'motopress-hotel-booking' ); ?>
									</a>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<div>
								<a href="<?php echo $generateRoomsLink; ?>">
									<?php _e( 'Generate Accommodations', 'motopress-hotel-booking' ); ?>
								</a>
							</div>
						</td>
					</tr>
				<?php } ?>

			</tbody>
		</table>
		<?php
	}

	public function saveMetaBoxes( $postId, $post, $update ){

		if ( !parent::saveMetaBoxes( $postId, $post, $update ) ) {
			return false;
		}

		$needFixFields = array(
			'mphb_children_capacity',
			'mphb_adults_capacity'
		);

		foreach ( $needFixFields as $fieldName ) {
			// update_post_meta not save '0' by default
			if ( isset( $_POST[$fieldName] ) && $_POST[$fieldName] == '0' ) {
				update_post_meta( $postId, $fieldName, '00' );
			}
		}

		$roomsCount = !empty( $_POST['mphb_generate_rooms_count'] ) ? absint( $_POST['mphb_generate_rooms_count'] ) : 0;
		if ( $roomsCount > 0 ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $postId );
			if ( $roomType ) {
				MPHB()->getRoomRepository()->generateRooms( $roomType, $roomsCount );
			}
		}

		return true;
	}

}
