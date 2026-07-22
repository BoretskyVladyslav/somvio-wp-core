<?php
/**
 * See the Difference — before/after comparison slider.
 *
 * Figma node: 323:5028
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Before/After section on the homepage (below How It Works).
 *
 * @return void
 */
function somvio_render_before_after() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/before', 'after' );
}
add_action( 'generate_after_header', 'somvio_render_before_after', 25 );

/**
 * Enqueue before/after slider script on homepage only.
 *
 * @return void
 */
function somvio_enqueue_before_after_assets() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/before-after.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-before-after',
		get_stylesheet_directory_uri() . '/assets/js/before-after.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_before_after_assets' );
