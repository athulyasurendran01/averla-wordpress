<?php
/**
 * Monstroid2 WooCommerce Theme hooks.
 *
 * @package Monstroid2
 */

/**
 * Enable Woocommerce theme support
 */

function monstroid2_woocommerce_support() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

/**
 * Change WooCommerce loop category title layout
 *
 * @param object $category
 *
 * @return string
 */
function monstroid2_woocommerce_template_loop_category_title( $category ) {
	?>
	<div class="title_count_block">
		<?php if ( 0 < $category->count ) {
			echo '<a href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '"  class="count">';
			echo apply_filters( 'woocommerce_subcategory_count_html', ' <span><span class="count__number">' . $category->count . '</span> ' . esc_html__( 'products', 'monstroid2' ) . '</span>', $category );
			echo '</a>';
		}
		?>
		<h3><?php
			echo '<a href="' . get_term_link( $category, 'product_cat' ) . '">';
			echo $category->name;
			echo '</a>';
			?>
		</h3>
	</div>
	<?php
}


function monstroid2_tm_categories_carousel_widget_arrows_pos() {
	return 'outside';
}

/**
 * Print sale and date format
 *
 * @return string
 */
function monstroid2_products_sale_end_date() {
	global $product;
	$sale_end_date = get_post_meta( get_the_id(), '_sale_price_dates_to', true );
	if ( '' != $sale_end_date ) {
		$data_format   = apply_filters( 'tm_products_sale_end_date_format', esc_html__( '%D %H:%M:%S', 'monstroid2' ) );
		$sale_end_date = '<span class="tm-products-sale-end-date" data-countdown="' . date( 'Y/m/d', $sale_end_date ) . '" data-format="' . $data_format . '"></span>';
	}
	echo $sale_end_date;
}

function monstroid2_products_format_sale_end_date() {
	return sprintf( '<span>%%D <i>%1$s</i></span> <span>%%H <i>%2$s</i></span><span>%%M <i>%3$s</i></span>', esc_html__( 'days', 'monstroid2' ), esc_html__( 'Hrs', 'monstroid2' ), esc_html__( 'Min', 'monstroid2' ) );
}


/**
 * Add WooCommerce 'New' and 'Featured' Flashes
 *
 */
function monstroid2_woocommerce_show_flash() {
	global $product;

	if ( ! $product->is_on_sale() ) {

		if ( 604800 > ( date( 'U' ) - strtotime( get_the_date() ) ) ) {
			echo '<span class="new">' . esc_html__( 'New', 'monstroid2' ) . '</span>';
		} else if ( $product->is_featured() ) {
			echo '<span class="featured">' . esc_html__( 'Featured', 'monstroid2' ) . '</span>';
		}
	}
}

/**
 * Change WooCommerce Price Output when Product is on Sale
 *
 * @param  string     $price Price
 * @param  int|string $from Regular price
 * @param  int|string $to Sale price
 *
 * @return string
 */
function monstroid2_woocommerce_get_price_html_from_to( $price, $from, $to ) {
	$price = '<ins>' . ( ( is_numeric( $to ) ) ? wc_price( $to ) : $to ) . '</ins> <del>' . ( ( is_numeric( $from ) ) ? wc_price( $from ) : $from ) . '</del>';

	return $price;
}


/**
 * Open wrap comapre & wishlist button grid-listing
 *
 */
function monstroid2_compare_wishlist_wrap_open( $atts = '' ) {
	if ( 'list' !== $atts ) {
		echo '<div class="wishlist_compare_button_block">';
	}
}

/**
 * Close wrap comapre & wishlist button grid-listing
 *
 */
function monstroid2_compare_wishlist_wrap_close( $atts = '' ) {
	if ( 'list' !== $atts ) {
		echo '</div>';
	}
}

/**
 * Open wrap comapre & wishlist button list-listing
 *
 */
function monstroid2_compare_wishlist_wrap_list_open( $atts = '' ) {
	if ( 'list' === $atts ) {
		echo '<div class="wishlist_compare_button_block">';
	}
}

