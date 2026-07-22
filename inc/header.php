<?php
/**
 * Custom Somvio header via GeneratePress hooks & filters.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable the default GeneratePress primary navigation.
 *
 * Returning false prevents nav from rendering in any GP location
 * (above/below header, float left/right, sidebars).
 */
add_filter( 'generate_navigation_location', '__return_false' );

/**
 * Swap the default GP header for the Somvio header markup.
 *
 * @return void
 */
function somvio_replace_default_header() {
	remove_action( 'generate_header', 'generate_construct_header' );
	add_action( 'generate_header', 'somvio_render_header' );
}
add_action( 'after_setup_theme', 'somvio_replace_default_header', 20 );

/**
 * Render the custom header template part.
 *
 * @return void
 */
function somvio_render_header() {
	get_template_part( 'template-parts/header/site', 'header' );
}

/**
 * Prevent GeneratePress header alignment classes from centering our custom bar.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_filter_header_alignment_body_class( $classes ) {
	return array_values(
		array_diff(
			(array) $classes,
			array( 'header-aligned-center', 'header-aligned-right' )
		)
	);
}
add_filter( 'body_class', 'somvio_filter_header_alignment_body_class', 20 );

/**
 * Book Now CTA URL (filterable for ACF / booking page later).
 *
 * @return string
 */
function somvio_get_book_now_url() {
	return esc_url( apply_filters( 'somvio_book_now_url', home_url( '/booking/' ) ) );
}

/**
 * Primary phone display & tel: href (filterable).
 *
 * @return array{display: string, href: string}
 */
function somvio_get_phone() {
	$phone = apply_filters(
		'somvio_phone',
		array(
			'display' => '+44 7402 495410',
			'href'    => 'tel:+447402495410',
		)
	);

	return array(
		'display' => isset( $phone['display'] ) ? (string) $phone['display'] : '+44 7402 495410',
		'href'    => isset( $phone['href'] ) ? (string) $phone['href'] : 'tel:+447402495410',
	);
}

/**
 * Enqueue Montserrat (Figma) and header interaction script.
 *
 * @return void
 */
function somvio_enqueue_header_assets() {
	wp_enqueue_style(
		'somvio-montserrat',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap',
		array(),
		null
	);

	$script_path = get_stylesheet_directory() . '/assets/js/header.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_header_assets' );

/**
 * Append chevron icon to top-level parent menu items (Services).
 *
 * @param string   $title The menu item title.
 * @param WP_Post  $item  The current menu item.
 * @param stdClass $args  An object of wp_nav_menu() arguments.
 * @param int      $depth Depth of menu item.
 * @return string
 */
function somvio_nav_menu_item_title( $title, $item, $args, $depth ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $title;
	}

	if ( 0 !== (int) $depth || ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $title;
	}

	$icon = somvio_get_icon( 'icon-chevron-down' );

	if ( '' === $icon ) {
		return $title;
	}

	return $title . '<span class="somvio-header__chevron" aria-hidden="true">' . $icon . '</span>';
}
add_filter( 'nav_menu_item_title', 'somvio_nav_menu_item_title', 10, 4 );

/**
 * Add BEM link classes to primary header menu anchors.
 *
 * @param array    $atts  HTML attributes applied to the link.
 * @param WP_Post  $item  The current menu item.
 * @param stdClass $args  An object of wp_nav_menu() arguments.
 * @param int      $depth Depth of menu item.
 * @return array
 */
function somvio_nav_menu_link_attributes( $atts, $item, $args, $depth ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $atts;
	}

	$class = 0 === (int) $depth ? 'somvio-header__link' : 'somvio-header__sublink';

	$atts['class'] = isset( $atts['class'] ) && '' !== $atts['class']
		? $atts['class'] . ' ' . $class
		: $class;

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'somvio_nav_menu_link_attributes', 10, 4 );

/**
 * Fallback primary menu matching Figma (Services dropdown included).
 *
 * Used when no menu is assigned to the `primary` location.
 *
 * @param array|stdClass $args wp_nav_menu() arguments.
 * @return void
 */
function somvio_header_menu_fallback( $args = array() ) {
	$home_url = esc_url( home_url( '/' ) );
	$items    = array(
		array(
			'label' => __( 'Home', 'somvio' ),
			'url'   => $home_url,
		),
		array(
			'label'    => __( 'Services', 'somvio' ),
			'url'      => esc_url( home_url( '/services/' ) ),
			'children' => array(
				array(
					'label' => __( 'Regular Cleaning', 'somvio' ),
					'url'   => esc_url( home_url( '/services/regular-cleaning/' ) ),
				),
				array(
					'label' => __( 'Deep Cleaning', 'somvio' ),
					'url'   => esc_url( home_url( '/services/deep-cleaning/' ) ),
				),
				array(
					'label' => __( 'End of Tenancy', 'somvio' ),
					'url'   => esc_url( home_url( '/services/end-of-tenancy/' ) ),
				),
				array(
					'label' => __( 'Airbnb Cleaning', 'somvio' ),
					'url'   => esc_url( home_url( '/services/airbnb-cleaning/' ) ),
				),
				array(
					'label' => __( 'After Builders', 'somvio' ),
					'url'   => esc_url( home_url( '/services/after-builders/' ) ),
				),
			),
		),
		array(
			'label' => __( 'About Us', 'somvio' ),
			'url'   => esc_url( home_url( '/about-us/' ) ),
		),
		array(
			'label' => __( 'Reviews', 'somvio' ),
			'url'   => esc_url( home_url( '/reviews/' ) ),
		),
		array(
			'label' => __( 'FAQ', 'somvio' ),
			'url'   => esc_url( home_url( '/faq/' ) ),
		),
		array(
			'label' => __( 'Booking', 'somvio' ),
			'url'   => esc_url( home_url( '/booking/' ) ),
		),
		array(
			'label' => __( 'Contact', 'somvio' ),
			'url'   => esc_url( home_url( '/contact/' ) ),
		),
	);

	$menu_class = 'somvio-header__menu';

	if ( is_array( $args ) && ! empty( $args['menu_class'] ) ) {
		$menu_class = $args['menu_class'];
	} elseif ( is_object( $args ) && ! empty( $args->menu_class ) ) {
		$menu_class = $args->menu_class;
	}

	echo '<ul class="' . esc_attr( $menu_class ) . '">';

	foreach ( $items as $item ) {
		$has_children = ! empty( $item['children'] );
		$li_class     = 'menu-item';

		if ( $has_children ) {
			$li_class .= ' menu-item-has-children';
		}

		echo '<li class="' . esc_attr( $li_class ) . '">';
		echo '<a class="somvio-header__link" href="' . esc_url( $item['url'] ) . '">';
		echo esc_html( $item['label'] );

		if ( $has_children ) {
			$icon = somvio_get_icon( 'icon-chevron-down' );
			if ( '' !== $icon ) {
				// Trusted local theme SVG from assets/icons/.
				echo '<span class="somvio-header__chevron" aria-hidden="true">' . $icon . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		echo '</a>';

		if ( $has_children ) {
			echo '<ul class="sub-menu">';
			foreach ( $item['children'] as $child ) {
				echo '<li class="menu-item">';
				echo '<a class="somvio-header__sublink" href="' . esc_url( $child['url'] ) . '">';
				echo esc_html( $child['label'] );
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
		}

		echo '</li>';
	}

	echo '</ul>';
}
