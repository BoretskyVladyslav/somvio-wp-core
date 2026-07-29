<?php
/**
 * Orchestrate LatePoint + emails + Stripe after quote/booking validation.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/booking/emails.php';
require_once get_stylesheet_directory() . '/inc/booking/latepoint.php';
require_once get_stylesheet_directory() . '/inc/booking/latepoint-seed.php';
require_once get_stylesheet_directory() . '/inc/booking/stripe.php';

/**
 * Normalize payment method from request.
 *
 * @param string $method Raw method.
 * @return string cash|online
 */
function somvio_normalize_payment_method( $method ) {
	$method = sanitize_key( (string) $method );

	if ( in_array( $method, array( 'online', 'stripe', 'card' ), true ) ) {
		return 'online';
	}

	if ( in_array( $method, array( 'cash', 'pay_on_completion', 'local', 'later' ), true ) ) {
		return 'cash';
	}

	// Quotes default to cash/enquiry (no payment step).
	return 'cash';
}

/**
 * Process a validated submission: LatePoint, emails, optional Stripe PI.
 *
 * @param array<string, mixed> $payload Sanitized payload with server `total`.
 * @return array<string, mixed>
 */
function somvio_process_booking_submission( array $payload ) {
	$payment_method = somvio_normalize_payment_method( $payload['payment_method'] ?? 'cash' );

	// REST layer must reject online without keys; never silently downgrade to cash here.
	if ( 'online' === $payment_method && function_exists( 'somvio_stripe_is_configured' ) && ! somvio_stripe_is_configured() ) {
		$payment_method = 'cash';
	}

	$payload['payment_method'] = $payment_method;

	$result = array(
		'latepoint' => null,
		'emails'    => null,
		'payment'   => null,
		'booking_id'=> 0,
		'order_id'  => 0,
	);

	$latepoint = somvio_latepoint_create_booking( $payload );
	$result['latepoint'] = $latepoint;

	if ( ! empty( $latepoint['success'] ) ) {
		$result['booking_id'] = (int) ( $latepoint['booking_id'] ?? 0 );
		$result['order_id']   = (int) ( $latepoint['order_id'] ?? 0 );
		$payload['booking_id'] = $result['booking_id'];
		$payload['order_id']   = $result['order_id'];
	} else {
		// Log but do not fail the whole request — emails should still go out.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Somvio LatePoint create failed: ' . wp_json_encode( $latepoint ) );
		}
	}

	$result['emails'] = somvio_send_booking_notification_emails( $payload );

	if ( 'online' === $payment_method && 'booking' === ( $payload['source'] ?? '' ) ) {
		if ( $result['booking_id'] > 0 ) {
			$stripe = somvio_stripe_create_payment_intent( (float) ( $payload['total'] ?? 0 ), $payload );
			$result['payment'] = $stripe;

			if ( ! empty( $stripe['success'] ) && class_exists( 'OsBookingModel' ) ) {
				$booking = new OsBookingModel( $result['booking_id'] );
				if ( ! empty( $booking->id ) && method_exists( $booking, 'save_meta_by_key' ) ) {
					$booking->save_meta_by_key( 'somvio_stripe_payment_intent', (string) ( $stripe['payment_intent_id'] ?? '' ) );
				}
			}
		} else {
			// Never create an orphan PaymentIntent without a LatePoint booking to bind it to.
			$result['payment'] = array(
				'success' => false,
				'error'   => 'missing_booking',
				'message' => __( 'Booking was received but online payment could not be started. Please contact us or choose pay on completion.', 'somvio' ),
			);
		}
	}

	/**
	 * After full booking processing.
	 *
	 * @param array<string, mixed> $payload Payload.
	 * @param array<string, mixed> $result  Processing result.
	 */
	do_action( 'somvio_booking_processed', $payload, $result );

	return $result;
}

/**
 * Hook: process after REST validation (legacy listeners).
 *
 * @param array<string, mixed> $payload Payload.
 * @return void
 */
function somvio_on_quote_submitted( $payload ) {
	if ( ! is_array( $payload ) ) {
		return;
	}

	// REST handler calls somvio_process_booking_submission directly and sets this flag
	// to avoid double-processing when do_action still fires.
	if ( ! empty( $payload['_processed'] ) ) {
		return;
	}

	somvio_process_booking_submission( $payload );
}
add_action( 'somvio_quote_submitted', 'somvio_on_quote_submitted', 10, 1 );

/**
 * Confirm Stripe payment after client-side confirmation.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function somvio_rest_confirm_payment( WP_REST_Request $request ) {
	$payment_intent_id = sanitize_text_field( (string) $request['payment_intent_id'] );
	$booking_id        = absint( $request['booking_id'] ?? 0 );

	if ( '' === $payment_intent_id ) {
		return new WP_Error( 'invalid_intent', __( 'Missing payment intent.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( $booking_id < 1 ) {
		return new WP_Error( 'invalid_booking', __( 'Missing booking ID.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( ! class_exists( 'OsBookingModel' ) ) {
		return new WP_Error( 'booking_unavailable', __( 'Booking system is unavailable.', 'somvio' ), array( 'status' => 503 ) );
	}

	$booking = new OsBookingModel( $booking_id );
	if ( empty( $booking->id ) || ! method_exists( $booking, 'get_meta_by_key' ) ) {
		return new WP_Error( 'booking_not_found', __( 'Booking not found.', 'somvio' ), array( 'status' => 404 ) );
	}

	$stored_intent = (string) $booking->get_meta_by_key( 'somvio_stripe_payment_intent', '' );
	if ( '' === $stored_intent || $stored_intent !== $payment_intent_id ) {
		return new WP_Error( 'intent_mismatch', __( 'Payment intent does not match this booking.', 'somvio' ), array( 'status' => 400 ) );
	}

	$stored_total = $booking->get_meta_by_key( 'somvio_server_total', '' );
	if ( '' === $stored_total ) {
		return new WP_Error( 'missing_total', __( 'Booking total could not be verified.', 'somvio' ), array( 'status' => 400 ) );
	}

	$expected = (float) $stored_total;
	$verify   = somvio_stripe_verify_payment_intent( $payment_intent_id, $expected );
	if ( empty( $verify['success'] ) ) {
		return new WP_Error(
			'payment_unverified',
			__( 'Payment could not be verified.', 'somvio' ),
			array(
				'status' => 402,
				'detail' => $verify['error'] ?? '',
			)
		);
	}

	somvio_latepoint_mark_booking_paid( $booking_id );

	return rest_ensure_response(
		array(
			'success'    => true,
			'booking_id' => $booking_id,
			'status'     => $verify['status'] ?? 'succeeded',
			'message'    => __( 'Payment confirmed. Your booking is confirmed.', 'somvio' ),
		)
	);
}

/**
 * Register payment confirm REST route.
 *
 * @return void
 */
function somvio_register_booking_payment_rest_routes() {
	register_rest_route(
		'somvio/v1',
		'/booking/confirm-payment',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'somvio_rest_confirm_payment',
			'permission_callback' => 'somvio_rest_can_submit_quote',
			'args'                => array(
				'payment_intent_id' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'booking_id'        => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'somvio_register_booking_payment_rest_routes' );
