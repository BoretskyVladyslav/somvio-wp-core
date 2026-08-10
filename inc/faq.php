<?php
/**
 * FAQ page — hero body class, accordion section, assets.
 *
 * Accordion UI: Figma 300:2375. Hero: Figma 300:2369.
 * Dedicated FAQ page template owns markup; this file wires detection + enqueue.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the FAQ page.
 *
 * @return bool
 */
function somvio_is_faq_page() {
	if ( is_page( 'faq' ) ) {
		return true;
	}

	if ( is_page_template( 'page-faq.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_faq_page', false );
}

/**
 * Whether the current view should show the FAQ accordion section.
 *
 * @return bool
 */
function somvio_should_render_faq() {
	if ( somvio_is_faq_page() ) {
		return true;
	}

	if ( function_exists( 'somvio_is_service_single_page' ) && somvio_is_service_single_page() ) {
		$post = get_post();
		if ( $post instanceof WP_Post && 'airbnb-cleaning' === $post->post_name ) {
			return true;
		}
	}

	return false;
}

/**
 * Render Airbnb-specific FAQ on the Airbnb Cleaning service page.
 *
 * @return void
 */
function somvio_render_airbnb_faq() {
	if ( ! function_exists( 'somvio_is_service_single_page' ) || ! somvio_is_service_single_page() ) {
		return;
	}

	$post = get_post();
	if ( ! ( $post instanceof WP_Post ) || 'airbnb-cleaning' !== $post->post_name ) {
		return;
	}

	get_template_part(
		'template-parts/sections/faq',
		null,
		array(
			'variant' => 'airbnb',
		)
	);
}
add_action( 'generate_after_header', 'somvio_render_airbnb_faq', 20 );

/**
 * Mark FAQ so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_faq_body_class( $classes ) {
	if ( somvio_is_faq_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_faq_body_class' );

/**
 * Enqueue accordion script wherever FAQ needs it.
 *
 * @return void
 */
function somvio_enqueue_faq_assets() {
	if ( ! somvio_should_render_faq() ) {
		return;
	}

	if ( wp_script_is( 'somvio-accordion', 'enqueued' ) ) {
		return;
	}

	somvio_enqueue_theme_script( 'somvio-accordion', 'assets/js/accordion.js' );
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_faq_assets' );
