<?php
/**
 * LatePoint booking creation from Somvio quote/booking payload.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether LatePoint booking models are available.
 *
 * @return bool
 */
function somvio_latepoint_is_available() {
	return class_exists( 'OsBookingModel' )
		&& class_exists( 'OsCustomerModel' )
		&& class_exists( 'OsServiceModel' );
}

/**
 * Map theme service keys → LatePoint service IDs (filterable).
 *
 * Keys match somvio_get_quote_service_options(). Values are LatePoint service IDs.
 * Empty / missing values fall back to name matching.
 *
 * @return array<string, int>
 */
function somvio_get_latepoint_service_map() {
	$map = array(
		'regular-cleaning' => 0,
		'deep-cleaning'    => 0,
		'end-of-tenancy'   => 0,
		'airbnb-cleaning'  => 0,
		'after-builders'   => 0,
	);

	/**
	 * Filter LatePoint service ID map.
	 *
	 * @param array<string, int> $map Service key => LatePoint service ID.
	 */
	$map = apply_filters( 'somvio_latepoint_service_map', $map );

	$out = array();
	foreach ( (array) $map as $key => $id ) {
		$out[ sanitize_key( (string) $key ) ] = absint( $id );
	}

	return $out;
}

/**
 * Resolve LatePoint service ID for a theme service key.
 *
 * @param string $service_key Theme service key.
 * @return int 0 if unresolved.
 */
function somvio_resolve_latepoint_service_id( $service_key ) {
	$service_key = sanitize_key( (string) $service_key );
	$map         = somvio_get_latepoint_service_map();

	if ( ! empty( $map[ $service_key ] ) ) {
		return (int) $map[ $service_key ];
	}

	if ( ! class_exists( 'OsServiceHelper' ) ) {
		return 0;
	}

	$labels = function_exists( 'somvio_get_quote_service_options' )
		? somvio_get_quote_service_options()
		: array();
	$needle = isset( $labels[ $service_key ] )
		? strtolower( trim( (string) $labels[ $service_key ] ) )
		: str_replace( '-', ' ', $service_key );

	$services = OsServiceHelper::get_services( false );
	if ( ! is_array( $services ) ) {
		return 0;
	}

	foreach ( $services as $service ) {
		if ( ! is_object( $service ) || empty( $service->id ) ) {
			continue;
		}
		$name = isset( $service->name ) ? strtolower( trim( (string) $service->name ) ) : '';
		if ( $name && ( $name === $needle || false !== strpos( $name, $needle ) || false !== strpos( $needle, $name ) ) ) {
			return (int) $service->id;
		}
	}

	// Fallback: first active service so the booking still lands in LatePoint.
	foreach ( $services as $service ) {
		if ( is_object( $service ) && ! empty( $service->id ) ) {
			return (int) $service->id;
		}
	}

	return 0;
}

/**
 * First usable LatePoint agent ID.
 *
 * @return int
 */
function somvio_get_latepoint_default_agent_id() {
	$forced = absint( apply_filters( 'somvio_latepoint_default_agent_id', 0 ) );
	if ( $forced > 0 ) {
		return $forced;
	}

	if ( ! class_exists( 'OsAgentHelper' ) ) {
		return 0;
	}

	$agents = OsAgentHelper::get_allowed_active_agents();
	if ( is_array( $agents ) ) {
		foreach ( $agents as $agent ) {
			if ( is_object( $agent ) && ! empty( $agent->id ) ) {
				return (int) $agent->id;
			}
		}
	}

	return 0;
}

/**
 * First usable LatePoint location ID.
 *
 * @return int
 */
function somvio_get_latepoint_default_location_id() {
	$forced = absint( apply_filters( 'somvio_latepoint_default_location_id', 0 ) );
	if ( $forced > 0 ) {
		return $forced;
	}

	if ( ! class_exists( 'OsLocationHelper' ) ) {
		return 0;
	}

	$locations = OsLocationHelper::get_locations( false );
	if ( is_array( $locations ) ) {
		foreach ( $locations as $location ) {
			if ( is_object( $location ) && ! empty( $location->id ) ) {
				return (int) $location->id;
			}
		}
	}

	return 0;
}

/**
 * Convert HH:MM to minutes from midnight.
 *
 * @param string $time Time slot.
 * @return int
 */
function somvio_time_slot_to_minutes( $time ) {
	$time = trim( (string) $time );
	if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', $time, $m ) ) {
		return 0;
	}

	$hours   = min( 23, max( 0, (int) $m[1] ) );
	$minutes = min( 59, max( 0, (int) $m[2] ) );

	return ( $hours * 60 ) + $minutes;
}

