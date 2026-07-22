<?php
/**
 * Services page — compact inner hero via GeneratePress hooks.
 *
 * Figma node: 300:2161
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the Services page.
 *
 * @return bool
 */
function somvio_is_services_page() {
	if ( is_page( 'services' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_services_page', false );
}

/**
 * Mark Services (and other overlapping-header pages) so the transparent
 * sticky header merges with the hero background — same as homepage.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_services_body_class( $classes ) {
	if ( somvio_is_services_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_services_body_class' );

/**
 * Render the compact Services inner hero below the custom header.
 *
 * @return void
 */
function somvio_render_services_hero() {
	if ( ! somvio_is_services_page() ) {
		return;
	}

	get_template_part( 'template-parts/hero/services', 'hero' );
}
add_action( 'generate_after_header', 'somvio_render_services_hero', 5 );
