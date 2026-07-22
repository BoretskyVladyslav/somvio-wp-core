<?php
/**
 * Services list (zig-zag rows) — Figma 300:2170.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Services list on the Services page (above How It Works).
 *
 * @return void
 */
function somvio_render_services_list() {
	if ( ! function_exists( 'somvio_is_services_page' ) || ! somvio_is_services_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/services', 'list' );
}
add_action( 'somvio_services_page_content', 'somvio_render_services_list', 5 );
