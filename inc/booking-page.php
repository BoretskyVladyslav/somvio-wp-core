<?php
/**
 * Booking page — hero body class, form assets.
 *
 * Figma nodes: 418:6207 (hero), 418:6213 (form + summary)
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

/**
 * Enqueue booking form script (dedicated funnel; modal calculator stays separate).
 *
 * @return void
 */
function somvio_enqueue_booking_form_assets() {
	if ( ! somvio_is_booking_page() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/booking-form.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-booking-form',
		get_stylesheet_directory_uri() . '/assets/js/booking-form.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);

	$privacy_url = function_exists( 'somvio_get_privacy_policy_page_id' )
		? get_permalink( somvio_get_privacy_policy_page_id() )
		: home_url( '/privacy-policy/' );
	$terms_id    = function_exists( 'somvio_get_page_id_by_slug' )
		? somvio_get_page_id_by_slug( 'terms-of-use' )
		: 0;
	$terms_url   = $terms_id > 0 ? get_permalink( $terms_id ) : home_url( '/terms-of-use/' );

	wp_localize_script(
		'somvio-booking-form',
		'somvioBookingForm',
		array(
			'restUrl' => esc_url_raw( rest_url( 'somvio/v1/quote/submit' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'rates'   => function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array(),
			'services'=> function_exists( 'somvio_get_quote_service_options' ) ? somvio_get_quote_service_options() : array(),
			'i18n'    => array(
				'stepOf'                => __( 'Step %1$d of %2$d', 'somvio' ),
				'selectDatePlaceholder' => __( 'Select date', 'somvio' ),
				'selectTime'            => __( 'Please select a time slot.', 'somvio' ),
				'selectDate'            => __( 'Please select a valid date.', 'somvio' ),
				'selectDateTime'        => __( 'Select a date and time to continue', 'somvio' ),
				'selectService'         => __( 'Please select a service.', 'somvio' ),
				'nextStep'              => __( 'Next Step', 'somvio' ),
				'back'                  => __( 'Back', 'somvio' ),
				'complete'              => __( 'Complete Booking', 'somvio' ),
				'submitting'            => __( 'Submitting…', 'somvio' ),
				'required'              => __( 'Please complete the required fields.', 'somvio' ),
				'invalidEmail'          => __( 'Please enter a valid email address.', 'somvio' ),
				'invalidPhone'          => __( 'Please enter a valid phone number.', 'somvio' ),
				'invalidName'           => __( 'Please enter your name.', 'somvio' ),
				'invalidAddress'        => __( 'Please enter your street address.', 'somvio' ),
				'termsRequired'         => __( 'Please accept the Terms & Conditions and Privacy Policy.', 'somvio' ),
				'completeContact'       => __( 'Complete required fields and accept the terms to continue', 'somvio' ),
				'requiredField'         => __( 'This field is required.', 'somvio' ),
				'submitError'           => __( 'Something went wrong. Please try again.', 'somvio' ),
				'backHome'              => __( 'Back to Home', 'somvio' ),
				'estimatedTotal'        => __( 'Estimated total', 'somvio' ),
				'none'                  => __( 'None', 'somvio' ),
				'notSelected'           => __( 'Not selected', 'somvio' ),
				'months'         => array(
					__( 'January', 'somvio' ),
					__( 'February', 'somvio' ),
					__( 'March', 'somvio' ),
					__( 'April', 'somvio' ),
					__( 'May', 'somvio' ),
					__( 'June', 'somvio' ),
					__( 'July', 'somvio' ),
					__( 'August', 'somvio' ),
					__( 'September', 'somvio' ),
					__( 'October', 'somvio' ),
					__( 'November', 'somvio' ),
					__( 'December', 'somvio' ),
				),
				'monthsShort'    => array(
					__( 'Jan', 'somvio' ),
					__( 'Feb', 'somvio' ),
					__( 'Mar', 'somvio' ),
					__( 'Apr', 'somvio' ),
					__( 'May', 'somvio' ),
					__( 'Jun', 'somvio' ),
					__( 'Jul', 'somvio' ),
					__( 'Aug', 'somvio' ),
					__( 'Sep', 'somvio' ),
					__( 'Oct', 'somvio' ),
					__( 'Nov', 'somvio' ),
					__( 'Dec', 'somvio' ),
				),
				'weekdays'       => array(
					__( 'S', 'somvio' ),
					__( 'M', 'somvio' ),
					__( 'T', 'somvio' ),
					__( 'W', 'somvio' ),
					__( 'T', 'somvio' ),
					__( 'F', 'somvio' ),
					__( 'S', 'somvio' ),
				),
			),
			'privacyUrl' => esc_url_raw( $privacy_url ? (string) $privacy_url : home_url( '/privacy-policy/' ) ),
			'termsUrl'   => esc_url_raw( $terms_url ? (string) $terms_url : home_url( '/terms-of-use/' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_booking_form_assets' );
