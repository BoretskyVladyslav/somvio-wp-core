<?php
/**
 * Stripe webhooks: create LatePoint bookings only after payment_intent.succeeded.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recalculate server total from stored payload (never trust client total).
 *
 * @param array<string, mixed> $payload Payload.
 * @return float
 */
function somvio_stripe_recalculate_payload_total( array $payload ) {
	if ( ! function_exists( 'somvio_calculate_quote_price' ) ) {
		return round( (float) ( $payload['total'] ?? 0 ), 2 );
	}

	return round(
		(float) somvio_calculate_quote_price(
			(string) ( $payload['service'] ?? '' ),
			(string) ( $payload['property'] ?? 'house' ),
			(int) ( $payload['bedrooms'] ?? 1 ),
			(int) ( $payload['bathrooms'] ?? 1 ),
			isset( $payload['addons'] ) ? (array) $payload['addons'] : array(),
			isset( $payload['addon_quantities'] ) ? (array) $payload['addon_quantities'] : array(),
			(int) ( $payload['linen_changes'] ?? 0 ),
			(int) ( $payload['main_rooms'] ?? 0 )
		),
		2
	);
}

/**
 * Create LatePoint booking + emails for a succeeded PaymentIntent (idempotent).
 *
 * @param string $payment_intent_id Intent ID.
 * @return array<string, mixed>
 */
function somvio_stripe_fulfill_payment_intent( $payment_intent_id ) {
	$payment_intent_id = sanitize_text_field( (string) $payment_intent_id );
	if ( '' === $payment_intent_id ) {
		return array(
			'success' => false,
			'error'   => 'missing_intent',
		);
	}

	$existing = somvio_stripe_get_booking_for_intent( $payment_intent_id );
	if ( $existing > 0 ) {
		return array(
			'success'    => true,
			'booking_id' => $existing,
			'idempotent' => true,
		);
	}

	$lock_key = somvio_stripe_intent_key( $payment_intent_id, 'lock' );
	if ( get_transient( $lock_key ) ) {
		usleep( 500000 );
		$existing = somvio_stripe_get_booking_for_intent( $payment_intent_id );
		return $existing > 0
			? array(
				'success'    => true,
				'booking_id' => $existing,
				'idempotent' => true,
			)
			: array(
				'success' => false,
				'error'   => 'locked',
			);
	}
	set_transient( $lock_key, 1, 30 );

	$payload = somvio_stripe_get_pending_payload( $payment_intent_id );
	if ( ! is_array( $payload ) ) {
		delete_transient( $lock_key );
		return array(
			'success' => false,
			'error'   => 'payload_missing',
		);
	}

	$server_total = somvio_stripe_recalculate_payload_total( $payload );
	$payload['total'] = $server_total;
	$payload['payment_method'] = 'online';

	$verify = somvio_stripe_verify_payment_intent( $payment_intent_id, $server_total );
	if ( empty( $verify['success'] ) || 'succeeded' !== ( $verify['status'] ?? '' ) ) {
		delete_transient( $lock_key );
		return array(
			'success' => false,
			'error'   => $verify['error'] ?? 'payment_not_complete',
			'status'  => $verify['status'] ?? '',
		);
	}

	$latepoint = somvio_latepoint_create_booking( $payload );
	if ( empty( $latepoint['success'] ) ) {
		delete_transient( $lock_key );
		return array(
			'success' => false,
			'error'   => $latepoint['error'] ?? 'latepoint_failed',
		);
	}

	$booking_id = (int) ( $latepoint['booking_id'] ?? 0 );
	somvio_stripe_set_booking_for_intent( $payment_intent_id, $booking_id );
	somvio_stripe_delete_pending_payload( $payment_intent_id );

	if ( $booking_id > 0 ) {
		somvio_latepoint_mark_booking_paid( $booking_id );
		if ( class_exists( 'OsBookingModel' ) ) {
			$booking = new OsBookingModel( $booking_id );
			if ( ! empty( $booking->id ) && method_exists( $booking, 'save_meta_by_key' ) ) {
				$booking->save_meta_by_key( 'somvio_stripe_payment_intent', $payment_intent_id );
			}
		}
	}

	$payload['booking_id'] = $booking_id;
	$payload['order_id']   = (int) ( $latepoint['order_id'] ?? 0 );
	$emails = function_exists( 'somvio_send_booking_notification_emails' )
		? somvio_send_booking_notification_emails( $payload )
		: null;

	delete_transient( $lock_key );

	return array(
		'success'    => true,
		'booking_id' => $booking_id,
		'order_id'   => (int) ( $latepoint['order_id'] ?? 0 ),
		'latepoint'  => $latepoint,
		'emails'     => $emails,
	);
}

