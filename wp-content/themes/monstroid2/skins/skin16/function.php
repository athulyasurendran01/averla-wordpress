<?php
/**
 * Skin16 functions, hooks and definitions.
 *
 * @package Monstroid2
 */

// Sets the theme assets URIs.
define( 'MONSTROID2_SKIN16_CSS', trailingslashit( MONSTROID2_THEME_URI ) . 'skins/skin16/assets/css' );

// Global functions
add_image_size( 'monstroid2-skin16-640-550', 640, 550, true );
add_image_size( 'monstroid2-skin16-769-431', 769, 431, true );

/**
 * Register assets.
 */
add_action( 'wp_enqueue_scripts', 'monstroid2_skin16_register_assets', 1 );

function monstroid2_skin16_register_assets() {
	wp_register_style( 'nucleo-mini', MONSTROID2_SKIN16_CSS . '/nucleo-mini.css', array(), '1.0.0' );
}

/**
 * Enqueue assets.
 */
add_action( 'wp_enqueue_scripts', 'monstroid2_skin16_enqueue_assets', 2 );

function monstroid2_skin16_enqueue_assets() {
	wp_enqueue_style( 'nucleo-mini', MONSTROID2_THEME_VERSION );
}

/**
 * Change footer layout template slug.
 */
add_filter( 'monstroid2_footer_layout_template_slug', 'monstroid2_skin16_footer_layout_template_slug' );

function monstroid2_skin16_footer_layout_template_slug( $layout ) {

	if ( 'default' == get_theme_mod( 'footer_layout_type' ) ) {
		return 'skins/skin16/template-parts/footer/layout';
	}

	return $layout;
}

/**
 * Change post template part slug
 */
add_filter( 'monstroid2_post_template_part_slug', 'monstroid2_skin16_post_template_part_slug', 10, 2 );

function monstroid2_skin16_post_template_part_slug( $blog_post_template, $blog_layout_type ) {

	if ( in_array( $blog_layout_type, array( 'grid-2-cols', 'grid-3-cols', 'vertical-justify' ) ) ) {
		$blog_post_template = 'skins/skin16/template-parts/post/grid/content';
	} elseif ( in_array( $blog_layout_type, array( 'masonry-2-cols', 'masonry-3-cols' ) ) ) {
		$blog_post_template = 'skins/skin16/template-parts/post/masonry/content';
	} else {
		$blog_post_template = 'skins/skin16/template-parts/post/default/content';
	}

	return $blog_post_template;
}

/**
 * Change single template part slug
 */
add_filter( 'monstroid2_single_post_template_part_slug', 'monstroid2_skin16_single_template_part_slug', 10, 2 );

function monstroid2_skin16_single_template_part_slug( $single_post_template, $single_post_type ) {

	$single_post_template = 'skins/skin16/template-parts/post/single/content-single';

	if ( 'modern' === $single_post_type && is_singular( 'post' ) ) {
		$single_post_template = 'skins/skin16/template-parts/post/single/content-single-modern';
	}

	return $single_post_template;
}

/**
 * Change single author bio avatar size
 */
add_filter( 'monstroid2_author_bio_avatar_size', 'monstroid2_skin16_author_bio_avatar_size' );

function monstroid2_skin16_author_bio_avatar_size( $size ) {
	return 130;
}

/**
 * Change post gallery thumb size
 */
add_filter( 'monstroid2_post_formats_gallery_thumbnail_size', 'monstroid2_skin16_post_formats_gallery_thumbnail_size' );

function monstroid2_skin16_post_formats_gallery_thumbnail_size( $size ) {
	$blog_featured_image = get_theme_mod( 'blog_featured_image', monstroid2_theme()->customizer->get_default( 'blog_featured_image' ) );
	$blog_layout         = get_theme_mod( 'blog_layout_type', monstroid2_theme()->customizer->get_default( 'blog_layout_type' ) );

	if ( 'small' === $blog_featured_image && 'default' === $blog_layout ) {
		$size['size'] = 'monstroid2-skin16-769-431';
	}

	return $size;
}

/**
 * Change default icons for share buttons
 */
add_filter( 'monstroid2_default_args_share_buttons', 'monstroid2_skin16_default_args_share_buttons' );

function monstroid2_skin16_default_args_share_buttons( $socials ) {

	$socials['facebook']['icon'] = 'nc-icon-mini social_logo-fb-simple';
	$socials['twitter']['icon'] = 'nc-icon-mini social_logo-twitter';
	$socials['google-plus']['icon'] = 'nc-icon-mini social_logo-google-plus';
	$socials['linkedin']['icon'] = 'nc-icon-mini social_logo-linkedin';
	$socials['pinterest']['icon'] = 'nc-icon-mini social_logo-pinterest';

	return $socials;
}

/**
 * Add placeholder attributes for comment form fields.
 */
add_filter( 'comment_form_defaults', 'monstroid2_skin16_modify_comment_form' );

