<?php
/**
 * UK postcode coverage check (Glasgow + suburbs) for the homepage quote start.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed outward prefixes. `G` covers G1–G84; `PA` covers Paisley / Renfrewshire.
 *
 * @return string[]
 */
function somvio_get_allowed_postcode_zones() {
	$zones = array( 'G', 'PA' );

	/**
	 * Filter allowed UK outward prefixes (e.g. G, PA, G20).
	 *
	 * @param string[] $zones Allowed prefixes.
	 */
	$zones = apply_filters( 'somvio_allowed_postcode_zones', $zones );

	return array_values( array_filter( array_map( 'strtoupper', array_map( 'sanitize_text_field', (array) $zones ) ) ) );
}

/**
 * Normalize a UK postcode: sanitize, uppercase, strip spaces.
 *
 * @param string $raw Raw input.
 * @return string
 */
function somvio_normalize_uk_postcode( $raw ) {
	$postcode = strtoupper( sanitize_text_field( (string) $raw ) );

	return (string) preg_replace( '/\s+/', '', $postcode );
}

/**
 * Extract outward code (G20, EH1, G1) from a normalized postcode.
 *
 * Full postcode: last 3 chars are inward. Outward-only (G20, PA) is accepted as-is.
 *
 * @param string $normalized Normalized postcode (no spaces).
 * @return string
 */
function somvio_postcode_outward( $normalized ) {
	$normalized = (string) $normalized;

	if ( preg_match( '/^[A-Z]{1,2}[0-9][0-9A-Z]?[0-9][A-Z]{2}$/', $normalized ) ) {
		return substr( $normalized, 0, -3 );
	}

	if ( preg_match( '/^[A-Z]{1,2}[0-9][0-9A-Z]?$/', $normalized ) ) {
		return $normalized;
	}

	return '';
}

/**
 * Glasgow G-district number from an outward code (G1, G20, G84).
 *
 * @param string $outward Outward code.
 * @return int 0 when not a G district.
 */
function somvio_postcode_g_district_number( $outward ) {
	if ( ! preg_match( '/^G([0-9]{1,2})[A-Z]?$/', (string) $outward, $match ) ) {
		return 0;
	}

	return (int) $match[1];
}

/**
 * Whether outward matches an allowed zone (exact or prefix).
 *
 * Zone `G` is further limited to districts 1–84 (G85+ rejected).
 *
 * @param string $outward Outward code.
 * @param string $zone    Allowed prefix.
 * @return bool
 */
function somvio_postcode_zone_matches( $outward, $zone ) {
	$outward = (string) $outward;
	$zone    = (string) $zone;

	if ( '' === $outward || '' === $zone ) {
		return false;
	}

	if ( $outward === $zone ) {
		if ( 'G' === $zone ) {
			return false;
		}

		return true;
	}

	if ( 0 !== strpos( $outward, $zone ) ) {
		return false;
	}

	$next = substr( $outward, strlen( $zone ), 1 );
	if ( '' === $next || ! ctype_digit( $next ) ) {
		return false;
	}

	if ( 'G' === $zone ) {
		$district = somvio_postcode_g_district_number( $outward );

		return $district >= 1 && $district <= 84;
	}

	return true;
}

/**
 * Display form of a compact UK postcode (space before inward).
 *
 * @param string $raw Raw or compact postcode.
 * @return string
 */
function somvio_format_uk_postcode_display( $raw ) {
	$compact = somvio_normalize_uk_postcode( $raw );
	if ( strlen( $compact ) < 5 ) {
		return $compact;
	}

	return substr( $compact, 0, -3 ) . ' ' . substr( $compact, -3 );
}

/**
 * Pull a UK postcode (full or outward) out of free text / address.
 *
 * @param string $raw Address or mixed postcode string.
 * @return string Compact postcode or empty.
 */
function somvio_extract_uk_postcode_from_text( $raw ) {
	$normalized = somvio_normalize_uk_postcode( $raw );

	if ( '' === $normalized ) {
		return '';
	}

	if ( preg_match( '/[A-Z]{1,2}[0-9][0-9A-Z]?[0-9][A-Z]{2}/', $normalized, $match ) ) {
		return $match[0];
	}

	if ( preg_match( '/[A-Z]{1,2}[0-9][0-9A-Z]?$/', $normalized, $match ) ) {
		return $match[0];
	}

	return '';
}

/**
 * Validate a UK postcode against allowed Glasgow / suburb zones.
 *
 * @param string $raw Raw postcode.
 * @return array{valid:bool,postcode:string,prefix:string,message?:string}
 */
function somvio_validate_postcode( $raw ) {
	$postcode = somvio_normalize_uk_postcode( $raw );
	$outward  = somvio_postcode_outward( $postcode );

	if ( '' === $postcode || '' === $outward ) {
		return array(
			'valid'    => false,
			'postcode' => $postcode,
			'prefix'   => '',
			'message'  => __( 'Enter a valid UK postcode.', 'somvio' ),
		);
	}

	$ok = false;
	foreach ( somvio_get_allowed_postcode_zones() as $zone ) {
		if ( somvio_postcode_zone_matches( $outward, $zone ) ) {
			$ok = true;
			break;
		}
	}

	if ( ! $ok ) {
		return array(
			'valid'    => false,
			'postcode' => $postcode,
			'prefix'   => $outward,
			'message'  => __( 'Sorry, we do not cover this area yet', 'somvio' ),
		);
	}

	return array(
		'valid'    => true,
		'postcode' => $postcode,
		'prefix'   => $outward,
		'message'  => '',
	);
}

/**
 * AJAX: validate postcode. Success/error JSON with keys valid, postcode, prefix.
 *
 * @return void
 */
function somvio_ajax_validate_postcode() {
	check_ajax_referer( 'somvio_validate_postcode', 'nonce' );

	$result = somvio_validate_postcode( wp_unslash( $_POST['postcode'] ?? '' ) );

	if ( empty( $result['valid'] ) ) {
		wp_send_json_error( $result, 400 );
	}

	wp_send_json_success( $result );
}
add_action( 'wp_ajax_somvio_validate_postcode', 'somvio_ajax_validate_postcode' );
add_action( 'wp_ajax_nopriv_somvio_validate_postcode', 'somvio_ajax_validate_postcode' );

/**
 * Enqueue homepage quote-start script.
 *
 * @return void
 */
function somvio_enqueue_quote_start_assets() {
	if ( ! function_exists( 'somvio_is_hero_page' ) || ! somvio_is_hero_page() ) {
		return;
	}

	if ( ! somvio_enqueue_theme_script( 'somvio-quote-start', 'assets/js/quote-start.js' ) ) {
		return;
	}

	wp_localize_script(
		'somvio-quote-start',
		'somvioQuoteStart',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'somvio_validate_postcode' ),
			'bookingUrl' => home_url( '/booking/' ),
			'i18n'       => array(
				'uncovered' => __( 'Sorry, we do not cover this area yet', 'somvio' ),
				'invalid'   => __( 'Enter a valid UK postcode.', 'somvio' ),
				'service'   => __( 'Please select a service.', 'somvio' ),
				'generic'   => __( 'Something went wrong. Please try again.', 'somvio' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_quote_start_assets', 20 );