/**
 * Close wrap comapre & wishlist button list-listing
 *
 */
function monstroid2_compare_wishlist_wrap_list_close( $atts = '' ) {
	if ( 'list' === $atts ) {
		echo '</div>';
	}
}

/**
 * Rewrite functions compare & wishlist for grid or listing layouts
 *
 */
function monstroid2_woocompare_add_button_loop( $atts = '' ) {
	if ( 'list' !== $atts && function_exists( 'tm_woocompare_add_button_loop' ) ) {
		tm_woocompare_add_button_loop( $atts );
	}
}

function monstroid2_woocompare_add_button_loop_list( $atts = '' ) {
	if ( 'list' === $atts && function_exists( 'tm_woocompare_add_button_loop' ) ) {
		tm_woocompare_add_button_loop( $atts );
	}
}

function monstroid2_woowishlist_add_button_loop( $atts = '' ) {
	if ( 'list' !== $atts && function_exists( 'tm_woowishlist_add_button_loop' ) ) {
		tm_woowishlist_add_button_loop( $atts );
	}
}

function monstroid2_woowishlist_add_button_loop_list( $atts = '' ) {
	if ( 'list' === $atts && function_exists( 'tm_woowishlist_add_button_loop' ) ) {
		tm_woowishlist_add_button_loop( $atts );
	}
}

/**
 * Rewrite functions rating for grid or listing layouts
 *
 */

function monstroid2_woocommerce_template_loop_rating( $atts = '' ) {
	if ( 'list' !== $atts ) {
		woocommerce_template_loop_rating( $atts );
	}
}

function monstroid2_woocommerce_template_loop_rating_list( $atts = '' ) {
	if ( 'list' === $atts ) {
		woocommerce_template_loop_rating( $atts );
	}
}

/**
 * Added custom thumbnail size for listing-line category thumbnail
 *
 */

function monstroid2_woocommerce_subcategory_thumbnail( $category, $atts = '' ) {
	global $_wp_additional_image_sizes;
	if ( 'list' === $atts ) {
		$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_list_size', 'monstroid2-thumb-listing-line-product' );
		$image_sizes          = get_intermediate_image_sizes();

		if ( in_array( $small_thumbnail_size, $image_sizes ) ) {
			$dimensions[ 'width' ]  = $_wp_additional_image_sizes[ $small_thumbnail_size ][ 'width' ];
			$dimensions[ 'height' ] = $_wp_additional_image_sizes[ $small_thumbnail_size ][ 'height' ];
			$dimensions[ 'crop' ]   = $_wp_additional_image_sizes[ $small_thumbnail_size ][ 'crop' ];
		} else {
			$dimensions[ 'width' ]  = '300';
			$dimensions[ 'height' ] = '300';
			$dimensions[ 'crop' ]   = 1;
		}

	} else {
		$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'shop_catalog' );
		$dimensions           = wc_get_image_size( $small_thumbnail_size );
	}

	$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );

	if ( $thumbnail_id ) {
		$image = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
		$image = $image[ 0 ];
	} else {
		$image = wc_placeholder_img_src();
	}

	if ( $image ) {
		// Prevent esc_url from breaking spaces in urls for image embeds
		// Ref: https://core.trac.wordpress.org/ticket/23605
		$image = str_replace( ' ', '%20', $image );

		echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '" width="' . esc_attr( $dimensions[ 'width' ] ) . '" height="' . esc_attr( $dimensions[ 'height' ] ) . '" />';
	}
}


/**
 * Add links into product title
 *
 */

function monstroid2_template_loop_product_title() {
	echo '<h3><a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">' . get_the_title() . '</a></h3>';
}

/**
 * Added custom thumbnail size for listing-line products
 *
 */

function monstroid2_get_product_thumbnail_size( $atts = '' ) {
	if ( 'list' === $atts ) {
		echo woocommerce_get_product_thumbnail( 'monstroid2-thumb-listing-line-product' );
	} else {
		echo woocommerce_get_product_thumbnail();
	}
}


