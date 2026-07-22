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
 * Render the benefits checklist strip below the Single Service hero.
 *
 * @return void
 */
function somvio_render_service_benefits() {
	if ( ! somvio_is_service_single_page() ) {
		return;
	}

	get_template_part( 'template-parts/sections/service', 'benefits' );
}
add_action( 'generate_after_header', 'somvio_render_service_benefits', 8 );

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