/**
 * Find or create a LatePoint customer from payload.
 *
 * @param array<string, mixed> $payload Payload.
 * @return OsCustomerModel|null
 */
function somvio_latepoint_get_or_create_customer( array $payload ) {
	if ( ! class_exists( 'OsCustomerModel' ) ) {
		return null;
	}

	$email = isset( $payload['email'] ) ? sanitize_email( (string) $payload['email'] ) : '';
	if ( ! $email ) {
		return null;
	}

	$existing = ( new OsCustomerModel() )->where( array( 'email' => $email ) )->set_limit( 1 )->get_results_as_models();
	if ( $existing && is_object( $existing ) && ! empty( $existing->id ) ) {
		$customer = $existing;
	} else {
		$customer = new OsCustomerModel();
		$customer->email = $email;
	}

	$first = trim( (string) ( $payload['first_name'] ?? '' ) );
	$last  = trim( (string) ( $payload['last_name'] ?? '' ) );
	$name  = trim( (string) ( $payload['name'] ?? '' ) );

	if ( '' === $first && '' !== $name ) {
		$parts = preg_split( '/\s+/', $name, 2 );
		$first = isset( $parts[0] ) ? $parts[0] : $name;
		$last  = isset( $parts[1] ) ? $parts[1] : $last;
	}

	$customer->first_name = $first ? $first : __( 'Customer', 'somvio' );
	$customer->last_name  = $last;
	$customer->phone      = isset( $payload['phone'] ) ? sanitize_text_field( (string) $payload['phone'] ) : '';
	$customer->is_guest   = true;

	$notes = array();
	if ( ! empty( $payload['address'] ) ) {
		$notes[] = sprintf(
			/* translators: %s: address */
			__( 'Address: %s', 'somvio' ),
			sanitize_text_field( (string) $payload['address'] )
		);
	}
	if ( ! empty( $payload['comment'] ) ) {
		$notes[] = sanitize_textarea_field( (string) $payload['comment'] );
	}
	if ( $notes ) {
		$customer->notes = implode( "\n", $notes );
	}

	if ( ! $customer->save() ) {
		return null;
	}

	return $customer;
}

/**
 * Build admin notes / booking meta text from payload.
 *
 * @param array<string, mixed> $payload Payload.
 * @return string
 */
function somvio_latepoint_booking_notes( array $payload ) {
	$labels = function_exists( 'somvio_booking_email_labels' )
		? somvio_booking_email_labels( $payload )
		: array();

	$lines = array(
		sprintf( 'Source: %s', $labels['source'] ?? (string) ( $payload['source'] ?? '' ) ),
		sprintf( 'Service: %s', $labels['service'] ?? (string) ( $payload['service'] ?? '' ) ),
		sprintf( 'Property: %s', $labels['property'] ?? (string) ( $payload['property'] ?? '' ) ),
		sprintf( 'Main rooms: %d', (int) ( $payload['main_rooms'] ?? 0 ) ),
		sprintf( 'Bedrooms: %d', (int) ( $payload['bedrooms'] ?? 0 ) ),
		sprintf( 'Bathrooms: %d', (int) ( $payload['bathrooms'] ?? 0 ) ),
		sprintf( 'Linen changes: %d', (int) ( $payload['linen_changes'] ?? 0 ) ),
		sprintf( 'Welcome pack: %s', $labels['welcome_pack'] ?? 'no' ),
		sprintf( 'Extras: %s', $labels['addons'] ?? '' ),
		sprintf( 'Payment: %s', $labels['payment'] ?? '' ),
		sprintf( 'Server total: %s', $labels['total_formatted'] ?? '' ),
	);

	if ( ! empty( $payload['address'] ) ) {
		$lines[] = 'Address: ' . sanitize_text_field( (string) $payload['address'] );
	}
	if ( ! empty( $payload['comment'] ) ) {
		$lines[] = 'Comment: ' . sanitize_textarea_field( (string) $payload['comment'] );
	}

	return implode( "\n", $lines );
}

/**
 * Create LatePoint order + booking for a submission.
 *
 * @param array<string, mixed> $payload Sanitized payload.
 * @return array{
 *   success:bool,
 *   booking_id?:int,
 *   order_id?:int,
 *   customer_id?:int,
 *   status?:string,
 *   payment_status?:string,
 *   error?:string
 * }
 */
