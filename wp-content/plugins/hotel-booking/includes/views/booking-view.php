<?php

namespace MPHB\Views;

use \MPHB\Entities;

class BookingView {

	public static function renderPriceBreakdown( Entities\Booking $booking ){
		echo self::generatePriceBreakdown( $booking );
	}

	public static function generatePriceBreakdown( Entities\Booking $booking ){
		$priceBreakdown = $booking->getPriceBreakdown();
		ob_start();
		if ( !empty( $priceBreakdown ) ) :
			?>
			<table class="mphb-price-breakdown">
				<tbody>
					<?php if ( isset( $priceBreakdown['room'] ) ) : ?>
						<tr>
							<th colspan="3"><?php printf( __( 'Rate: %s', 'motopress-hotel-booking' ), $priceBreakdown['room']['title'] ); ?></th>
						</tr>
						<tr>
							<th colspan="2"><?php _e( 'Dates', 'motopress-hotel-booking' ); ?></th>
							<th><?php _e( 'Price', 'motopress-hotel-booking' ); ?></th>
						</tr>
						<?php foreach ( $priceBreakdown['room']['list'] as $date => $datePrice ) : ?>
							<tr>
								<td colspan="2"><?php echo \MPHB\Utils\DateUtils::formatDateWPFront( \DateTime::createFromFormat( 'Y-m-d', $date ) ); ?></td>
								<td><?php echo mphb_format_price( $datePrice ); ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<th colspan="2"><?php _e( 'Subtotal', 'motopress-hotel-booking' ); ?></th>
							<th><?php echo mphb_format_price( $priceBreakdown['room']['total'] ); ?></th>
						</tr>
					<?php endif; ?>
					<?php if ( isset( $priceBreakdown['services'] ) && !empty( $priceBreakdown['services']['list'] ) ) : ?>
						<tr>
							<th colspan="3">
								<?php _e( 'Services', 'motopress-hotel-booking' ); ?>
							</th>
						</tr>
						<tr>
							<th><?php _e( 'Service', 'motopress-hotel-booking' ); ?></th>
							<th><?php _e( 'Details', 'motopress-hotel-booking' ); ?></th>
							<th><?php _e( 'Price', 'motopress-hotel-booking' ); ?></th>
						</tr>
						<?php foreach ( $priceBreakdown['services']['list'] as $serviceDetails ) : ?>
							<tr>
								<td><?php echo $serviceDetails['title']; ?></td>
								<td><?php echo $serviceDetails['details']; ?></td>
								<td><?php echo mphb_format_price( $serviceDetails['total'] ); ?></td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<th colspan="2">
								<?php _e( 'Services Subtotal', 'motopress-hotel-booking' ); ?>
							</th>
							<th>
								<?php echo mphb_format_price( $priceBreakdown['services']['total'] ); ?>
							</th>
						</tr>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<?php if ( !empty( $priceBreakdown['deposit'] ) ) : ?>
						<tr>
							<th colspan="2">
								<?php _e( 'Deposit', 'motopress-hotel-booking' ); ?>
							</th>
							<th>
								<?php
								echo mphb_format_price( $priceBreakdown['deposit'] );
								?>
							</th>
						</tr>
					<?php endif; ?>
					<tr>
						<th colspan="2">
							<?php _e( 'Total', 'motopress-hotel-booking' ); ?>
						</th>
						<th>
							<?php
							echo mphb_format_price( $priceBreakdown['total'] );
							?>
						</th>
					</tr>
				</tfoot>
			</table>
			<?php
		endif;
		return ob_get_clean();
	}

	public static function renderServicesList( Entities\Booking $booking ){
		$services = $booking->getServices();
		if ( !empty( $services ) ) {
			?>
			<p>
				<?php
				foreach ( $services as $serviceDetails ) {
					$serviceId	 = apply_filters( '_mphb_translate_post_id', $serviceDetails['id'], $booking->getLanguage() );
					$service	 = MPHB()->getServiceRepository()->findById( $serviceId );
					if ( !$service ) {
						continue;
					}

					echo $service->getTitle();
					if ( $service->isPayPerAdult() ) {
						echo '<em>';
						printf( _n( 'x %d adult', 'x %d adults', $serviceDetails['count'], 'motopress-hotel-booking' ), $serviceDetails['count'] );
						echo '</em>';
					}
					echo '<br />';
				}
				?>
			</p>
			<?php
		} else {
			echo "&#8212;";
		}
	}

	public static function renderCheckInDateWPFormatted( Entities\Booking $booking ){
		echo date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $booking->getCheckInDate()->getTimestamp() );
	}

	public static function renderCheckOutDateWPFormatted( Entities\Booking $booking ){
		echo date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $booking->getCheckOutDate()->getTimestamp() );
	}

	public static function renderTotalPriceHTML( Entities\Booking $booking ){
		echo mphb_format_price( $booking->getTotalPrice() );
	}

}
