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
 * Stripe webhook signing secret (whsec_…).
 *
 * @return string
 */
function somvio_get_stripe_webhook_secret() {
	$key = '';

	if ( defined( 'SOMVIO_STRIPE_WEBHOOK_SECRET' ) && SOMVIO_STRIPE_WEBHOOK_SECRET ) {
		$key = (string) SOMVIO_STRIPE_WEBHOOK_SECRET;
	} else {
		$key = (string) get_option( 'somvio_stripe_webhook_secret', '' );
	}

	/**
	 * Filter Stripe webhook signing secret.
	 *
	 * @param string $key Secret.
	 */
	return trim( (string) apply_filters( 'somvio_stripe_webhook_secret', $key ) );
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
 * GBP major units → integer pence.
 *
 * @param mixed $amount Major units.
 * @return int
 */
function somvio_stripe_to_cents( $amount ) {
	if ( function_exists( 'somvio_money_to_cents' ) ) {
		return somvio_money_to_cents( $amount );
	}

	if ( ! is_numeric( $amount ) ) {
		return 0;
	}

	return (int) round( (float) $amount * 100 );
}

/**
 * Acquire an atomic fulfill lock (add_option UNIQUE). Stale locks > 120s are replaced.
 * Never deletes the booking slot — a live 'pending' claim must not be wiped.
 *
 * @param string $payment_intent_id Intent ID.
 * @return bool
 */
function somvio_stripe_acquire_fulfill_lock( $payment_intent_id ) {
	$key = somvio_stripe_intent_key( $payment_intent_id, 'flock' );
	$now = time();

	if ( add_option( $key, $now, '', false ) ) {
		return true;
	}

	$held = absint( get_option( $key, 0 ) );
	if ( $held > 0 && ( $now - $held ) < 120 ) {
		return false;
	}

	delete_option( $key );

	return add_option( $key, $now, '', false );
}

/**
 * Release fulfill lock.
 *
 * @param string $payment_intent_id Intent ID.
 * @return void
 */
function somvio_stripe_release_fulfill_lock( $payment_intent_id ) {
	delete_option( somvio_stripe_intent_key( $payment_intent_id, 'flock' ) );
}

/**
 * Whether confirmation emails were already sent for this PaymentIntent.
 *
 * @param string $payment_intent_id Intent ID.
 * @return bool
 */
function somvio_stripe_emails_sent_for_intent( $payment_intent_id ) {
	return (bool) get_option( somvio_stripe_intent_key( $payment_intent_id, 'emails' ), false );
}

/**
 * Atomically claim the email-send slot for this PaymentIntent.
 *
 * @param string $payment_intent_id Intent ID.
 * @return bool True if this caller should send.
 */
function somvio_stripe_claim_email_send( $payment_intent_id ) {
	return add_option( somvio_stripe_intent_key( $payment_intent_id, 'emails' ), 1, '', false );
}

/**
 * Mark confirmation emails as sent for this PaymentIntent.
 *
 * @param string $payment_intent_id Intent ID.
 * @return void
 */
function somvio_stripe_mark_emails_sent( $payment_intent_id ) {
	update_option( somvio_stripe_intent_key( $payment_intent_id, 'emails' ), 1, false );
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

	$amount_minor = somvio_stripe_to_cents( $amount );
	if ( $amount_minor < 50 ) {
		return array(
			'success' => false,
			'error'   => 'invalid_amount',
		);
	}

	$currency = 'gbp';

	$meta = array(
		'source'     => sanitize_key( (string) ( $payload['source'] ?? 'booking' ) ),
		'service'    => sanitize_key( (string) ( $payload['service'] ?? '' ) ),
		'customer'   => sanitize_text_field( (string) ( $payload['name'] ?? '' ) ),
		'email'      => sanitize_email( (string) ( $payload['email'] ?? '' ) ),
		'date'       => sanitize_text_field( (string) ( $payload['date'] ?? '' ) ),
		'time'       => sanitize_text_field( (string) ( $payload['time'] ?? '' ) ),
		'payload_key'=> sanitize_text_field( (string) ( $payload['_payload_key'] ?? '' ) ),
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
 * Retrieve a PaymentIntent and verify it succeeded for the expected integer pence.
 *
 * @param string $payment_intent_id Intent ID.
 * @param int    $expected_cents    Frozen charged_total_cents from pending payload.
 * @return array{success:bool,status?:string,error?:string}
 */
function somvio_stripe_verify_payment_intent( $payment_intent_id, $expected_cents ) {
	$payment_intent_id = sanitize_text_field( (string) $payment_intent_id );
	$expected_cents    = absint( $expected_cents );
	$secret            = somvio_get_stripe_secret_key();

	if ( '' === $payment_intent_id || '' === $secret ) {
		return array(
			'success' => false,
			'error'   => 'invalid_request',
		);
	}

	if ( $expected_cents < 50 ) {
		return array(
			'success' => false,
			'error'   => 'expected_amount_missing',
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
	if ( 'succeeded' !== $status ) {
		return array(
			'success' => false,
			'status'  => $status,
			'error'   => 'payment_not_complete',
		);
	}

	$currency = strtolower( (string) ( $data['currency'] ?? '' ) );
	if ( 'gbp' !== $currency ) {
		return array(
			'success' => false,
			'status'  => $status,
			'error'   => 'currency_mismatch',
		);
	}

	$paid_minor = isset( $data['amount_received'] ) ? (int) $data['amount_received'] : (int) ( $data['amount'] ?? 0 );
	if ( $paid_minor !== $expected_cents ) {
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

/**
 * Option key for a PaymentIntent-scoped value.
 *
 * @param string $payment_intent_id Intent ID.
 * @param string $suffix            Suffix (pending|booking).
 * @return string
 */
function somvio_stripe_intent_key( $payment_intent_id, $suffix ) {
	$id = sanitize_text_field( (string) $payment_intent_id );
	return 'somvio_stripe_' . sanitize_key( $suffix ) . '_' . md5( $id );
}

/**
 * Pending payload TTL (Stripe retry window).
 *
 * @return int
 */
function somvio_stripe_pending_payload_ttl() {
	return 72 * HOUR_IN_SECONDS;
}

/**
 * Unwrap a stored pending record (option wrapper or legacy raw payload).
 *
 * @param mixed $data Option / transient value.
 * @return array<string, mixed>|null
 */
function somvio_stripe_unwrap_pending_record( $data ) {
	if ( ! is_array( $data ) ) {
		return null;
	}

	if ( isset( $data['expires_at'] ) ) {
		if ( (int) $data['expires_at'] < time() ) {
			return null;
		}
		return isset( $data['payload'] ) && is_array( $data['payload'] ) ? $data['payload'] : null;
	}

	if ( isset( $data['charged_total_cents'] ) || isset( $data['total'] ) || isset( $data['service'] ) ) {
		return $data;
	}

	return null;
}

/**
 * Store sanitized booking payload until payment succeeds or expires.
 *
 * @param string               $payment_intent_id Intent ID.
 * @param array<string, mixed> $payload           Payload with server total.
 * @return void
 */
function somvio_stripe_store_pending_payload( $payment_intent_id, array $payload ) {
	$key    = somvio_stripe_intent_key( $payment_intent_id, 'pending' );
	$record = array(
		'payload'    => $payload,
		'expires_at' => time() + somvio_stripe_pending_payload_ttl(),
	);

	if ( ! add_option( $key, $record, '', false ) ) {
		update_option( $key, $record, false );
	}

	delete_transient( $key );
}

/**
 * Retrieve pending payload for an intent.
 *
 * @param string $payment_intent_id Intent ID.
 * @return array<string, mixed>|null
 */
function somvio_stripe_get_pending_payload( $payment_intent_id ) {
	$key  = somvio_stripe_intent_key( $payment_intent_id, 'pending' );
	$data = get_option( $key, false );

	if ( is_array( $data ) ) {
		if ( isset( $data['expires_at'] ) && (int) $data['expires_at'] < time() ) {
			delete_option( $key );
			delete_transient( $key );
			return null;
		}

		$payload = somvio_stripe_unwrap_pending_record( $data );
		if ( is_array( $payload ) ) {
			return $payload;
		}
	}

	$legacy = get_transient( $key );
	$payload = somvio_stripe_unwrap_pending_record( $legacy );
	return is_array( $payload ) ? $payload : null;
}

/**
 * Drop pending payload (cancelled / fulfilled).
 *
 * @param string $payment_intent_id Intent ID.
 * @return void
 */
function somvio_stripe_delete_pending_payload( $payment_intent_id ) {
	$key = somvio_stripe_intent_key( $payment_intent_id, 'pending' );
	delete_option( $key );
	delete_transient( $key );
}

/**
 * Idempotent booking id already created for this PaymentIntent.
 *
 * @param string $payment_intent_id Intent ID.
 * @return int
 */
function somvio_stripe_get_booking_for_intent( $payment_intent_id ) {
	$key = somvio_stripe_intent_key( $payment_intent_id, 'booking' );
	$raw = get_option( $key, 0 );
	if ( 'pending' === $raw ) {
		return 0;
	}

	return absint( $raw );
}

/**
 * Claim the booking slot before LatePoint create. Returns existing id, 0 if claimed, or -1 if pending elsewhere.
 *
 * @param string $payment_intent_id Intent ID.
 * @return int
 */
function somvio_stripe_claim_booking_slot( $payment_intent_id ) {
	$key      = somvio_stripe_intent_key( $payment_intent_id, 'booking' );
	$existing = somvio_stripe_get_booking_for_intent( $payment_intent_id );
	if ( $existing > 0 ) {
		return $existing;
	}

	if ( add_option( $key, 'pending', '', false ) ) {
		return 0;
	}

	$raw = get_option( $key, 0 );
	if ( 'pending' === $raw ) {
		return -1;
	}

	return absint( $raw );
}

/**
 * Drop a pending booking claim after a failed LatePoint create.
 *
 * @param string $payment_intent_id Intent ID.
 * @return void
 */
function somvio_stripe_release_booking_slot( $payment_intent_id ) {
	$key = somvio_stripe_intent_key( $payment_intent_id, 'booking' );
	if ( 'pending' === get_option( $key, '' ) ) {
		delete_option( $key );
	}
}

/**
 * Remember LatePoint booking created for this PaymentIntent.
 *
 * @param string $payment_intent_id Intent ID.
 * @param int    $booking_id        LatePoint booking ID.
 * @return void
 */
function somvio_stripe_set_booking_for_intent( $payment_intent_id, $booking_id ) {
	$booking_id = absint( $booking_id );
	if ( $booking_id < 1 ) {
		return;
	}
	update_option( somvio_stripe_intent_key( $payment_intent_id, 'booking' ), $booking_id, false );
}

/**
 * Verify Stripe-Signature header against the raw request body.
 *
 * @param string $payload   Raw body.
 * @param string $sig_header Stripe-Signature header.
 * @return bool
 */
function somvio_stripe_verify_webhook_signature( $payload, $sig_header ) {
	$secret  = somvio_get_stripe_webhook_secret();
	$payload = (string) $payload;
	$header  = (string) $sig_header;

	if ( '' === $secret || '' === $payload || '' === $header ) {
		return false;
	}

	$timestamp = '';
	$v1        = array();
	foreach ( explode( ',', $header ) as $part ) {
		$kv = explode( '=', trim( $part ), 2 );
		if ( 2 !== count( $kv ) ) {
			continue;
		}
		if ( 't' === $kv[0] ) {
			$timestamp = $kv[1];
		}
		if ( 'v1' === $kv[0] ) {
			$v1[] = $kv[1];
		}
	}

	if ( '' === $timestamp || empty( $v1 ) ) {
		return false;
	}

	if ( abs( time() - (int) $timestamp ) > 300 ) {
		return false;
	}

	$expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
	foreach ( $v1 as $sig ) {
		if ( hash_equals( $expected, $sig ) ) {
			return true;
		}
	}

	return false;
}
