<?php
/**
 * Single blog post — hero body class + section wiring.
 *
 * Hero: Figma 300:2415. Template owns markup; this file marks the transparent header merge.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is a single blog post.
 *
 * @return bool
 */
function somvio_is_blog_single() {
	return is_singular( 'post' );
}

/**
 * Mark single posts so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_blog_single_body_class( $classes ) {
	if ( somvio_is_blog_single() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_blog_single_body_class' );
