<?php
/**
 * Legal pages — Privacy Policy / Terms of Use hero body class.
 *
 * Figma nodes: 300:2218 (Privacy), 300:2239 (Terms).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is a legal page with a dark hero.
 *
 * @return bool
 */
function somvio_is_legal_page() {
	if ( is_page( 'privacy-policy' ) || is_page_template( 'page-privacy-policy.php' ) ) {
		return true;
	}

	if ( is_page( 'terms-of-use' ) || is_page_template( 'page-terms-of-use.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_legal_page', false );
}

/**
 * Mark legal pages so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_legal_body_class( $classes ) {
	if ( somvio_is_legal_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_legal_body_class' );
