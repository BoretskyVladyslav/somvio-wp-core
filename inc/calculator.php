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
 * Transient key for the merged quote rate table.
 *
 * @return string
 */
function somvio_quote_rates_cache_key() {
	return 'somvio_quote_rates_v13';
}

/**
 * Default time slots for quote/booking.
 *
 * @return string[]
 */
function somvio_quote_default_time_slots() {
	return array(
		'08:00',
		'09:00',
		'10:00',
		'12:00',
		'13:00',
		'14:00',
		'16:00',
		'18:00',
	);
}

/**
 * Default 1–5 bedroom base prices (GBP).
 *
 * @return array<string, float>
 */
function somvio_quote_default_bedroom_base() {
	return array(
		'1' => 55,
		'2' => 75,
		'3' => 95,
		'4' => 120,
		'5' => 150,
	);
}

/**
 * Linen rate: missing, non-numeric, or <= 0 → £14.
 *
 * @param mixed $value Raw rate.
 * @return float
 */
function somvio_normalize_linen_rate( $value ) {
	if ( ! is_numeric( $value ) ) {
		return 14.0;
	}

	$rate = (float) $value;

	return ( is_finite( $rate ) && $rate > 0 ) ? $rate : 14.0;
}

/**
 * Whether a cached rate table has the required shape.
 *
 * @param mixed $rates Rate table.
 * @return bool
 */
function somvio_quote_rates_shape_is_valid( $rates ) {
	return is_array( $rates )
		&& ! empty( $rates['bedroom_base'] ) && is_array( $rates['bedroom_base'] )
		&& ! empty( $rates['time_slots'] ) && is_array( $rates['time_slots'] );
}

/**
 * Fill linen / slots / bases and clamp bathroom extra.
 *
 * @param mixed $rates Rate table.
 * @return array<string, mixed>
 */
function somvio_normalize_quote_rates( $rates ) {
	if ( ! is_array( $rates ) ) {
		$rates = array();
	}

	$rates['linen_change'] = somvio_normalize_linen_rate( $rates['linen_change'] ?? null );

	if ( empty( $rates['time_slots'] ) || ! is_array( $rates['time_slots'] ) ) {
		$rates['time_slots'] = somvio_quote_default_time_slots();
	}

	if ( empty( $rates['bedroom_base'] ) || ! is_array( $rates['bedroom_base'] ) ) {
		$rates['bedroom_base'] = somvio_quote_default_bedroom_base();
	}

	if ( ! isset( $rates['symbol'] ) || '' === (string) $rates['symbol'] ) {
		$rates['symbol'] = '£';
	}

	$rates['bathroom_extra'] = max( 0.0, (float) ( $rates['bathroom_extra'] ?? 0 ) );

	return $rates;
}

/**
 * Positive multiplier or fallback (empty/zero/NaN → 1).
 *
 * @param mixed $value    Raw multiplier.
 * @param float $fallback Fallback.
 * @return float
 */
function somvio_quote_positive_mult( $value, $fallback = 1.0 ) {
	$fallback = (float) $fallback;
	if ( ! is_numeric( $value ) ) {
		return $fallback;
	}

	$mult = (float) $value;

	return ( is_finite( $mult ) && $mult > 0 ) ? $mult : $fallback;
}

/**
 * Non-negative money amount (invalid → 0).
 *
 * @param mixed $value Raw amount.
 * @return float
 */
function somvio_quote_nonneg_money( $value ) {
	if ( ! is_numeric( $value ) ) {
		return 0.0;
	}

	$amount = (float) $value;
	if ( ! is_finite( $amount ) || $amount < 0 ) {
		return 0.0;
	}

	return $amount;
}

/**
 * Drop cached quote rates (call after ACF / options save).
 *
 * @return void
 */
function somvio_flush_quote_rates_cache() {
	delete_transient( 'somvio_quote_rates_v5' );
	delete_transient( 'somvio_quote_rates_v6' );
	delete_transient( 'somvio_quote_rates_v7' );
	delete_transient( 'somvio_quote_rates_v8' );
	delete_transient( 'somvio_quote_rates_v9' );
	delete_transient( 'somvio_quote_rates_v10' );
	delete_transient( 'somvio_quote_rates_v11' );
	delete_transient( 'somvio_quote_rates_v12' );
	delete_transient( somvio_quote_rates_cache_key() );
	wp_cache_delete( somvio_quote_rates_cache_key(), 'somvio' );
}

