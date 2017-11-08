<?php
/**
 * The Template for New Booking Email (Confirmation by User) content
 *
 * Email that will be sent to customer after booking is place.
 *
 * @version 1.2.1
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php printf( __( 'Dear %1$s %2$s, we received your request for reservation.', 'motopress-hotel-booking' ), '%customer_first_name%', '%customer_last_name%' ); ?>
<br/><br/>
<?php _e( 'Click the link below to confirm your booking.', 'motopress-hotel-booking' ); ?>
<br/>
<a href="%user_confirm_link%"> <?php _e( 'Confirm', 'motopress-hotel-booking' ); ?> </a>
<br/>
<small><?php _e( 'Note: link expires on', 'motopress-hotel-booking' ); ?> %user_confirm_link_expire% <?php _e( 'UTC', 'motopress-hotel-booking' ); ?></small>
<br/><br/>
<?php _e( 'If you did not place this booking, please ignore this email.', 'motopress-hotel-booking' ); ?>
<br/>
<h4><?php _e( 'Details of booking', 'motopress-hotel-booking' ) ?></h4>
<?php printf( __( 'ID: #%s', 'motopress-hotel-booking' ), '%booking_id%' ); ?>
<br/>
<?php printf( __( 'Check-in Date: %1$s, from %2$s', 'motopress-hotel-booking' ), '%check_in_date%', '%check_in_time%' ); ?>
<br/>
<?php printf( __( 'Check-out Date: %1$s, until %2$s', 'motopress-hotel-booking' ), '%check_out_date%', '%check_out_time%' ); ?>
<br/>
<?php printf( __( 'Adults: %s', 'motopress-hotel-booking' ), '%adults%' ); ?>
<br/>
<?php printf( __( 'Children: %s', 'motopress-hotel-booking' ), '%children%' ); ?>
<br/>
<?php printf( __( 'Accommodation: <a href="%1$s">%2$s</a>', 'motopress-hotel-booking' ), '%room_type_link%', '%room_type_title%' ); ?>
<br/>
<?php _e( 'Accommodation Rate:', 'motopress-hotel-booking' ); ?> %room_rate_title%
<br/>
%room_rate_description%
<br/>
<?php printf( __( 'Bed Type: %s', 'motopress-hotel-booking' ), '%room_type_bed_type%' ); ?>
<br/>

<h4><?php _e( 'Additional Services', 'motopress-hotel-booking' ); ?></h4>
%services%
<br/>

<h4><?php _e( 'Total Price:', 'motopress-hotel-booking' ) ?></h4>
%booking_total_price%
<br/>

<h4><?php _e( 'Customer Information', 'motopress-hotel-booking' ); ?></h4>
<?php printf( __( 'Name: %1$s %2$s', 'motopress-hotel-booking' ), '%customer_first_name%', '%customer_last_name%' ); ?>
<br/>
<?php printf( __( 'Email: %s', 'motopress-hotel-booking' ), '%customer_email%' ); ?>
<br/>
<?php printf( __( 'Phone: %s', 'motopress-hotel-booking' ), '%customer_phone%' ); ?>
<br/>
<?php printf( __( 'Note: %s', 'motopress-hotel-booking' ), '%customer_note%' ); ?>
<br/><br/>
<?php _e( 'Thank you!', 'motopress-hotel-booking' ); ?>