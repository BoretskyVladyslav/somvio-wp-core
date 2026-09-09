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
require_once get_stylesheet_directory() . '/inc/booking/stripe-webhook.php';

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

	if ( in_array( $method, array( 'cash', 'pay_on_completion', 'local', 'later', 'bank', 'bank_transfer' ), true ) ) {
		return 'cash';
	}

	// Quotes default to cash/enquiry (no payment step).
	return 'cash';
}

/**
 * Process a validated submission.
 *
 * Card: PaymentIntent only — LatePoint is created on payment_intent.succeeded.
 * Cash / bank: LatePoint immediately with payment pending.
 *
 * @param array<string, mixed> $payload Sanitized payload with server `total`.
 * @return array<string, mixed>
 */
function somvio_process_booking_submission( array $payload ) {
	$payment_method = somvio_normalize_payment_method( $payload['payment_method'] ?? 'cash' );
	$source         = sanitize_key( (string) ( $payload['source'] ?? '' ) );
	$payload['payment_method'] = $payment_method;

	$result = array(
		'latepoint'  => null,
		'emails'     => null,
		'payment'    => null,
		'booking_id' => 0,
		'order_id'   => 0,
	);

	$is_card = ( 'online' === $payment_method && 'booking' === $source );

	if ( $is_card ) {
		if ( ! function_exists( 'somvio_stripe_is_configured' ) || ! somvio_stripe_is_configured() ) {
			$result['payment'] = array(
				'success' => false,
				'error'   => 'stripe_not_configured',
				'message' => __( 'Stripe API keys are missing. Cannot process online payment.', 'somvio' ),
			);
			return $result;
		}

		$payload['_payload_key'] = wp_generate_uuid4();
		$payload['charged_total'] = round( (float) ( $payload['total'] ?? 0 ), 2 );
		$payload['charged_total_cents'] = function_exists( 'somvio_stripe_to_cents' )
			? somvio_stripe_to_cents( $payload['charged_total'] )
			: (int) round( (float) $payload['charged_total'] * 100 );
		if ( $payload['charged_total_cents'] < 50 ) {
			$result['payment'] = array(
				'success' => false,
				'error'   => 'invalid_amount',
				'message' => __( 'Unable to charge this booking. Please contact us.', 'somvio' ),
			);
			return $result;
		}
		$stripe = somvio_stripe_create_payment_intent( (float) $payload['charged_total'], $payload );
		$result['payment'] = $stripe;

		if ( ! empty( $stripe['success'] ) && ! empty( $stripe['payment_intent_id'] ) ) {
			somvio_stripe_store_pending_payload( (string) $stripe['payment_intent_id'], $payload );
		}

		do_action( 'somvio_booking_processed', $payload, $result );

		return $result;
	}

	$latepoint = somvio_latepoint_create_booking( $payload );
	$result['latepoint'] = $latepoint;

	if ( ! empty( $latepoint['success'] ) ) {
		$result['booking_id']  = (int) ( $latepoint['booking_id'] ?? 0 );
		$result['order_id']    = (int) ( $latepoint['order_id'] ?? 0 );
		$payload['booking_id'] = $result['booking_id'];
		$payload['order_id']   = $result['order_id'];
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'Somvio LatePoint create failed: ' . wp_json_encode( $latepoint ) );
	}

	$result['emails'] = somvio_send_booking_notification_emails( $payload );

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
 * After client-side Stripe.js confirm: re-verify PI with Stripe and fulfill
 * (same path as the webhook — never trust the browser as sole authority).
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function somvio_rest_confirm_payment( WP_REST_Request $request ) {
	$payment_intent_id = sanitize_text_field( (string) $request['payment_intent_id'] );

	if ( '' === $payment_intent_id ) {
		return new WP_Error( 'invalid_intent', __( 'Missing payment intent.', 'somvio' ), array( 'status' => 400 ) );
	}

	$fulfill = somvio_stripe_fulfill_payment_intent( $payment_intent_id );
	if ( empty( $fulfill['success'] ) && 'locked' === ( $fulfill['error'] ?? '' ) ) {
		usleep( 600000 );
		$fulfill = somvio_stripe_fulfill_payment_intent( $payment_intent_id );
	}
	if ( empty( $fulfill['success'] ) ) {
		$error = (string) ( $fulfill['error'] ?? '' );

		if ( 'locked' === $error ) {
			return new WP_Error(
				'pending_fulfillment',
				__( 'Payment succeeded. Booking confirmation is still in progress.', 'somvio' ),
				array(
					'status' => 503,
					'detail' => $error,
				)
			);
		}

		return new WP_Error(
			'payment_unverified',
			__( 'Payment could not be verified.', 'somvio' ),
			array(
				'status' => 402,
				'detail' => $error,
			)
		);
	}

	return rest_ensure_response(
		array(
			'success'    => true,
			'booking_id' => (int) ( $fulfill['booking_id'] ?? 0 ),
			'order_id'   => (int) ( $fulfill['order_id'] ?? 0 ),
			'status'     => 'succeeded',
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
					'required'          => false,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'somvio_register_booking_payment_rest_routes' );
