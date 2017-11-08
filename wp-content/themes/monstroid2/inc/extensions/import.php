<?php
/**
 * Import remap hooks
 */

add_filter( 'cherry_data_import_home_regex_replace', 'monstroid2_remap_shortcodes' );
add_action( 'cherry_data_import_remap_posts',        'monstroid2_remap_menus' );
add_action( 'cherry_data_import_remap_posts',        'monstroid2_remap_timetable' );

/**
 * Remap terms in shortocdes
 *
 * @param  array $regex Shortcode data for regex.
 * @return array
 */
function monstroid2_remap_shortcodes( $regex ) {

	$regex[] = array(
		'shortcode' => 'tm_pb_posts',
		'attr'      => 'categories',
	);

	$regex[] = array(
		'shortcode' => 'mprm_items',
		'attr'      => 'categ',
	);

	$regex[] = array(
		'shortcode' => 'mprm_categories',
		'attr'      => 'categ',
	);

	$regex[] = array(
		'shortcode' => 'tm_pb_menu_items',
		'attr'      => 'tags_list',
	);

	$regex[] = array(
		'shortcode' => 'tm_pb_cherry_testi',
		'attr'      => 'ids',
		'delimiter' => ' ',
	);

	$regex[] = array(
		'shortcode' => 'tm_pb_cherry_testi',
		'attr'      => 'category',
		'delimiter' => ',',
	);

	$regex[] = array(
		'shortcode' => 'tm_pb_cherry_services',
		'attr'      => 'category',
		'delimiter' => ',',
	);

	return $regex;
}

/**
 * Remap timetable ID's
 *
 * @param array $posts post remapping array.
 */
function monstroid2_remap_timetable( $posts ) {

	global $wpdb;

	if ( ! function_exists( 'cdi_tools' ) || ! is_callable( array( cdi_tools(), 'is_db_table_exists' ) ) ) {
		return;
	}

	if ( ! cdi_tools()->is_db_table_exists() ) {
		return;
	}

	$table_name = $wpdb->prefix . 'mp_timetable_data';

	$query = "
		SELECT *
		FROM $table_name
		WHERE 1
	";

	$data = $wpdb->get_results( $query );

	if ( empty( $data ) ) {
		return;
	}

	foreach ( $data as $row ) {

		$column_id = isset( $posts[ $row->column_id ] ) ? $posts[ $row->column_id ] : $row->column_id;
		$event_id  = isset( $posts[ $row->event_id ] ) ? $posts[ $row->event_id ] : $row->event_id;

		$wpdb->update(
			$table_name,
			array(
				'id'          => $row->id,
				'column_id'   => $column_id,
				'event_id'    => $event_id,
				'event_start' => $row->event_start,
				'event_end'   => $row->event_end,
				'user_id'     => $row->user_id,
				'description' => $row->description,
			),
			array(
				'id' => $row->id,
			),
			$format = array(
				'id'          => '%d',
				'column_id'   => '%d',
				'event_id'    => '%d',
				'event_start' => '%s',
				'event_end'   => '%s',
				'user_id'     => '%s',
				'description' => '%s',
			),
			$where_format = array(
				'term_id' => '%d',
			)
		);

	}
}

/**
 * Remap thumbanil ID's and menu terms
 *
 * @param array $posts post remapping array.
 */
function monstroid2_remap_menus( $posts ) {

	global $wpdb;

	$query = "
		SELECT *
		FROM $wpdb->termmeta
		WHERE meta_key LIKE 'mprm_taxonomy_%'
	";

	$taxmeta = $wpdb->get_results( $query );

	if ( empty( $taxmeta ) ) {
		return;
	}

	foreach ( $taxmeta as $tax ) {
		$value = unserialize( $tax->meta_value );
		if ( is_string( $value ) ) {
			$value = unserialize( $value );
		}
		if ( isset( $value['thumbnail_id'] ) && isset( $posts[ $value['thumbnail_id'] ] ) ) {
			$value['thumbnail_id'] = $posts[ $value['thumbnail_id'] ];
		}
		$wpdb->update(
			$wpdb->termmeta,
			array(
				'meta_id'    => $tax->meta_id,
				'term_id'    => $tax->term_id,
				'meta_key'   => 'mprm_taxonomy_' . $tax->term_id,
				'meta_value' => serialize( $value ),
			),
			array(
				'term_id' => $tax->term_id,
			),
			$format = array(
				'meta_id'    => '%d',
				'term_id'    => '%d',
				'meta_key'   => '%s',
				'meta_value' => '%s',
			),
			$where_format = array(
				'term_id' => '%d',
			)
		);
	}
}