/**
 * Preview / server rate table (GBP). Client totals are UI-only.
 *
 * @return array<string, mixed>
 */
function somvio_get_quote_rates() {
	static $memo = null;

	if ( is_array( $memo ) && somvio_quote_rates_shape_is_valid( $memo ) ) {
		return somvio_normalize_quote_rates( $memo );
	}

	$cache_key = somvio_quote_rates_cache_key();
	$cached    = wp_cache_get( $cache_key, 'somvio' );

	if ( false === $cached ) {
		$cached = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) && somvio_quote_rates_shape_is_valid( $cached ) ) {
			wp_cache_set( $cache_key, $cached, 'somvio', HOUR_IN_SECONDS );
		}
	}

	if ( false !== $cached && is_array( $cached ) ) {
		if ( ! somvio_quote_rates_shape_is_valid( $cached ) ) {
			delete_transient( $cache_key );
			wp_cache_delete( $cache_key, 'somvio' );
		} else {
			$memo = somvio_normalize_quote_rates( $cached );

			return $memo;
		}
	}

	$rates = array(
		'currency'         => 'GBP',
		'symbol'           => '£',
		/* Placeholder rates — replace when client confirms final pricing. */
		'bedroom_base'     => somvio_quote_default_bedroom_base(),
		'bathroom_extra'   => 10,
		'linen_change'     => 14,
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
		'time_slots'       => somvio_quote_default_time_slots(),
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
				'label' => __( 'Inside Kitchen Cupboards (must be empty)', 'somvio' ),
				'price' => 39,
				'icon'  => 'icon-addon-cupboards.svg',
			),
			'washing-machine'           => array(
				'label' => __( 'Inside Washing Machine', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-washing.svg',
			),
			'dishwasher'                => array(
				'label' => __( 'Inside Dishwasher', 'somvio' ),
				'price' => 20,
				'icon'  => 'icon-addon-dishwasher.svg',
			),
			'tumble-dryer'              => array(
				'label' => __( 'Inside Tumble Dryer', 'somvio' ),
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
				'qty'   => true,
				'unit'  => __( 'room', 'somvio' ),
			),
			'venetian-blinds'           => array(
				'label' => __( 'Venetian Blinds (per window)', 'somvio' ),
				'price' => 12,
				'icon'  => 'icon-addon-blinds.svg',
				'qty'   => true,
				'unit'  => __( 'window', 'somvio' ),
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
	$rates = somvio_normalize_quote_rates( $rates );

	set_transient( somvio_quote_rates_cache_key(), $rates, HOUR_IN_SECONDS );
	wp_cache_set( somvio_quote_rates_cache_key(), $rates, 'somvio', HOUR_IN_SECONDS );
	$memo = $rates;

	return $rates;
}

/**
 * Format a GBP amount the same way as the live sticky total (two decimals).
 *
 * @param float $amount Amount.
 * @return string
 */
function somvio_format_money( $amount ) {
	$rates  = function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array();
	$symbol = isset( $rates['symbol'] ) ? (string) $rates['symbol'] : '£';

	return $symbol . number_format( (float) $amount, 2, '.', '' );
}

/**
 * Convert a GBP major-unit amount to integer pence.
 *
 * @param mixed $amount Major units.
 * @return int
 */
function somvio_money_to_cents( $amount ) {
	if ( ! is_numeric( $amount ) ) {
		return 0;
	}

	$major = (float) $amount;
	if ( ! is_finite( $major ) ) {
		return 0;
	}

	return (int) round( $major * 100 );
}

/**
 * Marketing "From £X" label from a live calculated amount.
 *
 * @param float $amount Amount.
 * @return string
 */
function somvio_format_from_price( $amount ) {
	$rates  = function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array();
	$symbol = isset( $rates['symbol'] ) ? (string) $rates['symbol'] : '£';
	$amount = (float) $amount;
	$whole  = abs( $amount - round( $amount ) ) < 0.001;
	$formatted = $whole
		? $symbol . number_format( $amount, 0, '.', '' )
		: $symbol . number_format( $amount, 2, '.', '' );

	return sprintf(
		/* translators: %s: formatted money amount, e.g. £55 */
		__( 'From %s', 'somvio' ),
		$formatted
	);
}

/**
 * Cheapest 1-bed / 1-bath total for a service (no add-ons).
 *
 * @param string $service Service key.
 * @return float
 */
function somvio_get_service_starting_price( $service ) {
	$service = sanitize_key( (string) $service );
	$house   = somvio_calculate_quote_price( $service, 'house', 1, 1 );
	$apt     = somvio_calculate_quote_price( $service, 'apartment', 1, 1 );

	return min( $house, $apt );
}

/**
 * Display price for a bedroom count on a service (1 bathroom, no add-ons).
 *
 * @param string $service  Service key.
 * @param int    $bedrooms Bedroom count 1–5.
 * @param string $property house|apartment.
 * @return float
 */
function somvio_get_bedroom_display_price( $service, $bedrooms, $property = 'house' ) {
	return somvio_calculate_quote_price(
		sanitize_key( (string) $service ),
		sanitize_key( (string) $property ),
		(int) $bedrooms,
		1
	);
}

/**
 * Resolve calculator service key from ACF, slug, or title.
 *
 * @param int $post_id Page ID. 0 = current.
 * @return string
 */
function somvio_get_current_service_key( $post_id = 0 ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		$post_id = (int) get_the_ID();
	}

	$options = somvio_get_quote_service_options();

	if ( $post_id > 0 && function_exists( 'get_field' ) ) {
		$acf_key = sanitize_key( (string) get_field( 'somvio_service_key', $post_id ) );
		if ( '' !== $acf_key && isset( $options[ $acf_key ] ) ) {
			return $acf_key;
		}
	}

	if ( $post_id > 0 ) {
		$slug = sanitize_key( (string) get_post_field( 'post_name', $post_id ) );
		if ( '' !== $slug && isset( $options[ $slug ] ) ) {
			return $slug;
		}

		return somvio_quote_service_key_from_title( (string) get_the_title( $post_id ) );
	}

	return 'regular-cleaning';
}