function somvio_latepoint_create_booking( array $payload ) {
	if ( ! somvio_latepoint_is_available() ) {
		return array(
			'success' => false,
			'error'   => 'latepoint_unavailable',
		);
	}

	$service_id  = somvio_resolve_latepoint_service_id( (string) ( $payload['service'] ?? '' ) );
	$agent_id    = somvio_get_latepoint_default_agent_id();
	$location_id = somvio_get_latepoint_default_location_id();

	if ( $service_id < 1 || $agent_id < 1 || $location_id < 1 ) {
		return array(
			'success' => false,
			'error'   => 'latepoint_not_configured',
		);
	}

	$customer = somvio_latepoint_get_or_create_customer( $payload );
	if ( ! $customer || empty( $customer->id ) ) {
		return array(
			'success' => false,
			'error'   => 'customer_save_failed',
		);
	}

	$payment_method = isset( $payload['payment_method'] ) ? sanitize_key( (string) $payload['payment_method'] ) : 'cash';
	$is_online      = in_array( $payment_method, array( 'online', 'stripe' ), true );

	/*
	 * Online: payment_pending until Stripe confirms.
	 * Cash / pay on completion: approved (confirmed) with unpaid order.
	 */
	$booking_status = $is_online
		? ( defined( 'LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING' ) ? LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING : 'payment_pending' )
		: ( defined( 'LATEPOINT_BOOKING_STATUS_APPROVED' ) ? LATEPOINT_BOOKING_STATUS_APPROVED : 'approved' );

	$order_payment_status = defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID' )
		? LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID
		: 'not_paid';

	if ( $is_online && defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_PROCESSING' ) ) {
		$order_payment_status = LATEPOINT_ORDER_PAYMENT_STATUS_PROCESSING;
	}

	$total = isset( $payload['total'] ) ? (float) $payload['total'] : 0.0;
	if ( class_exists( 'OsMoneyHelper' ) && method_exists( 'OsMoneyHelper', 'pad_to_db_format' ) ) {
		$total_db = OsMoneyHelper::pad_to_db_format( $total );
	} else {
		$total_db = number_format( $total, 4, '.', '' );
	}

	$order_id = 0;
	$order_item_id = 0;

	if ( class_exists( 'OsOrderModel' ) && class_exists( 'OsOrderItemModel' ) ) {
		$order = new OsOrderModel();
		$order->customer_id     = (int) $customer->id;
		$order->status          = defined( 'LATEPOINT_ORDER_STATUS_OPEN' ) ? LATEPOINT_ORDER_STATUS_OPEN : 'open';
		$order->payment_status  = $order_payment_status;
		$order->fulfillment_status = defined( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED' )
			? LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED
			: ( defined( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_UNFULFILLED' ) ? LATEPOINT_ORDER_FULFILLMENT_STATUS_UNFULFILLED : 'not_fulfilled' );
		$order->total           = $total_db;
		$order->subtotal        = $total_db;
		$order->customer_comment = isset( $payload['comment'] ) ? sanitize_textarea_field( (string) $payload['comment'] ) : '';
		$order->source_url      = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['HTTP_REFERER'] ) ) : home_url( '/booking/' );
		$order->ip_address      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';

		$price_breakdown = array(
			'somvio' => array(
				'service'  => (string) ( $payload['service'] ?? '' ),
				'total'    => $total,
				'payment'  => $payment_method,
				'addons'   => isset( $payload['addons'] ) ? (array) $payload['addons'] : array(),
			),
		);
		$order->price_breakdown = wp_json_encode( $price_breakdown );

		if ( $order->save() ) {
			$order_id = (int) $order->id;

			$order_item = new OsOrderItemModel();
			$order_item->order_id = $order_id;
			$order_item->variant  = defined( 'LATEPOINT_ITEM_VARIANT_BOOKING' ) ? LATEPOINT_ITEM_VARIANT_BOOKING : 'booking';
			$order_item->total    = $total_db;
			$order_item->subtotal = $total_db;
			$order_item->item_data = wp_json_encode(
				array(
					'service_id'  => $service_id,
					'agent_id'    => $agent_id,
					'location_id' => $location_id,
					'start_date'  => (string) ( $payload['date'] ?? '' ),
					'start_time'  => somvio_time_slot_to_minutes( (string) ( $payload['time'] ?? '' ) ),
				)
			);

			if ( $order_item->save() ) {
				$order_item_id = (int) $order_item->id;
			}
		}
	}

	$start_time = somvio_time_slot_to_minutes( (string) ( $payload['time'] ?? '' ) );
	$booking    = new OsBookingModel();
	$booking->service_id   = $service_id;
	$booking->agent_id     = $agent_id;
	$booking->location_id  = $location_id;
	$booking->customer_id  = (int) $customer->id;
	$booking->start_date   = sanitize_text_field( (string) ( $payload['date'] ?? '' ) );
	$booking->start_time   = $start_time;
	$booking->status       = $booking_status;
	$booking->total_attendees = 1;

	if ( $order_item_id > 0 ) {
		$booking->order_item_id = $order_item_id;
	}

	$service_model = new OsServiceModel( $service_id );
	$duration      = ( ! empty( $service_model->duration ) ) ? (int) $service_model->duration : 60;
	$booking->duration = $duration;
	$booking->end_time = $start_time + $duration;
	$booking->end_date = $booking->start_date;
	if ( $booking->end_time >= 24 * 60 ) {
		$booking->end_time = $booking->end_time - ( 24 * 60 );
		try {
			$end = new DateTimeImmutable( $booking->start_date, wp_timezone() );
			$booking->end_date = $end->modify( '+1 day' )->format( 'Y-m-d' );
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			$booking->end_date = $booking->start_date;
		}
	}

	if ( method_exists( $booking, 'set_utc_datetimes' ) ) {
		$booking->set_utc_datetimes();
	}

	if ( ! $booking->save() ) {
		$errors = method_exists( $booking, 'get_error_messages' ) ? $booking->get_error_messages() : array();
		return array(
			'success' => false,
			'error'   => is_array( $errors ) && $errors ? implode( ', ', $errors ) : 'booking_save_failed',
		);
	}

	$notes = somvio_latepoint_booking_notes( $payload );
	if ( method_exists( $booking, 'save_meta_by_key' ) ) {
		$booking->save_meta_by_key( 'somvio_payload', wp_json_encode( $payload ) );
		$booking->save_meta_by_key( 'somvio_notes', $notes );
		$booking->save_meta_by_key( 'somvio_payment_method', $payment_method );
		$booking->save_meta_by_key( 'somvio_server_total', (string) $total );
	}

	/**
	 * After a LatePoint booking is created from Somvio.
	 *
	 * @param OsBookingModel       $booking Booking.
	 * @param array<string, mixed> $payload Payload.
	 * @param int                  $order_id Order ID.
	 */
	do_action( 'somvio_latepoint_booking_created', $booking, $payload, $order_id );

	return array(
		'success'         => true,
		'booking_id'      => (int) $booking->id,
		'order_id'        => $order_id,
		'customer_id'     => (int) $customer->id,
		'status'          => (string) $booking->status,
		'payment_status'  => (string) $order_payment_status,
		'booking_code'    => isset( $booking->booking_code ) ? (string) $booking->booking_code : '',
	);
}

/**
 * Mark LatePoint booking/order as paid after Stripe success.
 *
 * @param int $booking_id LatePoint booking ID.
 * @return bool
 */
function somvio_latepoint_mark_booking_paid( $booking_id ) {
	$booking_id = absint( $booking_id );
	if ( $booking_id < 1 || ! class_exists( 'OsBookingModel' ) ) {
		return false;
	}

	$booking = new OsBookingModel( $booking_id );
	if ( empty( $booking->id ) ) {
		return false;
	}

	$approved = defined( 'LATEPOINT_BOOKING_STATUS_APPROVED' ) ? LATEPOINT_BOOKING_STATUS_APPROVED : 'approved';
	if ( method_exists( $booking, 'update_status' ) ) {
		$booking->update_status( $approved );
	} else {
		$booking->status = $approved;
		$booking->save();
	}

	if ( ! empty( $booking->order_item_id ) && class_exists( 'OsOrderItemModel' ) && class_exists( 'OsOrderModel' ) ) {
		$item = new OsOrderItemModel( (int) $booking->order_item_id );
		if ( ! empty( $item->order_id ) ) {
			$order = new OsOrderModel( (int) $item->order_id );
			if ( ! empty( $order->id ) ) {
				$paid = defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID' )
					? LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID
					: 'fully_paid';
				$order->update_attributes( array( 'payment_status' => $paid ) );
			}
		}
	}

	if ( method_exists( $booking, 'save_meta_by_key' ) ) {
		$booking->save_meta_by_key( 'somvio_stripe_paid', '1' );
	}

	return true;
}