/**
 * Wrap content single product
 *
 */
function monstroid2_single_product_open_wrapper() {
	echo '<div class="single_product_wrapper">';
}

function monstroid2_single_product_close_wrapper() {
	echo '</div>';
}

/**
 * Open wrap product loop elements
 *
 */
function monstroid2_product_image_wrap_open() {
	echo '<div class="block_product_thumbnail">';
}

/**
 * Close wrap product loop elements
 *
 */
function monstroid2_product_image_wrap_close() {
	echo '</div>';
}

/**
 * Open wrap content product loop elements
 *
 */
function monstroid2_product_content_wrap_open() {
	echo '<div class="block_product_content">';
}

/**
 * Open wrap content product loop elements
 *
 */
function monstroid2_product_content_wrap_close() {
	echo '</div>';
}


/**
 * Assets woocommerce script
 *
 */

function monstroid2_enqueue_assets() {

	if ( monstroid2_is_woocommerce_activated() ) {
		wp_register_script( 'jquery-easyzoom', MONSTROID2_THEME_URI . '/assets/js/min/easyzoom.min.js', array( 'jquery' ), '2.3.1', true );
		wp_enqueue_script( 'monstroid2-single-product-init', MONSTROID2_THEME_URI . '/assets/js/single-product.js', array( 'jquery-easyzoom' ), '1.0.0', true );
		wp_enqueue_script( 'countdown-script', MONSTROID2_THEME_URI . '/assets/js/min/jquery.countdown.min.js', array(), '2.1.0', true );
		wp_enqueue_script( 'monstroid2-woo-theme-script', MONSTROID2_THEME_URI . '/assets/js/woo-theme-script.js', array(), '1.0.0', true );
	}
}


function monstroid2_thumb_cols() {
	return 5; // .last class applied to every 4th thumbnail
}


/*
 *
 * Wrap products carousel - div
 */


function monstroid2_tm_products_carousel_widget_wrapper_open() {
	return '<div class="swiper-wrapper tm-products-carousel-widget-wrapper products">';
}

function monstroid2_tm_products_carousel_widget_wrapper_close() {
	return '</div>';
}


/**
 * Output the related products.
 *
 * @subpackage  Product
 */
function monstroid2_woocommerce_output_related_products() {

	$args = array(
		'posts_per_page'    => 10,
		'columns'           => 4,
		'orderby'           => 'rand'
	);

	woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );
}


/*
 *
 * Page Cart Checkout
 */

function monstroid2_woocommerce_cross_sell_display( $posts_per_page = 2, $columns = 4, $orderby = 'rand' ) {

	if ( is_checkout() ) {
		return;
	}

	wc_get_template( 'cart/cross-sells.php', array(
		'posts_per_page' => $posts_per_page,
		'orderby'        => $orderby,
		'columns'        => $columns,
	) );

}



/**
 * Open wrapper for carousel loop products
 *
 * @param array Provided arguments
 * @param bool Columns argument for backwards compat
 * @param bool Order by argument for backwards compat
 */
function monstroid2_woocommerce_product_loop_carousel_start( $echo = true, $swiper = false ) {

	$uniqid = uniqid();
	$GLOBALS['uniqid'] = $uniqid;
	$sidebar_position = get_theme_mod( 'sidebar_position' );
	$slides_per_view = 'fullwidth' === $sidebar_position || is_product() ? '4' : '3';
	$data_attr_line = 'class="monstroid2-carousel swiper-container"';
	$data_attr_line .= ' data-uniq-id="swiper-carousel-' . $uniqid . '"';
	$data_attr_line .= ' data-slides-per-view="' . $slides_per_view . '"';
	$data_attr_line .= ' data-slides-per-group="1"';
	$data_attr_line .= ' data-slides-per-column="1"';
	$data_attr_line .= ' data-space-between-slides="50"';
	$data_attr_line .= ' data-duration-speed="500"';
	$data_attr_line .= ' data-swiper-loop="false"';
	$data_attr_line .= ' data-free-mode="false"';
	$data_attr_line .= ' data-grab-cursor="true"';
	$data_attr_line .= ' data-mouse-wheel="false"';

	$html = '<div class="swiper-carousel-container">';
		$html.= '<div id="swiper-carousel-' . $uniqid . '" ' . $data_attr_line . '>';
			$html.= '<div class="swiper-wrapper">';
	if ( true === $echo ) {
		echo $html;
	}else{
		return $html;
	}
}

