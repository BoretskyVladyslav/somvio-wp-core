<?php
/**
 * 404 + search fallback pages — Figma 420:6896 styling system.
 *
 * Full-bleed dark surfaces; no pre-footer CTA
 * (see somvio_should_skip_cta_banner).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mark 404 / search so the transparent sticky header merges with the hero
 * and body uses the branded dark fallback background.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_404_body_class( $classes ) {
	if ( is_404() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-404-page';
		$classes[] = 'somvio-dark-fallback';
	}

	if ( is_search() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-search-page';
		$classes[] = 'somvio-dark-fallback';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_404_body_class' );
