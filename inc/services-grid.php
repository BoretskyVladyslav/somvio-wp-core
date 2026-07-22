<?php
/**
 * Tailored Cleaning Solutions — services grid section.
 *
 * Figma node: 300:1401
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the services grid on the homepage (below Why Choose).
 *
 * @return void
 */
function somvio_render_services_grid() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/services', 'grid' );
}
add_action( 'generate_after_header', 'somvio_render_services_grid', 15 );
