<?php
/**
 * Homepage hero section via GeneratePress hooks.
 *
 * Figma node: 310:4965
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view should show the homepage hero.
 *
 * True for the WP Front Page (`is_front_page()`), and as a fallback for a
 * Page with slug `home` before it is assigned in Settings → Reading.
 *
 * @return bool
 */
function somvio_is_hero_page() {
	if ( is_front_page() ) {
		return true;
	}

	// Fallback while the static Front Page is not configured yet.
	if ( is_page( 'home' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_hero_page', false );
}

/**
 * Mark hero pages so CSS can merge the transparent header with the hero BG.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_hero_body_class( $classes ) {
	if ( somvio_is_hero_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_hero_body_class' );

/**
 * Render the homepage hero directly below the custom header.
 *
 * @return void
 */
function somvio_render_hero() {
	if ( ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/hero/hero' );
}
add_action( 'generate_after_header', 'somvio_render_hero', 5 );