/**
 * JSON for a data-somvio-rates attribute (same table as wp_localize_script).
 *
 * @param array<string, mixed>|null $rates Rate table.
 * @return string
 */
function somvio_quote_rates_data_attr( $rates = null ) {
	if ( ! is_array( $rates ) ) {
		$rates = somvio_get_quote_rates();
	}

	$json = wp_json_encode( $rates );

	return is_string( $json ) ? $json : '{}';
}

/**
 * Whether an addon definition uses quantity pricing.
 *
 * @param mixed $def Addon definition.
 * @return bool
 */
function somvio_addon_is_qty( $def ) {
	return is_array( $def ) && ! empty( $def['qty'] );
}

/**
 * Sanitize addon quantity map (key => 1–10).
 *
 * @param mixed $value Raw request value.
 * @return array<string, int>
 */
function somvio_rest_sanitize_addon_quantities( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$out = array();
	foreach ( $value as $key => $qty ) {
		$key = sanitize_key( (string) $key );
		$qty = absint( $qty );
		if ( '' === $key || $qty < 1 || $qty > 10 ) {
			continue;
		}
		$out[ $key ] = $qty;
	}

	return $out;
}

/**
 * Access / entry method options for booking checkout.
 *
 * @return array<string, string>
 */
function somvio_get_access_method_options() {
	$options = array(
		'at-home'              => __( "I'll be at home", 'somvio' ),
		'call-me'              => __( 'Please call me', 'somvio' ),
		'key-neighbour-hidden' => __( 'The key will be with neighbour or hidden', 'somvio' ),
		'other'                => __( 'Other', 'somvio' ),
	);

	/**
	 * Filter access method options.
	 *
	 * @param array<string, string> $options Key => label.
	 */
	$filtered = apply_filters( 'somvio_access_method_options', $options );

	return is_array( $filtered ) ? $filtered : $options;
}

/**
 * Bedroom/size count used for bedroom_base pricing.
 *
 * After Builders sizes the job from Rooms (Living, Bed, Dining), not a separate bedrooms counter.
 *
 * @param string $service    Service key.
 * @param int    $bedrooms   Bedroom count.
 * @param int    $main_rooms Main rooms count.
 * @return int
 */