function monstroid2_skin16_modify_comment_form( $args ) {
	$args = wp_parse_args( $args );

	if ( ! isset( $args['format'] ) ) {
		$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
	}

	$req       = get_option( 'require_name_email' );
	$aria_req  = ( $req ? " aria-required='true'" : '' );
	$html_req  = ( $req ? " required='required'" : '' );
	$html5     = 'html5' === $args['format'];
	$commenter = wp_get_current_commenter();

	$args['label_submit'] = esc_html__( 'Submit', 'monstroid2' );

	$args['fields']['author'] = '<p class="comment-form-author"><input id="author" class="comment-form__field" name="author" type="text" placeholder="' . esc_html__( 'Name:', 'monstroid2' ) . '" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . $html_req . ' /></p>';

	$args['fields']['email'] = '<p class="comment-form-email"><input id="email" class="comment-form__field" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' placeholder="' . esc_html__( 'E-mail:', 'monstroid2' ) . '" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-describedby="email-notes"' . $aria_req . $html_req . ' /></p>';

	$args['fields']['url'] = '';

	$args['comment_field'] = '<p class="comment-form-comment"><textarea id="comment" class="comment-form__field" name="comment" placeholder="' . esc_html__( 'Comment:', 'monstroid2' ) . '" cols="45" rows="8" aria-required="true" required="required"></textarea></p>';

	$args['title_reply_before'] = '<h4 id="reply-title" class="comment-reply-title">';

	$args['title_reply_after'] = '</h4>';

	$args['title_reply'] = esc_html__( 'Leave a reply', 'monstroid2' );

	return $args;
}

/**
 * Change pagination phrases
 */
add_filter( 'monstroid2_content_posts_pagination', 'monstroid2_skin16_content_posts_pagination' );

function monstroid2_skin16_content_posts_pagination( $pagination ) {

	$pagination['prev_text'] = esc_html__( 'Prev', 'monstroid2' );
	$pagination['next_text'] = esc_html__( 'Next', 'monstroid2' );

	return $pagination;
}

/**
 * Add custom icon fonts to builder.
 */
add_filter( 'tm_builder_custom_font_icons', 'monstroid2_skin16_builder_custom_font_icons' );

function monstroid2_skin16_builder_custom_font_icons( $icons ) {
	$icons['nucleooutline'] = array(
		'src'  => MONSTROID2_SKIN16_CSS . '/nucleo-outline.css',
		'base' => 'nucleo-outline',
	);

	unset( $icons['linear-icons'] );

	return $icons;
}

/**
 * Change cherry-services-list icon pack.
 */
add_filter( 'cherry_services_list_meta_options_args', 'monstroid2_skin16_change_services_list_icon_pack', 10 );

function monstroid2_skin16_change_services_list_icon_pack( $fields ) {

	$fields['fields']['cherry-services-icon']['auto_parse'] = true ;
	$fields['fields']['cherry-services-icon']['icon_data'] = array(
		'icon_set'    => 'monstroid2NucleoOutline',
		'icon_css'    => MONSTROID2_SKIN16_CSS . '/nucleo-outline.css',
		'icon_base'   => 'nc-icon-outline',
		'icon_prefix' => '',
	);

	return $fields;
}

/**
 * Change cherry-services-list icon format
 */
add_filter( 'cherry_services_default_icon_format', 'monstroid2_skin16_cherry_services_default_icon_format' );

function monstroid2_skin16_cherry_services_default_icon_format( $icon_format ) {
	return '<i class="nc-icon-outline %s"></i>';
}

/**
 * Customization cherry-projects prev button text.
 */
add_filter( 'cherry-projects-prev-button-text', 'monstroid2_skin16_cherry_projects_prev_button_text' );

function monstroid2_skin16_cherry_projects_prev_button_text( $prev_text ) {
	return esc_html__( 'PREV', 'monstroid2' );
}

/**
 * Customization cherry-projects next button text.
 */
add_filter( 'cherry-projects-next-button-text', 'monstroid2_skin16_cherry_projects_next_button_text' );

function monstroid2_skin16_cherry_projects_next_button_text( $next_text ) {
	return esc_html__( 'NEXT', 'monstroid2' );
}

/**
 * Customization cherry-projects author prefix text.
 */
add_filter( 'cherry-projects-author-settings', 'monstroid2_skin16_cherry_projects_author_text' );

function monstroid2_skin16_cherry_projects_author_text( $settings ) {
	$settings['prefix'] = esc_html__( 'by ', 'monstroid2' );

	return $settings;
}

/**
 * Customization cherry-projects date format.
 */
add_filter( 'cherry-projects-date-settings', 'monstroid2_skin16_cherry_projects_date_format' );

function monstroid2_skin16_cherry_projects_date_format( $settings ) {

	$date_format = get_option( 'date_format' );

	if ( ! empty( $date_format ) ) {
		$settings['date_format'] = $date_format;
	}

	return $settings;
}

/**
 * Add new services list template
 */
add_filter( 'cherry_services_listing_templates_list', 'monstroid2_skin16_cherry_services_listing_templates_list' );

function monstroid2_skin16_cherry_services_listing_templates_list( $tmpl ) {
	$tmpl['default-skin16'] = 'default-skin16.tmpl';
	$tmpl['media-icon-float-skin16'] = 'media-icon-float-skin16.tmpl';

	return $tmpl;
}

/**
 * Remove fields icons
 */
add_filter( 'monstroid2_search_form_input_icon', '__return_empty_string' );
add_filter( 'monstroid2_subscribe_view_icon', '__return_empty_string' );

/**
 * Add skin services single template.
 */
add_filter( 'cherry_services_single_templates_list', 'monstroid2_skin16_cherry_services_single_templates_list' );

function monstroid2_skin16_cherry_services_single_templates_list( $tmpl_list ) {

	$tmpl_list['single-skin16'] = 'single-skin16.tmpl';

	return $tmpl_list;
}

add_filter( 'monstroid2_specific_content_classes', 'monstroid2_skin16_specific_content_classes' );
function monstroid2_skin16_specific_content_classes( $classes ) {
	return array( 'col-xs-12', 'col-md-12', 'col-lg-8', 'col-lg-push-2' );
}