/**
 * Discard pending payload when payment fails or is cancelled — no LatePoint row.
 *
 * @param string $payment_intent_id Intent ID.
 * @return void
 */
function somvio_stripe_abandon_payment_intent( $payment_intent_id ) {
	$payment_intent_id = sanitize_text_field( (string) $payment_intent_id );
	if ( '' === $payment_intent_id ) {
		return;
	}

	if ( somvio_stripe_get_booking_for_intent( $payment_intent_id ) > 0 ) {
		return;
	}

	somvio_stripe_delete_pending_payload( $payment_intent_id );
}

/**
 * REST: Stripe webhook (signature is the permission check).
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function somvio_rest_can_stripe_webhook( WP_REST_Request $request ) {
	$sig = $request->get_header( 'stripe-signature' );
	if ( ! $sig ) {
		$sig = $request->get_header( 'Stripe-Signature' );
	}

	if ( ! somvio_stripe_verify_webhook_signature( $request->get_body(), (string) $sig ) ) {
		return new WP_Error( 'invalid_signature', __( 'Invalid Stripe signature.', 'somvio' ), array( 'status' => 401 ) );
	}

	return true;
}

/**
 * Handle Stripe webhook events.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function somvio_rest_stripe_webhook( WP_REST_Request $request ) {
	$event = json_decode( $request->get_body(), true );
	if ( ! is_array( $event ) || empty( $event['type'] ) ) {
		return new WP_Error( 'invalid_event', __( 'Invalid event.', 'somvio' ), array( 'status' => 400 ) );
	}

	$type     = sanitize_text_field( (string) $event['type'] );
	$event_id = sanitize_text_field( (string) ( $event['id'] ?? '' ) );
	$object   = isset( $event['data']['object'] ) && is_array( $event['data']['object'] )
		? $event['data']['object']
		: array();
	$pi_id    = sanitize_text_field( (string) ( $object['id'] ?? '' ) );
	$seen_key = '' !== $event_id ? 'somvio_stripe_evt_' . md5( $event_id ) : '';

	if ( '' !== $seen_key && get_transient( $seen_key ) ) {
		return rest_ensure_response(
			array(
				'received'   => true,
				'idempotent' => true,
			)
		);
	}

	if ( 'payment_intent.succeeded' === $type ) {
		if ( '' === $pi_id ) {
			return new WP_Error( 'missing_intent', __( 'Missing payment intent.', 'somvio' ), array( 'status' => 400 ) );
		}
		$result = somvio_stripe_fulfill_payment_intent( $pi_id );
		if ( empty( $result['success'] ) ) {
			return new WP_Error(
				'fulfill_failed',
				__( 'Payment succeeded but booking could not be created.', 'somvio' ),
				array(
					'status' => 500,
					'detail' => $result['error'] ?? '',
				)
			);
		}

		if ( '' !== $seen_key ) {
			set_transient( $seen_key, 1, WEEK_IN_SECONDS );
		}

		return rest_ensure_response(
			array(
				'received'   => true,
				'booking_id' => (int) ( $result['booking_id'] ?? 0 ),
			)
		);
	}

	if ( in_array( $type, array( 'payment_intent.payment_failed', 'payment_intent.canceled' ), true ) ) {
		somvio_stripe_abandon_payment_intent( $pi_id );
		if ( '' !== $seen_key ) {
			set_transient( $seen_key, 1, WEEK_IN_SECONDS );
		}
		return rest_ensure_response(
			array(
				'received'  => true,
				'abandoned' => true,
			)
		);
	}

	if ( '' !== $seen_key ) {
		set_transient( $seen_key, 1, WEEK_IN_SECONDS );
	}

	return rest_ensure_response(
		array(
			'received' => true,
			'ignored'  => $type,
		)
	);
}

/**
 * Register Stripe webhook REST route.
 *
 * @return void
 */
function somvio_register_stripe_webhook_rest_route() {
	register_rest_route(
		'somvio/v1',
		'/stripe/webhook',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'somvio_rest_stripe_webhook',
			'permission_callback' => 'somvio_rest_can_stripe_webhook',
		)
	);
}
add_action( 'rest_api_init', 'somvio_register_stripe_webhook_rest_route' );
