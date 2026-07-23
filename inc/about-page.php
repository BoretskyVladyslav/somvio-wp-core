<?php
/**
 * About Us page — hero body class for transparent header merge.
 *
 * Figma node: 384:5980
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the About Us page.
 *
 * @return bool
 */
function somvio_is_about_page() {
	if ( is_page( 'about-us' ) || is_page( 'about' ) ) {
		return true;
	}

	if ( is_page_template( 'page-about.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_about_page', false );
}

/**
 * Mark About Us so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_about_body_class( $classes ) {
	if ( somvio_is_about_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_about_body_class' );
