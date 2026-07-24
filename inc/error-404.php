<?php
/**
 * 404 page — Figma 420:6896.
 *
 * Full-bleed dark hero with background photo; no pre-footer CTA
 * (see somvio_should_skip_cta_banner).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mark 404 so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_404_body_class( $classes ) {
	if ( is_404() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-404-page';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_404_body_class' );
