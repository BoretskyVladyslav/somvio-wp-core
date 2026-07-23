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
	return somvio_is_faq_page();
}

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

	$script_path = get_stylesheet_directory() . '/assets/js/accordion.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-accordion',
		get_stylesheet_directory_uri() . '/assets/js/accordion.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_faq_assets' );
