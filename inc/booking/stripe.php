<?php
/**
 * Stripe Payment Intent helpers for Somvio booking.
 *
 * Keys via constants, options, or filters — never hardcode secrets in the theme.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe secret key (server only).
 *
 * @return string
 */
function somvio_get_stripe_secret_key() {
	$key = '';

	if ( defined( 'SOMVIO_STRIPE_SECRET_KEY' ) && SOMVIO_STRIPE_SECRET_KEY ) {
		$key = (string) SOMVIO_STRIPE_SECRET_KEY;
	} else {
		$key = (string) get_option( 'somvio_stripe_secret_key', '' );
	}

	/**
	 * Filter Stripe secret key.
	 *
	 * @param string $key Secret key.
	 */
	return trim( (string) apply_filters( 'somvio_stripe_secret_key', $key ) );
}

/**
 * Stripe publishable key (safe for client).
 *
 * @return string
 */
function somvio_get_stripe_publishable_key() {
	$key = '';

	if ( defined( 'SOMVIO_STRIPE_PUBLISHABLE_KEY' ) && SOMVIO_STRIPE_PUBLISHABLE_KEY ) {
		$key = (string) SOMVIO_STRIPE_PUBLISHABLE_KEY;
	} else {
		$key = (string) get_option( 'somvio_stripe_publishable_key', '' );
	}

	/**
	 * Filter Stripe publishable key.
	 *
	 * @param string $key Publishable key.
	 */
	return trim( (string) apply_filters( 'somvio_stripe_publishable_key', $key ) );
}

/**
 * Whether Stripe is configured for PaymentIntent creation.
 *
 * @return bool
 */
function somvio_stripe_is_configured() {
	$secret = somvio_get_stripe_secret_key();
	$pub    = somvio_get_stripe_publishable_key();

	return ( '' !== $secret && '' !== $pub );
}

/**
 * Create a Stripe PaymentIntent for a booking total (GBP).
 *
 * @param float                $amount  Amount in major units (e.g. 75.00).
 * @param array<string, mixed> $payload Booking payload / metadata.
 * @return array{success:bool,client_secret?:string,payment_intent_id?:string,publishable_key?:string,error?:string}
 */
function somvio_stripe_create_payment_intent( $amount, array $payload = array() ) {
	$amount = round( (float) $amount, 2 );
	if ( $amount < 0.5 ) {
		return array(
			'success' => false,
			'error'   => 'invalid_amount',
		);
	}

	$secret = somvio_get_stripe_secret_key();
	$pub    = somvio_get_stripe_publishable_key();

	if ( '' === $secret || '' === $pub ) {
		return array(
			'success' => false,
			'error'   => 'stripe_not_configured',
		);
	}

	$amount_minor = (int) round( $amount * 100 );
	$currency     = 'gbp';

	$meta = array(
		'source'      => sanitize_key( (string) ( $payload['source'] ?? 'booking' ) ),
		'service'     => sanitize_key( (string) ( $payload['service'] ?? '' ) ),
		'customer'    => sanitize_text_field( (string) ( $payload['name'] ?? '' ) ),
		'email'       => sanitize_email( (string) ( $payload['email'] ?? '' ) ),
		'date'        => sanitize_text_field( (string) ( $payload['date'] ?? '' ) ),
		'time'        => sanitize_text_field( (string) ( $payload['time'] ?? '' ) ),
		'booking_id'  => isset( $payload['booking_id'] ) ? (string) absint( $payload['booking_id'] ) : '',
		'order_id'    => isset( $payload['order_id'] ) ? (string) absint( $payload['order_id'] ) : '',
	);

	$body = array(
		'amount'               => $amount_minor,
		'currency'             => $currency,
		'automatic_payment_methods[enabled]' => 'true',
		'receipt_email'        => $meta['email'],
		'description'          => sprintf(
			'Somvio booking — %s on %s %s',
			$meta['service'],
			$meta['date'],
			$meta['time']
		),
	);

	foreach ( $meta as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}
		$body[ 'metadata[' . $key . ']' ] = substr( $value, 0, 500 );
	}

	$response = wp_remote_post(
		'https://api.stripe.com/v1/payment_intents',
		array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $secret,
			),
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'success' => false,
			'error'   => $response->get_error_message(),
		);
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
		$message = is_array( $data ) && isset( $data['error']['message'] )
			? (string) $data['error']['message']
			: 'stripe_request_failed';

		return array(
			'success' => false,
			'error'   => $message,
		);
	}

	$client_secret = isset( $data['client_secret'] ) ? (string) $data['client_secret'] : '';
	$intent_id     = isset( $data['id'] ) ? (string) $data['id'] : '';

	if ( '' === $client_secret || '' === $intent_id ) {
		return array(
			'success' => false,
			'error'   => 'missing_client_secret',
		);
	}

	return array(
		'success'            => true,
		'client_secret'      => $client_secret,
		'payment_intent_id'  => $intent_id,
		'publishable_key'    => $pub,
		'amount'             => $amount,
		'currency'           => $currency,
	);
}

/**
 * Retrieve a PaymentIntent and verify it succeeded for the expected amount.
 *
 * @param string $payment_intent_id Intent ID.
 * @param float  $expected_amount   Expected major-unit amount.
 * @return array{success:bool,status?:string,error?:string}
 */
function somvio_stripe_verify_payment_intent( $payment_intent_id, $expected_amount ) {
	$payment_intent_id = sanitize_text_field( (string) $payment_intent_id );
	$secret            = somvio_get_stripe_secret_key();

	if ( '' === $payment_intent_id || '' === $secret ) {
		return array(
			'success' => false,
			'error'   => 'invalid_request',
		);
	}

	$response = wp_remote_get(
		'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $payment_intent_id ),
		array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $secret,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'success' => false,
			'error'   => $response->get_error_message(),
		);
	}

	$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['id'] ) ) {
		return array(
			'success' => false,
			'error'   => 'stripe_retrieve_failed',
		);
	}

	$status = isset( $data['status'] ) ? (string) $data['status'] : '';
	if ( ! in_array( $status, array( 'succeeded', 'processing', 'requires_capture' ), true ) ) {
		return array(
			'success' => false,
			'status'  => $status,
			'error'   => 'payment_not_complete',
		);
	}

	$paid_minor = isset( $data['amount_received'] ) ? (int) $data['amount_received'] : (int) ( $data['amount'] ?? 0 );
	$expected_minor = (int) round( (float) $expected_amount * 100 );
	if ( $expected_minor > 0 && abs( $paid_minor - $expected_minor ) > 1 ) {
		return array(
			'success' => false,
			'error'   => 'amount_mismatch',
		);
	}

	return array(
		'success' => true,
		'status'  => $status,
	);
}
