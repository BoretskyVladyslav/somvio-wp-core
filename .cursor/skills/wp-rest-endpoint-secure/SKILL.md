---
name: wp-rest-endpoint-secure
description: >-
  Somvio WordPress REST security pattern: register_rest_route with nonce via
  permission_callback, capability checks, sanitize on input, escape on output,
  and transients for cacheable non-authoritative reads. Use when adding or
  editing a WP REST route, AJAX/Fetch handler, booking/quote API, wp_localize_script
  nonce, permission_callback, set_transient/get_transient, or when the user
  mentions secure endpoint, REST nonce, or "prefer register_rest_route over
  admin-ajax".
disable-model-invocation: false
---

# Secure WP REST endpoint (Somvio)

## Status in this theme (verify before citing)

`somvio-child` currently has **no** custom `register_rest_route()` calls.
Security baseline that **does** exist: sanitize on input paths, escape on
HTML/URL output (see references below). When adding REST, follow this skill and
`.cursor/rules/wordpress-mdc.mdc`:

- Prefer `register_rest_route` over `admin-ajax.php`
- Nonces + capability checks on every mutating/ privileged request
- Sanitize all input; escape all output
- Client prices are UI-only — server recalculates (see global skill
  `booking-calculator-pattern`)

## Real security references already in the theme

### Sanitize + path containment (`functions.php`)

```php
function somvio_get_icon( $name ) {
	$name = sanitize_file_name( (string) $name );
	$name = preg_replace( '/\.svg$/i', '', $name );

	if ( ! is_string( $name ) || '' === $name ) {
		return '';
	}

	$icons_dir = realpath( get_stylesheet_directory() . '/assets/icons' );
	$path      = realpath( get_stylesheet_directory() . '/assets/icons/' . $name . '.svg' );

	if ( false === $icons_dir || false === $path || 0 !== strpos( $path, $icons_dir ) ) {
		return '';
	}

	$svg = file_get_contents( $path );
	return false === $svg ? '' : $svg;
}
```

Pattern: sanitize → validate → constrain to an allowed root → then read.

### Escape on output (templates)

```php
// inc/header.php
return esc_url( apply_filters( 'somvio_book_now_url', home_url( '/booking/' ) ) );

// template-parts/sections/services-grid.php
echo esc_html( $service['title'] );
echo esc_html( $service['price'] );
esc_html_e( 'View All Services', 'somvio' );
```

REST JSON responses: return plain scalars/arrays via `rest_ensure_response()`
(WP encodes JSON). Still sanitize **before** compute/store. Escape when the same
data is later printed into HTML.

## Canonical `register_rest_route` shape

Put handlers under `inc/` by responsibility (e.g. future `inc/calculator/rest.php`).
Bootstrap from `functions.php` with `require_once` only — no logic in bootstrap.

```php
add_action( 'rest_api_init', 'somvio_register_rest_routes' );

function somvio_register_rest_routes() {
	register_rest_route(
		'somvio/v1',
		'/booking/confirm',
		array(
			'methods'             => WP_REST_Server::CREATABLE, // POST
			'callback'            => 'somvio_rest_confirm_booking',
			'permission_callback' => 'somvio_rest_can_confirm_booking',
			'args'                => array(
				'service' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
				'rooms'   => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'addons'  => array(
					'required'          => false,
					'type'              => 'array',
					'default'           => array(),
					'sanitize_callback' => 'somvio_rest_sanitize_string_list',
				),
			),
		)
	);
}

/**
 * Cookie + REST nonce for logged-in or public forms that use wp_localize_script.
 * For public booking: still require a valid nonce; add capability only if needed.
 */
function somvio_rest_can_confirm_booking( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'rest_forbidden', __( 'Invalid nonce.', 'somvio' ), array( 'status' => 403 ) );
	}

	// Example privileged route — uncomment / adapt when not public:
	// if ( ! current_user_can( 'manage_options' ) ) {
	//     return new WP_Error( 'rest_forbidden', __( 'Forbidden.', 'somvio' ), array( 'status' => 403 ) );
	// }

	return true;
}

function somvio_rest_sanitize_string_list( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'sanitize_key', $value ) ) );
}

function somvio_rest_confirm_booking( WP_REST_Request $request ) {
	$service = sanitize_key( (string) $request['service'] );
	$rooms   = absint( $request['rooms'] );
	$addons  = somvio_rest_sanitize_string_list( $request['addons'] );

	// Re-validate business rules + recalculate price on the server.
	$total = somvio_calculate_price( $service, $rooms, $addons );

	return rest_ensure_response(
		array(
			'total'   => $total,
			'service' => $service,
			'rooms'   => $rooms,
		)
	);
}
```

### Wire nonce to the client

```php
wp_localize_script(
	'somvio-calculator',
	'somvioCalc',
	array(
		'restUrl' => esc_url_raw( rest_url( 'somvio/v1/booking/confirm' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	)
);
```

```js
await fetch(somvioCalc.restUrl, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': somvioCalc.nonce,
  },
  body: JSON.stringify(payload),
});
```

`permission_callback` must not be `__return_true` on routes that mutate data or
expose private info without an explicit public-read rationale.

## Transients — cache reads, not booking writes

Use transients for **derived / infrequently changing** data that should not hit
the DB on every request (rate tables, public service catalog snapshots). Do
**not** use transients as the system of record for bookings or payments.

```php
function somvio_get_rate_table() {
	$cached = get_transient( 'somvio_rate_table_v1' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$rates = somvio_load_rate_table_from_source(); // ACF / options — still sanitize
	set_transient( 'somvio_rate_table_v1', $rates, HOUR_IN_SECONDS );
	return $rates;
}

// After admin updates rates:
delete_transient( 'somvio_rate_table_v1' );
```

## Checklist for every new route

- [ ] Namespace `somvio/v1`, prefixed callbacks `somvio_rest_*`
- [ ] `permission_callback` verifies `wp_rest` nonce (and capability when needed)
- [ ] `args` + `sanitize_callback` (and/or manual sanitize in callback)
- [ ] Business validation + server-side price calc before persist
- [ ] `rest_ensure_response` / `WP_Error` with proper HTTP status
- [ ] Transients only for cacheable reads; invalidate on write
- [ ] No secrets in client-localized data
- [ ] Confirm the file/function exists before referencing it elsewhere

## Related

- Price authority / calculator modules: global skill `booking-calculator-pattern`
- Theme rules: `.cursor/rules/wordpress-mdc.mdc`
