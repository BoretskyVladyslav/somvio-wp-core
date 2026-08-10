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

	somvio_enqueue_theme_script( 'somvio-why-choose', 'assets/js/why-choose.js' );
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_why_choose_assets' );