/**
 * Closed wrapper for carousel loop products
 *
 * @param type|bool $echo
 * @param type|bool $swiper
 */
function monstroid2_woocommerce_product_loop_carousel_end( $echo = true, $swiper = false ) {

	global $uniqid;
	$html = '</div>';
	$html.= '</div>';
	$html.= '<div id="swiper-carousel-' . $uniqid . '-next" class="swiper-button-next button-next"></div><div id="swiper-carousel-' . $uniqid . '-prev" class="swiper-button-prev button-prev"></div>';
	$html.= '</div>';
	unset($GLOBALS['uniqid']);
	if ( true === $echo ) {
		echo $html;
	} else{
		return $html;
	}
}



function monstroid2_woocommerce_cart_item_name( $title, $cart_item, $cart_item_key ) {
	// Split on spaces.
	$title = preg_split( '/\s+/', $title );

	// Slice array
	$title = array_slice( $title, 0, 4 );

	// Re-create the string.
	$title = join( ' ', $title );

	return '<span class="mini_cart_item_title">' . $title . '&hellip;</span>';
}

/*
 *
 * Function for Smartbox thumbnail
 */
function monstroid2_woocommerce_template_loop_product_thumbnail_custom_size() {
	echo woocommerce_get_product_thumbnail( 'monstroid2-smart-box-thumb' );
}

function monstroid2_products_smart_box_widget__cat_img_size() {
	return 'monstroid2-thumb-listing-line-product';
}

/*
 *
 * Remove woo-pagination and added theme pagination
 */
function monstroid2_woocommerce_pagination_args( $args = array() ) {

	unset( $args[ 'type' ] );
	unset( $args[ 'end_size' ] );
	unset( $args[ 'mid_size' ] );

	return $args;
}


/**
 * Add product categories in product list
 *
 * @return string
 */
function monstroid2_woocommerce_list_categories() {

	$sep    = '</li> <li>';
	$before = '<ul class="product-categories"><li>';
	$after  = '</li></ul>';

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '>=' ) ) {
		echo wc_get_product_category_list( get_the_id(), $sep, $before, $after );
	} else {
		global $product;
		if ( ! empty( $product ) ) {
			echo $product->get_categories( $sep, $before, $after );
		}
	}
}

/**
 * Display short description for listing-line template product
 *
 */
function monstroid2_woocommerce_display_short_excerpt( $atts = '' ) {
	if ( 'list' === $atts ) {
		echo '<div class="desc_products_listing_line">';
		woocommerce_template_single_excerpt( $atts );
		echo '</div>';
	}
}


/**
 * Add layout synchronization for product loop and product carousel widget.
 *
 */
function monstroid2_products_carousel_widget_hooks( $hooks ) {

	$hooks[ 'cat' ] = array(
		'woocommerce_before_shop_loop_item_title',
		'monstroid2_woocommerce_list_categories',
		14,
		1
	);

	$hooks[ 'title' ] = array(
		'woocommerce_shop_loop_item_title',
		'monstroid2_template_loop_product_title',
		10,
		1
	);

	$hooks[ 'price' ] = array(
		'woocommerce_after_shop_loop_item',
		'woocommerce_template_loop_price',
		5,
		1
	);

	return $hooks;
}

add_filter( 'tm_wc_ahax_filters_loader', 'monstroid2_wc_compare_wishlist_ahax_filters_loader' );
add_filter( 'tm_wc_compare_wishlist_loader', 'monstroid2_wc_compare_wishlist_ahax_filters_loader' );

function monstroid2_wc_compare_wishlist_ahax_filters_loader(){
	return '<div class="page-preloader"></div>';
}

