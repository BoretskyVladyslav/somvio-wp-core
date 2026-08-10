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

	if ( ! somvio_enqueue_theme_script( 'somvio-booking-form', 'assets/js/booking-form.js' ) ) {
		return;
	}

	$privacy_url = function_exists( 'somvio_get_privacy_policy_page_id' )
		? get_permalink( somvio_get_privacy_policy_page_id() )
		: home_url( '/privacy-policy/' );
	$terms_id    = function_exists( 'somvio_get_terms_conditions_page_id' )
		? somvio_get_terms_conditions_page_id()
		: ( function_exists( 'somvio_get_page_id_by_slug' ) ? somvio_get_page_id_by_slug( 'terms-conditions' ) : 0 );
	$terms_url   = $terms_id > 0 ? get_permalink( $terms_id ) : home_url( '/terms-conditions/' );

	wp_localize_script(
		'somvio-booking-form',
		'somvioBookingForm',
		array(
			'restUrl' => esc_url_raw( rest_url( 'somvio/v1/quote/submit' ) ),
			'confirmPaymentUrl' => esc_url_raw( rest_url( 'somvio/v1/booking/confirm-payment' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'rates'   => function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array(),
			'services'=> function_exists( 'somvio_get_quote_service_options' ) ? somvio_get_quote_service_options() : array(),
			'stripePublishableKey' => function_exists( 'somvio_get_stripe_publishable_key' )
				? somvio_get_stripe_publishable_key()
				: '',
			'stripeEnabled' => function_exists( 'somvio_stripe_is_configured' ) && somvio_stripe_is_configured(),
			'i18n'    => array(
				'stepOf'                => __( 'Step {current} of {total}', 'somvio' ),
				'selectDatePlaceholder' => __( 'Select date', 'somvio' ),
				'selectTime'            => __( 'Please select a time slot.', 'somvio' ),
				'selectDate'            => __( 'Please select a valid date.', 'somvio' ),
				'selectDateTime'        => __( 'Select a date and time to continue', 'somvio' ),
				'selectService'         => __( 'Please select a service.', 'somvio' ),
				'bedroomHome'           => __( '%d Bedroom Home', 'somvio' ),
				'mainRooms'             => __( 'Main rooms', 'somvio' ),
				'roomsLivingBedDining'  => __( 'Rooms (Living, Bed, Dining)', 'somvio' ),
				'bedrooms'              => __( 'Bedrooms', 'somvio' ),
				'bathrooms'             => __( 'Bathrooms', 'somvio' ),
				'bathroomsAndShowers'   => __( 'Bathrooms And Shower Rooms', 'somvio' ),
				'toilets'               => __( 'Toilets (without Baths/showers)', 'somvio' ),
				'kitchens'              => __( 'Kitchens', 'somvio' ),
				'noOfBedrooms'          => __( 'No. of Bedrooms', 'somvio' ),
				'noOfBathrooms'         => __( 'No. of Bathrooms', 'somvio' ),
				'linenChanges'          => __( 'No. of Linen Changes', 'somvio' ),
				'frequency'             => __( 'Frequency', 'somvio' ),
				'frequencyWeekly'       => __( 'Weekly', 'somvio' ),
				'frequencyFortnightly'  => __( 'Fortnightly', 'somvio' ),
				'nextStep'              => __( 'Next Step', 'somvio' ),
				'back'                  => __( 'Back', 'somvio' ),
				'complete'              => __( 'Complete Booking', 'somvio' ),
				'submitting'            => __( 'Submitting…', 'somvio' ),
				'required'              => __( 'Please complete the required fields.', 'somvio' ),
				'invalidEmail'          => __( 'Please enter a valid email address.', 'somvio' ),
				'invalidPhone'          => __( 'Please enter a valid phone number.', 'somvio' ),
				'invalidName'           => __( 'Please enter your name.', 'somvio' ),
				'invalidAddress'        => __( 'Please enter your street address.', 'somvio' ),
				'termsRequired'         => __( 'You must accept the Terms & Conditions and Privacy Policy to complete your booking.', 'somvio' ),
				'selectPayment'         => __( 'Please select a payment method.', 'somvio' ),
				'onlinePaymentUnavailable' => __( 'Stripe API keys are missing. Cannot process online payment.', 'somvio' ),
				'stripeKeysMissing'     => __( 'Stripe API keys are missing. Cannot process online payment.', 'somvio' ),
				'completeContact'       => __( 'Complete the required fields to continue', 'somvio' ),
				'requiredField'         => __( 'This field is required.', 'somvio' ),
				'submitError'           => __( 'Something went wrong. Please try again.', 'somvio' ),
				'paymentError'          => __( 'Payment could not be completed. Please try again.', 'somvio' ),
				'paymentUnavailable'    => __( 'Booking was received but online payment could not be started. Please contact us or choose pay on completion.', 'somvio' ),
				'paymentUnconfirmed'    => __( 'Payment was not confirmed. Please try again or contact us.', 'somvio' ),
				'paymentSuccess'        => __( 'Payment successful — your booking is confirmed.', 'somvio' ),
				'paying'                => __( 'Processing payment…', 'somvio' ),
				'backHome'              => __( 'Back to Home', 'somvio' ),
				'estimatedTotal'        => __( 'Estimated total', 'somvio' ),
				'totalPrice'            => __( 'Total Price', 'somvio' ),
				'none'                  => __( 'None', 'somvio' ),
				'notSelected'           => __( 'Not selected', 'somvio' ),
				'bedroomsCount'         => __( '%d Bedrooms', 'somvio' ),
				'bathroomsCount'        => __( '%d Bathrooms', 'somvio' ),
				'bathroomsAndShowersCount' => __( '%d Bathrooms And Shower Rooms', 'somvio' ),
				'mainRoomsCount'        => __( '%d Main rooms', 'somvio' ),
				'roomsLivingBedDiningCount' => __( '%d Rooms (Living, Bed, Dining)', 'somvio' ),
				'toiletsCount'          => __( '%d Toilets (without Baths/showers)', 'somvio' ),
				'kitchensCount'         => __( '%d Kitchens', 'somvio' ),
				'linenChangesCount'     => __( '%d Linen changes', 'somvio' ),
				'frequencyWeeklyLabel'  => __( 'Weekly', 'somvio' ),
				'frequencyFortnightlyLabel' => __( 'Fortnightly', 'somvio' ),
				'welcomePack'           => __( 'Welcome pack', 'somvio' ),
				'extraServices'         => __( 'Extra Services', 'somvio' ),
				'accessMethod'          => __( 'How will we get in?', 'somvio' ),
				'selectAccess'          => __( 'Please select how we will get in.', 'somvio' ),
				'addonQty'              => __( '%1$s (x%2$d)', 'somvio' ),
				'selectOption'          => __( 'Select an option', 'somvio' ),
				'accessOptions'         => function_exists( 'somvio_get_access_method_options' )
					? somvio_get_access_method_options()
					: array(),
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
			'termsUrl'   => esc_url_raw( $terms_url ? (string) $terms_url : home_url( '/terms-conditions/' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_booking_form_assets' );
