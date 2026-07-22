<?php
/**
 * Testimonials — customer reviews and trust proof.
 *
 * Figma node: 325:5029
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Testimonials section on the homepage (below Before/After).
 *
 * @return void
 */
function somvio_render_testimonials() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/testimonials' );
}
add_action( 'generate_after_header', 'somvio_render_testimonials', 30 );
