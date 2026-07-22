<?php
/**
 * "Why Choose Somvio?" advantages section.
 *
 * Figma node: 300:1393 (desktop) / 300:2790 (mobile carousel).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Why Choose section on the homepage (below hero).
 *
 * @return void
 */
function somvio_render_why_choose() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/why', 'choose-us' );
}
add_action( 'generate_after_header', 'somvio_render_why_choose', 10 );

/**
 * Enqueue Why Choose carousel script on homepage.
 *
 * @return void
 */
function somvio_enqueue_why_choose_assets() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/why-choose.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-why-choose',
		get_stylesheet_directory_uri() . '/assets/js/why-choose.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_why_choose_assets' );