function somvio_quote_price_size_count( $service, $bedrooms, $main_rooms = 0 ) {
	$service = sanitize_key( (string) $service );
	if ( 'after-builders' === $service ) {
		return max( 1, min( 5, absint( $main_rooms ) ) );
	}

	return max( 1, min( 5, absint( $bedrooms ) ) );
}

/**
 * Whether the service exposes a Bedrooms counter (After Builders uses Rooms only).
 *
 * @param string $service Service key.
 * @return bool
 */
function somvio_quote_service_uses_bedrooms( $service ) {
	return 'after-builders' !== sanitize_key( (string) $service );
}

/**
 * Airbnb linen-change surcharge (£14 per change by default).
 *
 * @param string $service        Service key.
 * @param int    $linen_changes  Linen change count.
 * @return float
 */
function somvio_quote_linen_total( $service, $linen_changes ) {
	if ( 'airbnb-cleaning' !== sanitize_key( (string) $service ) ) {
		return 0.0;
	}

	$rates = somvio_get_quote_rates();
	$rate  = somvio_normalize_linen_rate( $rates['linen_change'] ?? null );
	$qty   = max( 0, min( 10, absint( $linen_changes ) ) );

	return round( $rate * $qty, 2 );
}

/**
 * Recalculate quote total from trusted inputs (server authority).
 *
 * @param string               $service          Service key.
 * @param string               $property         Property key.
 * @param int                  $bedrooms         Bedroom count.
 * @param int                  $bathrooms        Bathroom count.
 * @param string[]             $addons           Selected add-on keys.
 * @param array<string, int>   $addon_quantities Qty map for per-unit addons.
 * @param int                  $linen_changes    Airbnb linen changes.
 * @param int                  $main_rooms       After Builders rooms (living/bed/dining).
 * @return float
 */
