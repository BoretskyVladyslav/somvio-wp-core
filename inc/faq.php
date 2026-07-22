<?php
/**
 * FAQ accordion section.
 *
 * Accordion UI: Figma 300:2375 (FAQ page). Rendered on the homepage before CTA.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view should show the FAQ section.
 *
 * @return bool
 */
function somvio_should_render_faq() {
	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_should_render_faq', false );
}

/**
 * Render the FAQ section on the homepage (below Testimonials).
 *
 * @return void
 */
function somvio_render_faq_home() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/faq' );
}
add_action( 'generate_after_header', 'somvio_render_faq_home', 35 );

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
