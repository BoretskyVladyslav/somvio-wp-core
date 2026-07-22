<?php
/**
 * How It Works accordion section.
 *
 * Figma node: 300:1456
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view should show How It Works.
 *
 * @return bool
 */
function somvio_should_render_how_it_works() {
	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		return true;
	}

	if ( function_exists( 'somvio_is_services_page' ) && somvio_is_services_page() ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_should_render_how_it_works', false );
}

/**
 * Render the How It Works section on the homepage (below Services Grid).
 *
 * @return void
 */
function somvio_render_how_it_works() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/how', 'it-works' );
}
add_action( 'generate_after_header', 'somvio_render_how_it_works', 20 );

/**
 * Render How It Works on the Services page (above CTA / footer).
 *
 * @return void
 */
function somvio_render_how_it_works_services() {
	if ( ! function_exists( 'somvio_is_services_page' ) || ! somvio_is_services_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/how', 'it-works' );
}
add_action( 'somvio_services_page_content', 'somvio_render_how_it_works_services', 10 );

/**
 * Enqueue accordion script on pages that show How It Works.
 *
 * @return void
 */
function somvio_enqueue_how_it_works_assets() {
	if ( ! somvio_should_render_how_it_works() ) {
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
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_how_it_works_assets' );