function somvio_calculate_quote_price( $service, $property, $bedrooms, $bathrooms, $addons = array(), $addon_quantities = array(), $linen_changes = 0, $main_rooms = 0 ) {
	$rates = somvio_get_quote_rates();
	$service = sanitize_key( (string) $service );

	$bed_key = (string) somvio_quote_price_size_count( $service, $bedrooms, $main_rooms );
	if ( isset( $rates['bedroom_base'][ $bed_key ] ) ) {
		$base = somvio_quote_nonneg_money( $rates['bedroom_base'][ $bed_key ] );
	} elseif ( isset( $rates['bedroom_base']['1'] ) ) {
		$base = somvio_quote_nonneg_money( $rates['bedroom_base']['1'] );
	} else {
		$base = 0.0;
	}

	$bath_extra = max( 0, absint( $bathrooms ) - 1 ) * somvio_quote_nonneg_money( $rates['bathroom_extra'] ?? 0 );
	$svc_mult   = isset( $rates['service_mult'][ $service ] )
		? somvio_quote_positive_mult( $rates['service_mult'][ $service ] )
		: 1.0;
	$prop_mult  = isset( $rates['property_mult'][ $property ] )
		? somvio_quote_positive_mult( $rates['property_mult'][ $property ] )
		: 1.0;

	$addon_total = 0.0;
	$addon_defs  = isset( $rates['addons'] ) && is_array( $rates['addons'] ) ? $rates['addons'] : array();
	$quantities  = is_array( $addon_quantities ) ? $addon_quantities : array();

	foreach ( (array) $addons as $addon_key ) {
		$addon_key = sanitize_key( (string) $addon_key );
		if ( ! isset( $addon_defs[ $addon_key ] ) || ! is_array( $addon_defs[ $addon_key ] ) ) {
			continue;
		}
		if ( somvio_addon_is_qty( $addon_defs[ $addon_key ] ) ) {
			/* Qty addons priced via $quantities; bare key implies qty 1 (quote toggle compat). */
			if ( ! isset( $quantities[ $addon_key ] ) ) {
				$quantities[ $addon_key ] = 1;
			}
			continue;
		}
		if ( isset( $addon_defs[ $addon_key ]['price'] ) ) {
			$addon_total += somvio_quote_nonneg_money( $addon_defs[ $addon_key ]['price'] );
		}
	}

	foreach ( $quantities as $addon_key => $qty ) {
		$addon_key = sanitize_key( (string) $addon_key );
		$qty       = absint( $qty );
		if ( $qty < 1 || ! isset( $addon_defs[ $addon_key ] ) || ! somvio_addon_is_qty( $addon_defs[ $addon_key ] ) ) {
			continue;
		}
		$unit = isset( $addon_defs[ $addon_key ]['price'] ) ? somvio_quote_nonneg_money( $addon_defs[ $addon_key ]['price'] ) : 0.0;
		$addon_total += $unit * min( 10, $qty );
	}

	$linen_total = somvio_quote_linen_total( $service, $linen_changes );

	return round( ( ( $base + $bath_extra ) * $svc_mult * $prop_mult ) + $addon_total + $linen_total, 2 );
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
	$raw     = strtolower( trim( (string) $title ) );
	$options = somvio_get_quote_service_options();
	$slug    = sanitize_key( str_replace( ' ', '-', $raw ) );
	if ( '' !== $slug && isset( $options[ $slug ] ) ) {
		return $slug;
	}

	$map = array(
		'regular cleaning' => 'regular-cleaning',
		'deep cleaning'    => 'deep-cleaning',
		'end of tenancy'   => 'end-of-tenancy',
		'airbnb cleaning'  => 'airbnb-cleaning',
		'after builders'   => 'after-builders',
	);

	return isset( $map[ $raw ] ) ? $map[ $raw ] : 'regular-cleaning';
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

	// Quote modal + on-page calculators need rates on most public views.
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

	if ( ! somvio_enqueue_theme_script( 'somvio-quote-calculator', 'assets/js/quote-calculator.js' ) ) {
		return;
	}

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
	$frequency      = sanitize_key( (string) ( $request['frequency'] ?? '' ) );
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
	$addon_quantities = somvio_rest_sanitize_addon_quantities( $request['addon_quantities'] ?? array() );
	$access_method    = sanitize_key( (string) ( $request['access_method'] ?? '' ) );
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
	if ( ! somvio_quote_service_uses_bedrooms( $service ) ) {
		$bedrooms = 0;
	} elseif ( $bedrooms < 1 || $bedrooms > 5 ) {
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
	if ( 'regular-cleaning' === $service ) {
		if ( ! in_array( $frequency, array( 'weekly', 'fortnightly' ), true ) ) {
			$frequency = 'weekly';
		}
	} else {
		$frequency = '';
	}
	if ( ! somvio_is_valid_quote_date( $date ) ) {
		return new WP_Error( 'invalid_date', __( 'Invalid date.', 'somvio' ), array( 'status' => 400 ) );
	}
	$slots = ( isset( $rates['time_slots'] ) && is_array( $rates['time_slots'] ) )
		? $rates['time_slots']
		: somvio_quote_default_time_slots();
	if ( ! in_array( $time, $slots, true ) ) {
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
		if ( function_exists( 'somvio_validate_postcode' ) && function_exists( 'somvio_extract_uk_postcode_from_text' ) ) {
			$extracted = somvio_extract_uk_postcode_from_text( $address );
			if ( '' === $extracted ) {
				return new WP_Error(
					'invalid_postcode',
					__( 'Enter a valid UK postcode.', 'somvio' ),
					array( 'status' => 400 )
				);
			}

			$postcode = somvio_validate_postcode( $extracted );
			if ( empty( $postcode['valid'] ) ) {
				$pc_message = isset( $postcode['message'] ) && '' !== (string) $postcode['message']
					? (string) $postcode['message']
					: __( 'Sorry, we do not cover this area yet', 'somvio' );

				return new WP_Error( 'invalid_postcode', $pc_message, array( 'status' => 400 ) );
			}
		}
		if ( ! $terms ) {
			return new WP_Error( 'terms_required', __( 'Please accept the Terms & Conditions and Privacy Policy.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( ! in_array( $payment_method, array( 'cash', 'online' ), true ) ) {
			return new WP_Error( 'invalid_payment', __( 'Please select a payment method.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( 'online' === $payment_method && function_exists( 'somvio_stripe_is_configured' ) && ! somvio_stripe_is_configured() ) {
			return new WP_Error(
				'stripe_keys_missing',
				__( 'Stripe API keys are missing. Cannot process online payment.', 'somvio' ),
				array( 'status' => 400 )
			);
		}
		$access_options = somvio_get_access_method_options();
		if ( '' === $access_method || ! isset( $access_options[ $access_method ] ) ) {
			return new WP_Error(
				'invalid_access_method',
				__( 'Please select how we will get in.', 'somvio' ),
				array( 'status' => 400 )
			);
		}
	} else {
		// Quick quote has no payment step.
		$payment_method = 'cash';
		$access_method  = '';
	}

	$extras_services = isset( $rates['extras_services'] ) && is_array( $rates['extras_services'] )
		? $rates['extras_services']
		: array( 'deep-cleaning', 'end-of-tenancy', 'after-builders' );
	if ( ! in_array( $service, $extras_services, true ) ) {
		$addons           = array();
		$addon_quantities = array();
	}

	foreach ( $addons as $addon_key ) {
		if ( ! isset( $addon_defs[ $addon_key ] ) ) {
			return new WP_Error( 'invalid_addon', __( 'Invalid add-on selection.', 'somvio' ), array( 'status' => 400 ) );
		}
	}

	foreach ( $addon_quantities as $qty_key => $qty_val ) {
		unset( $qty_val );
		if ( ! isset( $addon_defs[ $qty_key ] ) || ! somvio_addon_is_qty( $addon_defs[ $qty_key ] ) ) {
			return new WP_Error( 'invalid_addon', __( 'Invalid add-on selection.', 'somvio' ), array( 'status' => 400 ) );
		}
		if ( ! in_array( $qty_key, $addons, true ) ) {
			$addons[] = $qty_key;
		}
	}

	$server_total = somvio_calculate_quote_price( $service, $property, $bedrooms, $bathrooms, $addons, $addon_quantities, $linen_changes, $main_rooms );
	$client_total = isset( $request['client_total'] ) ? (float) $request['client_total'] : null;

	if ( null !== $client_total && ! is_finite( $client_total ) ) {
		return new WP_Error( 'invalid_total', __( 'Invalid estimated total.', 'somvio' ), array( 'status' => 400 ) );
	}
	if ( null !== $client_total ) {
		$server_cents = somvio_money_to_cents( $server_total );
		$client_cents = somvio_money_to_cents( $client_total );
		if ( abs( $client_cents - $server_cents ) > 1 ) {
			return new WP_Error(
				'price_mismatch',
				__( 'Price changed. Review the new total.', 'somvio' ),
				array(
					'status' => 409,
					'total'  => $server_total,
				)
			);
		}
	}

	if ( 'booking' === $source && ( ! is_finite( $server_total ) || somvio_money_to_cents( $server_total ) < 50 ) ) {
		return new WP_Error(
			'invalid_total',
			__( 'Unable to price this booking. Please contact us.', 'somvio' ),
			array( 'status' => 400 )
		);
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'somvio_quote_rl_' . md5( $ip . '|' . strtolower( $email ) );
	if ( get_transient( $rate_key ) ) {
		return new WP_Error(
			'rate_limited',
			__( 'Please wait before submitting again.', 'somvio' ),
			array( 'status' => 429 )
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
		'frequency'       => $frequency,
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
		'addon_quantities'=> $addon_quantities,
		'access_method'   => $access_method,
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

	set_transient( $rate_key, 1, 60 );

	$response = array(
		'success'    => true,
		'total'      => $server_total,
		'symbol'     => isset( $rates['symbol'] ) && '' !== (string) $rates['symbol'] ? (string) $rates['symbol'] : '£',
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
			$response['message'] = __( 'Please complete your online payment to confirm the booking.', 'somvio' );
		} else {
			$response['requires_payment'] = true;
			$response['payment_error']    = (string) ( $payment['error'] ?? 'stripe_failed' );
			$response['message']          = ! empty( $payment['message'] )
				? (string) $payment['message']
				: __( 'Online payment could not be started. Please try again or choose pay on completion.', 'somvio' );
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
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
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
				'addon_quantities' => array(
					'required'          => false,
					'type'              => 'object',
					'default'           => array(),
					'sanitize_callback' => 'somvio_rest_sanitize_addon_quantities',
				),
				'access_method'   => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_key',
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
				'frequency'       => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
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
