<?php
/**
 * Testimonials — customer reviews and trust proof.
 *
 * Figma: 300:2040 (section), 300:2042 (Social Proof badge), composition 389:6012.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Testimonials / Social Proof should render.
 *
 * Disabled until real client reviews are available.
 * Re-enable via: add_filter( 'somvio_show_testimonials', '__return_true' );
 *
 * @return bool
 */
function somvio_should_render_testimonials() {
	/**
	 * Master switch for fake/placeholder testimonials.
	 *
	 * @param bool $show Whether testimonials are enabled.
	 */
	if ( ! (bool) apply_filters( 'somvio_show_testimonials', false ) ) {
		return false;
	}

	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		return true;
	}

	if ( function_exists( 'somvio_is_service_single_page' ) && somvio_is_service_single_page() ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_should_render_testimonials', false );
}

/**
 * Render the Testimonials section (homepage below Before/After; Single Service below hero).
 *
 * @return void
 */
function somvio_render_testimonials() {
	if ( ! somvio_should_render_testimonials() ) {
		return;
	}

	get_template_part( 'template-parts/sections/testimonials' );
}
add_action( 'generate_after_header', 'somvio_render_testimonials', 30 );
