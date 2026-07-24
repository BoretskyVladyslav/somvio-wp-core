<?php
/**
 * Booking page — hero body class for transparent header merge.
 *
 * Figma node: 418:6207
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the Booking page.
 *
 * @return bool
 */
function somvio_is_booking_page() {
	if ( is_page( 'booking' ) ) {
		return true;
	}

	if ( is_page_template( 'page-booking.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_booking_page', false );
}

/**
 * Mark Booking so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_booking_body_class( $classes ) {
	if ( somvio_is_booking_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_booking_body_class' );
