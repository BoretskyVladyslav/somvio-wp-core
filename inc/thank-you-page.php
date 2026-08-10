<?php
/**
 * Thank You page — body class + page script enqueue.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the Thank You page.
 *
 * @return bool
 */
function somvio_is_thank_you_page() {
	if ( is_page( 'thank-you' ) ) {
		return true;
	}

	if ( is_page_template( 'page-thank-you.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_thank_you_page', false );
}

/**
 * Mark Thank You for transparent sticky header merge.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_thank_you_body_class( $classes ) {
	if ( somvio_is_thank_you_page() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-thank-you-page';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_thank_you_body_class' );

/**
 * Enqueue Thank You page hydrator (sessionStorage summary).
 *
 * @return void
 */
function somvio_enqueue_thank_you_page_assets() {
	if ( ! somvio_is_thank_you_page() ) {
		return;
	}

	somvio_enqueue_theme_script( 'somvio-thank-you-page', 'assets/js/thank-you-page.js' );
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_thank_you_page_assets' );

/**
 * Enqueue global booking → Thank You redirect listener.
 *
 * Loaded only where quote/booking flows can fire the redirect event.
 *
 * @return void
 */
function somvio_enqueue_thank_you_redirect_assets() {
	if ( is_admin() || somvio_is_thank_you_page() ) {
		return;
	}

	$needs_redirect = ( function_exists( 'somvio_is_booking_page' ) && somvio_is_booking_page() )
		|| ( function_exists( 'somvio_needs_quote_calculator_assets' ) && somvio_needs_quote_calculator_assets() );

	if ( ! $needs_redirect ) {
		return;
	}

	if ( ! somvio_enqueue_theme_script( 'somvio-thank-you-redirect', 'assets/js/thank-you-redirect.js' ) ) {
		return;
	}

	$thank_you_url = function_exists( 'somvio_get_thank_you_url' )
		? somvio_get_thank_you_url()
		: home_url( '/thank-you/' );

	wp_localize_script(
		'somvio-thank-you-redirect',
		'somvioThankYou',
		array(
			'url'          => esc_url_raw( $thank_you_url ),
			'storageKey'   => 'somvio_booking_summary',
			'redirectDelay'=> 400,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_thank_you_redirect_assets' );
