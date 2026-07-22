<?php
/**
 * Single Service page — hero via GeneratePress hooks.
 *
 * Figma node: 362:4968
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view uses the Single Service page template.
 *
 * @return bool
 */
function somvio_is_service_single_page() {
	if ( is_page_template( 'page-single-service.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_service_single_page', false );
}

/**
 * Mark Single Service pages for transparent header + hero BG merge.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_service_single_body_class( $classes ) {
	if ( somvio_is_service_single_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_service_single_body_class' );

/**
 * Render the Single Service hero below the custom header.
 *
 * @return void
 */
function somvio_render_service_single_hero() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/hero/service-single', 'hero' );
}
add_action( 'generate_after_header', 'somvio_render_service_single_hero', 5 );
