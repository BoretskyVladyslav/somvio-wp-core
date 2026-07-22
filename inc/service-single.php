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

/**
 * Render the Our Story / service overview section (Figma 362:5002).
 *
 * @return void
 */
function somvio_render_service_story() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/single-service', 'story' );
}
add_action( 'generate_after_header', 'somvio_render_service_story', 10 );

/**
 * Render the What's Included checklist section (Figma 366:5375).
 *
 * @return void
 */
function somvio_render_service_whats_included() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/single-service', 'whats-included' );
}
add_action( 'generate_after_header', 'somvio_render_service_whats_included', 12 );

/**
 * Render the Transparent Pricing section (Figma 366:5409).
 *
 * @return void
 */
function somvio_render_service_pricing() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/single-service', 'pricing' );
}
add_action( 'generate_after_header', 'somvio_render_service_pricing', 14 );

/**
 * Render the Gallery / See the Difference slider (Figma 366:5439).
 *
 * @return void
 */
function somvio_render_service_gallery() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/single-service', 'gallery' );
}
add_action( 'generate_after_header', 'somvio_render_service_gallery', 16 );

/**
 * Enqueue gallery slider script on Single Service pages.
 *
 * @return void
 */
function somvio_enqueue_service_gallery_assets() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/service-gallery.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-service-gallery',
		get_stylesheet_directory_uri() . '/assets/js/service-gallery.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_service_gallery_assets' );

/**
 * Render the Why Choose Our Service benefits grid (Figma 366:5552 / 300:3014).
 *
 * @return void
 */
function somvio_render_service_why_choose() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/single-service', 'why-choose' );
}
add_action( 'generate_after_header', 'somvio_render_service_why_choose', 18 );
