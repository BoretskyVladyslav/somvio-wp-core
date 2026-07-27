<?php
/**
 * Instant quote calculator — rates, enqueue, REST submit.
 *
 * Figma: 300:1766, 300:1852, 300:1818, 300:1792, 409:6039
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preview / server rate table (GBP). Client totals are UI-only.
 *
 * @return array<string, mixed>
 */
function somvio_get_quote_rates() {
	delete_transient( 'somvio_quote_rates_v5' );
	delete_transient( 'somvio_quote_rates_v6' );
	delete_transient( 'somvio_quote_rates_v7' );

	$cached = get_transient( 'somvio_quote_rates_v8' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$rates = array(
		'currency'         => 'GBP',
		'symbol'           => '£',
		/* Placeholder rates — replace when client confirms final pricing. */
		'bedroom_base'     => array(
			'1' => 55,
			'2' => 75,
			'3' => 95,
			'4' => 120,
			'5' => 150,
		),
		'bathroom_extra'   => 10,
		'service_mult'     => array(
			'regular-cleaning' => 1.0,
			'deep-cleaning'    => 1.4,
			'end-of-tenancy'   => 1.5,
			'airbnb-cleaning'  => 1.2,
			'after-builders'   => 1.6,
		),
		'property_mult'    => array(
			'house'     => 1.0,
			'apartment' => 0.95,
		),
		'time_slots'       => array(
			'08:00',
			'09:00',
			'10:00',
			'12:00',
			'13:00',
			'14:00',
			'16:00',
			'18:00',
		),
		/* Extra Services (booking form) — placeholder prices. */
		'addons'           => array(
			'deep-oven-clean'           => array(
				'label' => __( 'Deep Oven Clean', 'somvio' ),
				'price' => 49,
				'icon'  => 'icon-addon-oven.svg',
			),
			'inside-fridge-freezer'     => array(
				'label' => __( 'Inside Fridge / Freezer', 'somvio' ),
				'price' => 25,
				'icon'  => 'icon-addon-fridge.svg',
			),
			'kitchen-cupboards'         => array(
				'label' => __( 'Inside All Kitchen Cupboards (empty)', 'somvio' ),
				'price' => 39,
				'icon'  => 'icon-addon-cupboards.svg',
			),
			'kitchen-appliances'        => array(
				'label' => __( 'Kitchen Appliances (Internal)', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-appliances.svg',
			),
			'washing-machine'           => array(
				'label' => __( 'Washing Machine', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-washing.svg',
			),
			'dishwasher'                => array(
				'label' => __( 'Dishwasher', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-dishwasher.svg',
			),
			'tumble-dryer'              => array(
				'label' => __( 'Tumble Dryer', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-dryer.svg',
			),
			'microwave-air-fryer'       => array(
				'label' => __( 'Microwave / Air Fryer Cleaning', 'somvio' ),
				'price' => 15,
				'icon'  => 'icon-addon-microwave.svg',
			),
			'carpet-deep-clean'         => array(
				'label' => __( 'Carpet Deep Cleaning (per room)', 'somvio' ),
				'price' => 30,
				'icon'  => 'icon-addon-carpet.svg',
			),
			'venetian-blinds'           => array(
				'label' => __( 'Venetian Blinds (per window)', 'somvio' ),
				'price' => 12,
				'icon'  => 'icon-addon-blinds.svg',
			),
			'balcony-patio'             => array(
				'label' => __( 'Balcony / Patio Cleaning', 'somvio' ),
				'price' => 25,
				'icon'  => 'icon-addon-balcony.svg',
			),
		),
		/* Services that include Extra Services step. */
		'extras_services'  => array(
			'deep-cleaning',
			'end-of-tenancy',
			'after-builders',
		),
	);

	/**
	 * Filter quote rate table.
	 *
	 * @param array<string, mixed> $rates Rate table.
	 */
	$rates = apply_filters( 'somvio_quote_rates', $rates );

	set_transient( 'somvio_quote_rates_v8', $rates, HOUR_IN_SECONDS );

	return $rates;
}

/**
 * Recalculate quote total from trusted inputs (server authority).
 *
 * @param string   $service  Service key.
 * @param string   $property Property key.
 * @param int      $bedrooms Bedroom count.
 * @param int      $bathrooms Bathroom count.
 * @param string[] $addons   Selected add-on keys.
 * @return float
 */
function somvio_calculate_quote_price( $service, $property, $bedrooms, $bathrooms, $addons = array() ) {
	$rates = somvio_get_quote_rates();

	$bed_key = (string) max( 1, min( 5, absint( $bedrooms ) ) );
	$base    = isset( $rates['bedroom_base'][ $bed_key ] )
		? (float) $rates['bedroom_base'][ $bed_key ]
		: 55.0;

	$bath_extra = max( 0, absint( $bathrooms ) - 1 ) * (float) $rates['bathroom_extra'];
	$svc_mult   = isset( $rates['service_mult'][ $service ] )
		? (float) $rates['service_mult'][ $service ]
		: 1.0;
	$prop_mult  = isset( $rates['property_mult'][ $property ] )
		? (float) $rates['property_mult'][ $property ]
		: 1.0;

	$addon_total = 0.0;
	$addon_defs  = isset( $rates['addons'] ) && is_array( $rates['addons'] ) ? $rates['addons'] : array();

	foreach ( (array) $addons as $addon_key ) {
		$addon_key = sanitize_key( (string) $addon_key );
		if ( isset( $addon_defs[ $addon_key ]['price'] ) ) {
			$addon_total += (float) $addon_defs[ $addon_key ]['price'];
		}
	}

	return round( ( ( $base + $bath_extra ) * $svc_mult * $prop_mult ) + $addon_total, 2 );
}

/**
 * Validate quote phone (UK / international digits).
 *
 * @param string $phone Raw phone input.
 * @return bool
 */
function somvio_is_valid_quote_phone( $phone ) {
	$phone = preg_replace( '/\s+/', '', (string) $phone );
	if ( '' === $phone ) {
		return false;
	}

	return (bool) preg_match( '/^(\+?[1-9]\d{9,14}|0[1-9]\d{9,10})$/', $phone );
}

/**
 * Validate quote email with stricter pattern than is_email alone.
 *
 * @param string $email Email address.
 * @return bool
 */
function somvio_is_valid_quote_email( $email ) {
	$email = trim( (string) $email );
	if ( ! is_email( $email ) ) {
		return false;
	}

	return (bool) preg_match( '/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email );
}

/**
 * Validate a real, non-past booking date in the site timezone.
 *
 * @param string $date ISO date.
 * @return bool
 */
function somvio_is_valid_quote_date( $date ) {
	$date = trim( (string) $date );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return false;
	}

	$parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
	$errors = DateTimeImmutable::getLastErrors();

	if (
		false === $parsed ||
		( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) ||
		$date !== $parsed->format( 'Y-m-d' )
	) {
		return false;
	}

	return $date >= current_datetime()->format( 'Y-m-d' );
}

/**
 * Return a multibyte-safe text length where available.
 *
 * @param string $value Text value.
 * @return int
 */
function somvio_quote_text_length( $value ) {
	$value = (string) $value;

	return function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
}

/**
 * Service type options for the quote calculator.
 *
 * @return array<string, string> value => label
 */
function somvio_get_quote_service_options() {
	return array(
		'regular-cleaning' => __( 'Regular Cleaning', 'somvio' ),
		'deep-cleaning'    => __( 'Deep Cleaning', 'somvio' ),
		'end-of-tenancy'   => __( 'End of Tenancy', 'somvio' ),
		'airbnb-cleaning'  => __( 'Airbnb Cleaning', 'somvio' ),
		'after-builders'   => __( 'After Builders', 'somvio' ),
	);
}

/**
 * Property type options.
 *
 * @return array<string, string>
 */
function somvio_get_quote_property_options() {
	return array(
		'house'     => __( 'House', 'somvio' ),
		'apartment' => __( 'Apartment', 'somvio' ),
	);
}

/**
 * Map a service title (or slug) to a calculator service key.
 *
 * @param string $title Service title.
 * @return string
 */
function somvio_quote_service_key_from_title( $title ) {
	$title = strtolower( trim( (string) $title ) );
	$map   = array(
		'regular cleaning' => 'regular-cleaning',
		'deep cleaning'    => 'deep-cleaning',
		'end of tenancy'   => 'end-of-tenancy',
		'airbnb cleaning'  => 'airbnb-cleaning',
		'after builders'   => 'after-builders',
	);

	return isset( $map[ $title ] ) ? $map[ $title ] : 'regular-cleaning';
}

/**
 * Whether the current request should load calculator assets.
 *
 * @return bool
 */
function somvio_needs_quote_calculator_assets() {
	if ( is_admin() ) {
		return false;
	}

	// Global header Book Now opens the floating quote modal.
	/**
	 * Force calculator assets off/on.
	 *
	 * @param bool $needed Whether assets are needed.
	 */
	return (bool) apply_filters( 'somvio_needs_quote_calculator_assets', true );
}

/**
 * Render floating quote modal before </body>.
 *
 * @return void
 */
function somvio_render_quote_modal() {
	if ( is_admin() ) {
		return;
	}

	get_template_part( 'template-parts/components/quote', 'modal' );
}
add_action( 'wp_footer', 'somvio_render_quote_modal', 20 );

/**
 * Enqueue quote calculator script + localize rates / REST.
 *
 * @return void
 */
function somvio_enqueue_quote_calculator_assets() {
	if ( ! somvio_needs_quote_calculator_assets() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/quote-calculator.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-quote-calculator',
		get_stylesheet_directory_uri() . '/assets/js/quote-calculator.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);

	wp_localize_script(
		'somvio-quote-calculator',
		'somvioQuoteCalc',
		array(
			'restUrl'  => esc_url_raw( rest_url( 'somvio/v1/quote/submit' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'rates'    => somvio_get_quote_rates(),
			'i18n'     => array(
				'stepOf'           => __( 'Step %1$d of %2$d', 'somvio' ),
				'titleDefault'     => __( 'Get Your Instant Quote', 'somvio' ),
				'titleDate'        => __( 'Get Your Date', 'somvio' ),
				'titleExtras'      => __( 'Extra Services', 'somvio' ),
				'selectDate'       => __( 'Select date', 'somvio' ),
				'selectTime'       => __( 'Please select a time slot.', 'somvio' ),
				'bedroomHome'      => __( '%d Bedroom Home', 'somvio' ),
				'mainRooms'        => __( 'Main rooms', 'somvio' ),
				'bedrooms'         => __( 'Bedrooms', 'somvio' ),
				'bathrooms'        => __( 'Bathrooms', 'somvio' ),
				'noOfBedrooms'     => __( 'No. of Bedrooms', 'somvio' ),
				'noOfBathrooms'    => __( 'No. of Bathrooms', 'somvio' ),
				'linenChanges'     => __( 'No. of Linen Changes', 'somvio' ),
				'nextStep'         => __( 'Next Step', 'somvio' ),
				'back'             => __( 'Back', 'somvio' ),
				'submitQuote'      => __( 'Submit Quote', 'somvio' ),
				'submitting'       => __( 'Submitting…', 'somvio' ),
				'close'            => __( 'Close', 'somvio' ),
				'required'         => __( 'Please complete the required fields.', 'somvio' ),
				'invalidEmail'     => __( 'Please enter a valid email address.', 'somvio' ),
				'invalidPhone'     => __( 'Please enter a valid phone number.', 'somvio' ),
				'invalidName'      => __( 'Please enter your full name.', 'somvio' ),
				'submitError'      => __( 'Something went wrong. Please try again.', 'somvio' ),
				'estimatedTotal'   => __( 'Estimated total', 'somvio' ),
				'previewNote'      => __( 'Preview only — final price confirmed after review.', 'somvio' ),
				'months'           => array(
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
				'weekdays'         => array(
					__( 'S', 'somvio' ),
					__( 'M', 'somvio' ),
					__( 'T', 'somvio' ),
					__( 'W', 'somvio' ),
					__( 'T', 'somvio' ),
					__( 'F', 'somvio' ),
					__( 'S', 'somvio' ),
				),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_quote_calculator_assets' );

/**
 * Sanitize a list of string keys.
 *
 * @param mixed $value Raw value.
 * @return string[]
 */
function somvio_rest_sanitize_string_list( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'sanitize_key', $value ) ) );
}

/**
 * REST permission: valid wp_rest nonce (public quote form).
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function somvio_rest_can_submit_quote( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Invalid nonce.', 'somvio' ),
			array( 'status' => 403 )
		);
	}

	return true;
}

/**
 * Handle quote submit — recalculate server-side, never trust client total.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function somvio_rest_submit_quote( WP_REST_Request $request ) {
	$service   = sanitize_key( (string) $request['service'] );
	$property  = sanitize_key( (string) $request['property'] );
	$bedrooms       = absint( $request['bedrooms'] );
	$bathrooms      = absint( $request['bathrooms'] );
	$main_rooms     = absint( $request['main_rooms'] ?? 0 );
	$linen_changes  = absint( $request['linen_changes'] ?? 0 );
	$welcome_pack   = sanitize_key( (string) ( $request['welcome_pack'] ?? 'no' ) );
	$toilets        = absint( $request['toilets'] ?? 0 );
	$kitchens       = absint( $request['kitchens'] ?? 0 );
	$date      = sanitize_text_field( (string) $request['date'] );
	$time      = sanitize_text_field( (string) $request['time'] );
	$first     = sanitize_text_field( (string) ( $request['first_name'] ?? '' ) );
	$last      = sanitize_text_field( (string) ( $request['last_name'] ?? '' ) );
	$name      = sanitize_text_field( (string) $request['name'] );
	$email     = sanitize_email( (string) $request['email'] );
	$phone     = sanitize_text_field( (string) $request['phone'] );
	$address   = sanitize_text_field( (string) ( $request['address'] ?? '' ) );
	$comment   = sanitize_textarea_field( (string) $request['comment'] );
	$addons    = somvio_rest_sanitize_string_list( $request['addons'] ?? array() );
	$terms     = rest_sanitize_boolean( $request['terms_accepted'] ?? false );
	$source    = sanitize_key( (string) ( $request['source'] ?? 'quote' ) );
	$payment_method = function_exists( 'somvio_normalize_payment_method' )
		? somvio_normalize_payment_method( (string) ( $request['payment_method'] ?? 'cash' ) )
		: 'cash';

	if ( '' === $property ) {
		$property = 'house';
	}

	$services   = somvio_get_quote_service_options();
	$props      = somvio_get_quote_property_options();
	$rates      = somvio_get_quote_rates();
	$addon_defs = isset( $rates['addons'] ) && is_array( $rates['addons'] ) ? $rates['addons'] : array();

	if ( ! in_array( $source, array( 'quote', 'booking' ), true ) ) {
		return new WP_Error( 'invalid_source', __( 'Invalid submission source.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( ! isset( $services[ $service ] ) ) {
		return new WP_Error( 'invalid_service', __( 'Invalid service type.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( ! isset( $props[ $property ] ) ) {
		return new WP_Error( 'invalid_property', __( 'Invalid property type.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( $bedrooms < 1 || $bedrooms > 5 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( $bathrooms < 1 || $bathrooms > 4 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( $main_rooms > 10 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( $linen_changes > 10 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( ! in_array( $welcome_pack, array( 'yes', 'no' ), true ) ) {
		$welcome_pack = 'no';
	}
	if ( $toilets > 5 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( $kitchens > 5 ) {
		return new WP_Error( 'invalid_rooms', __( 'Invalid room counts.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( ! somvio_is_valid_quote_date( $date ) ) {
		return new WP_Error( 'invalid_date', __( 'Invalid date.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( ! in_array( $time, $rates['time_slots'], true ) ) {
		return new WP_Error( 'invalid_time', __( 'Invalid time slot.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( '' !== $first || '' !== $last ) {
		$name = trim( $first . ' ' . $last );
	} else {
		$name = trim( $name );
	}

	if ( somvio_quote_text_length( $name ) < 2 || somvio_quote_text_length( $name ) > 160 ) {
		return new WP_Error( 'invalid_name', __( 'Please enter your full name.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( somvio_quote_text_length( $email ) > 254 || ! somvio_is_valid_quote_email( $email ) ) {
		return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( somvio_quote_text_length( $phone ) > 32 || ! somvio_is_valid_quote_phone( $phone ) ) {
		return new WP_Error( 'invalid_phone', __( 'Please enter a valid phone number.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( somvio_quote_text_length( $comment ) > 2000 ) {
		return new WP_Error( 'invalid_comment', __( 'Comment is too long.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( 'booking' === $source ) {
		if (
			somvio_quote_text_length( trim( $first ) ) < 2 ||
			somvio_quote_text_length( trim( $first ) ) > 80 ||
			somvio_quote_text_length( trim( $last ) ) < 2 ||
			somvio_quote_text_length( trim( $last ) ) > 80
		) {
			return new WP_Error( 'invalid_name', __( 'Please enter your first and last name.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( somvio_quote_text_length( trim( $address ) ) < 3 || somvio_quote_text_length( trim( $address ) ) > 255 ) {
			return new WP_Error( 'invalid_address', __( 'Please enter your street address.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( ! $terms ) {
			return new WP_Error( 'terms_required', __( 'Please accept the Terms & Conditions and Privacy Policy.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( ! in_array( $payment_method, array( 'cash', 'online' ), true ) ) {
			return new WP_Error( 'invalid_payment', __( 'Please select a payment method.', 'somvio' ), array( 'status' => 400 ) );
		}
	} else {
		// Quick quote has no payment step.
		$payment_method = 'cash';
	}

	$extras_services = isset( $rates['extras_services'] ) && is_array( $rates['extras_services'] )
		? $rates['extras_services']
		: array( 'deep-cleaning', 'end-of-tenancy', 'after-builders' );
	if ( ! in_array( $service, $extras_services, true ) ) {
		$addons = array();
	}

	foreach ( $addons as $addon_key ) {
		if ( ! isset( $addon_defs[ $addon_key ] ) ) {
			return new WP_Error( 'invalid_addon', __( 'Invalid add-on selection.', 'somvio' ), array( 'status' => 400 ) );
		}
	}

	$server_total = somvio_calculate_quote_price( $service, $property, $bedrooms, $bathrooms, $addons );
	$client_total = isset( $request['client_total'] ) ? (float) $request['client_total'] : null;

	if ( null !== $client_total && ! is_finite( $client_total ) ) {
		return new WP_Error( 'invalid_total', __( 'Invalid estimated total.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( null !== $client_total && abs( $client_total - $server_total ) > 0.01 ) {
		return new WP_Error(
			'price_mismatch',
			__( 'Price changed. Review the new total.', 'somvio' ),
			array(
				'status' => 409,
				'total'  => $server_total,
			)
		);
	}

	$payload = array(
		'service'         => $service,
		'property'        => $property,
		'bedrooms'        => $bedrooms,
		'bathrooms'       => $bathrooms,
		'main_rooms'      => $main_rooms,
		'linen_changes'   => $linen_changes,
		'welcome_pack'    => $welcome_pack,
		'toilets'         => $toilets,
		'kitchens'        => $kitchens,
		'date'            => $date,
		'time'            => $time,
		'first_name'      => $first,
		'last_name'       => $last,
		'name'            => $name,
		'email'           => $email,
		'phone'           => $phone,
		'address'         => $address,
		'comment'         => $comment,
		'addons'          => $addons,
		'terms_accepted'  => $terms,
		'source'          => $source,
		'payment_method'  => $payment_method,
		'total'           => $server_total,
	);

	$process = array();
	if ( function_exists( 'somvio_process_booking_submission' ) ) {
		$process = somvio_process_booking_submission( $payload );
		$payload['booking_id'] = isset( $process['booking_id'] ) ? (int) $process['booking_id'] : 0;
		$payload['order_id']   = isset( $process['order_id'] ) ? (int) $process['order_id'] : 0;
		$payload['_processed'] = true;
	}

	/**
	 * Fired after a quote passes validation (email/CRM hooks).
	 *
	 * @param array<string, mixed> $payload Sanitized quote data with server total.
	 */
	do_action( 'somvio_quote_submitted', $payload );

	$response = array(
		'success'    => true,
		'total'      => $server_total,
		'symbol'     => $rates['symbol'],
		'booking_id' => isset( $process['booking_id'] ) ? (int) $process['booking_id'] : 0,
		'order_id'   => isset( $process['order_id'] ) ? (int) $process['order_id'] : 0,
		'payment_method' => $payment_method,
		'message'    => __( 'Thank you! Your request has been sent.', 'somvio' ),
	);

	if (
		'online' === $payment_method &&
		isset( $process['payment'] ) &&
		is_array( $process['payment'] )
	) {
		$payment = $process['payment'];
		if ( ! empty( $payment['success'] ) ) {
			$response['requires_payment'] = true;
			$response['payment']          = array(
				'client_secret'     => (string) ( $payment['client_secret'] ?? '' ),
				'payment_intent_id' => (string) ( $payment['payment_intent_id'] ?? '' ),
				'publishable_key'   => (string) ( $payment['publishable_key'] ?? '' ),
			);
			$response['message'] = __( 'Booking created. Please complete your online payment.', 'somvio' );
		} else {
			$response['requires_payment'] = true;
			$response['payment_error']    = (string) ( $payment['error'] ?? 'stripe_failed' );
			$response['message']          = __( 'Booking saved, but online payment could not be started. We’ll contact you to arrange payment.', 'somvio' );
		}
	}

	if (
		'cash' === $payment_method &&
		'booking' === $source &&
		! empty( $process['latepoint']['success'] )
	) {
		$response['message'] = __( 'Thank you! Your booking is confirmed — pay on completion.', 'somvio' );
		$response['booking_status'] = isset( $process['latepoint']['status'] )
			? (string) $process['latepoint']['status']
			: 'approved';
		$response['payment_status'] = isset( $process['latepoint']['payment_status'] )
			? (string) $process['latepoint']['payment_status']
			: 'not_paid';
	}

	return rest_ensure_response( $response );
}

/**
 * Register quote REST routes.
 *
 * @return void
 */
function somvio_register_quote_rest_routes() {
	register_rest_route(
		'somvio/v1',
		'/quote/submit',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'somvio_rest_submit_quote',
			'permission_callback' => 'somvio_rest_can_submit_quote',
			'args'                => array(
				'service'      => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
				'property'     => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
				'bedrooms'     => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'bathrooms'    => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'date'         => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'time'         => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'name'         => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email'        => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_email',
				),
				'phone'        => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'comment'      => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_textarea_field',
				),
				'addons'          => array(
					'required'          => false,
					'type'              => 'array',
					'default'           => array(),
					'sanitize_callback' => 'somvio_rest_sanitize_string_list',
				),
				'toilets'         => array(
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				),
				'kitchens'        => array(
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				),
				'main_rooms'      => array(
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				),
				'linen_changes'   => array(
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				),
				'welcome_pack'    => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => 'no',
					'sanitize_callback' => 'sanitize_key',
				),
				'first_name'      => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'last_name'       => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'address'         => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'terms_accepted'  => array(
					'required' => false,
					'type'     => 'boolean',
					'default'  => false,
				),
				'source'          => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => 'quote',
					'sanitize_callback' => 'sanitize_key',
				),
				'payment_method'  => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => 'cash',
					'sanitize_callback' => 'sanitize_key',
				),
				'client_total'    => array(
					'required' => false,
					'type'     => 'number',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'somvio_register_quote_rest_routes' );
