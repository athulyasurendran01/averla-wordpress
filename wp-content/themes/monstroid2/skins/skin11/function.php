<?php
/**
 * Skin11 functions, hooks and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Monstroid2
 */

// Change terms permalink text
add_filter( 'cherry-projects-terms-permalink-text', 'monstroid2_skin11_projects_terms_permalink_text' );

// Change layout of single project
add_filter( 'monstroid2_content_classes', 'monstroid2_skin11_set_specific_content_classes' );

// Change breadcrumbs separator
add_filter( 'cherry_breadcrumb_args', 'monstroid2_skin11_breadcrumbs_settings' );

// Add new services list template
add_filter( 'cherry_services_listing_templates_list', 'monstroid2_skin11_cherry_services_listing_templates_list' );

// Add new single service template
add_filter( 'cherry_services_single_templates_list', 'monstroid2_skin11_cherry_services_single_templates_list' );

// Add title to blog page
add_filter( 'monstroid2_single_post_title_html', 'monstroid2_skin11_single_post_title_html' );

// Invisible button read more in module post
add_filter( 'monstroid2_module_post_btn_settings_layout_1', 'monstroid2_skin11_module_post_btn_settings_layout_1' );

// Cherry services hooks.
add_filter( 'cherry_services_list_meta_options_args', 'monstroid2_skin11_cherry_services_list_meta_options_args' );
add_filter( 'cherry_services_data_callbacks', 'monstroid2_skin11_cherry_services_data_callbacks', 10, 2 );


/**
 * Change terms permalink text
 */
function monstroid2_skin11_projects_terms_permalink_text() {
	return esc_html__( 'view projects', 'monstroid2' );
}

/**
 * Change layout for custom post type
 */
function monstroid2_skin11_set_specific_content_classes( $layout_classes ) {
	$sidebar_position = get_theme_mod( 'sidebar_position' );

	if ( ('fullwidth' === $sidebar_position && is_single() && !is_singular( 'post' )) ) {
		$layout_classes = array( 'col-xs-12' );
	}

	return $layout_classes;
}

/**
 * Change breadcrumbs separator
 */
function monstroid2_skin11_breadcrumbs_settings( $args ) {
	$args['separator'] = ' | ';

	return $args;
}

/**
 * Add new services list template
 */

function monstroid2_skin11_cherry_services_listing_templates_list( $tmpl ) {

	$tmpl['media-thumb'] = 'media-thumb.tmpl';
	$tmpl['default-skin1'] = 'default-skin1.tmpl';
	return $tmpl;
}

/**
 * Add new single service template
 */

function monstroid2_skin11_cherry_services_single_templates_list( $tmpl ) {

	$tmpl['single-skin1'] = 'single-skin1.tmpl';
	return $tmpl;
}

add_action( 'cherry_projects_before_main_content', 'monstroid2_skin11_cherry_projects_before_main_content' );
function monstroid2_skin11_cherry_projects_before_main_content() {
	if ( ! is_tax( array( 'projects_category', 'projects_tag' ) ) ) {
		return;
	}

	$title = '<h2 class="project-terms-title">' . single_term_title( '', false ) . '</h2>';
	$desc  = get_the_archive_description();
	$image = monstroid2_utility()->utility->media->get_image(
		array(
			'html'        => '<img src="%3$s" %2$s alt="%4$s" %5$s >',
			'class'       => 'term-img',
			'size'        => 'monstroid2-thumb-xl',
			'placeholder' => false,
			'echo'        => false,
		),
		'term',
		get_queried_object_id()
	); ?>

	<div class="project-terms-caption grid-default-skin1">
		<div class="project-terms-caption-header">
			<div class="project-terms-thumbnail">
				<?php echo $image; ?>
			</div>
			<?php
			if ( single_term_title( '', false ) ) {
				echo $title;
			} ?>
		</div>
		<div class="project-terms-caption-content">
			<div class="container">
				<?php if ( $desc ) {
					echo $desc;
				} ?>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Add title to blog page
 */
function monstroid2_skin11_single_post_title_html(){
	return '<h4 class="page-title">%s</h4>';
}

/**
 * Invisible button read more in module post
 */
function monstroid2_skin11_module_post_btn_settings_layout_1( $args ) {

	$args = array(
		'visible' => false,
		'text'    => esc_html__( 'Read More', 'monstroid2' ),
		'icon'    => '<i class="linearicon linearicon-arrow-right"></i>',
		'class'   => 'tm-posts_more-btn link',
		'html'    => '<a href="%1$s" %3$s><span class="link__text">%4$s</span>%5$s</a>',
		'echo'    => true,
	);
	return $args;
}


/**
 * Add new post-meta field to cherry services.
 *
 */
function monstroid2_skin11_cherry_services_list_meta_options_args( $args ) {

	$args['fields']['cherry-services-thumb'] = array(
		'type'               => 'media',
		'element'            => 'control',
		'parent'             => 'general',
		'multi_upload'       => false,
		'library_type'       => 'image',
		'upload_button_text' => esc_html__( 'Add thumbnails', 'monstroid2' ),
		'label'              => esc_html__( 'Service thumbnails', 'monstroid2' ),
		'sanitize_callback'  => 'esc_attr',
	);

	return $args;
}

/**
 * Add new macros %%THUMB%% to cherry services.
 */
function monstroid2_skin11_cherry_services_data_callbacks( $data, $atts ) {

	$data['thumb'] = 'monstroid2_get_service_thumb';

	return $data;
}

/**
 * Callback function to macros %%THUMB%%.
 */
function monstroid2_get_service_thumb ( $args = array() ) {

	$callbacks = cherry_services_templater()->callbacks;
	$atts      = $callbacks->atts;

	if ( ! isset( $atts['show_media'] ) ) {
		return;
	}

	$atts['show_media'] = filter_var( $atts['show_media'], FILTER_VALIDATE_BOOLEAN );

	if ( true !== $atts['show_media'] ) {
		return;
	}

	global $post;
	$thumb = get_post_meta( $post->ID, 'cherry-services-thumb', true );

	if ( ! $thumb ) {
		return;
	}

	$format = apply_filters( 'monstroid2_cherry_services_default_thumb_format', '<div class="service-thumb"><img src="%1$s" alt="%2$s" ></div>' );

	$args = wp_parse_args( $args, array(
		'wrap'   => 'div',
		'class'  => '',
		'base'   => 'thumb_wrap',
		'size'   => 'full',
		'format' => $format,
	) );

	$result = sprintf( $args['format'], wp_get_attachment_image_url( esc_attr( $thumb ), $args['size'] ), $callbacks->post_title() );

	return $callbacks->macros_wrap( $args, $result );
}

