---
name: wp-postcode-validator
description: "Логіка валідації британських поштових індексів (Postcode) для калькулятора Somvio."
paths: "**/*.php, **/*.js"
---

# UK postcode validator (Somvio calculator)

No postcode handler exists in this theme yet. Implement under `inc/` + `assets/js/`. Do not invent a live zone list — zones come from filter `somvio_allowed_postcode_zones`.

## Match rule

1. `sanitize_text_field` → uppercase → strip spaces.
2. UK shape: outward + inward. Inward = last 3 chars (`9AA`). Outward = the rest (`G20`, `EH1`, `G1`).
3. A zone in `$allowed_zones` matches if outward **equals** the zone or **starts with** it (`G20` matches `G20`; `EH` matches `EH1` / `EH12`).
4. JSON only — no HTML.

Success: `{ "valid": true, "postcode": "G208NN", "prefix": "G20" }`
Fail: `{ "valid": false, "postcode": "...", "prefix": "...", "message": "..." }`

## PHP

```php
function somvio_get_allowed_postcode_zones() {
	$zones = array( 'G20', 'EH' ); // placeholder — replace via filter, do not invent coverage

	/**
	 * @param string[] $zones Allowed outward prefixes (e.g. G20, EH).
	 */
	$zones = apply_filters( 'somvio_allowed_postcode_zones', $zones );

	return array_values( array_filter( array_map( 'strtoupper', array_map( 'sanitize_text_field', (array) $zones ) ) ) );
}

function somvio_normalize_uk_postcode( $raw ) {
	$postcode = strtoupper( sanitize_text_field( (string) $raw ) );
	return preg_replace( '/\s+/', '', $postcode );
}

/**
 * @return array{valid:bool,postcode:string,prefix:string,message?:string}
 */
function somvio_validate_postcode( $raw ) {
	$postcode = somvio_normalize_uk_postcode( $raw );
	$prefix   = '';

	if ( ! preg_match( '/^[A-Z]{1,2}[0-9][0-9A-Z]?[0-9][A-Z]{2}$/', $postcode ) ) {
		return array(
			'valid'    => false,
			'postcode' => $postcode,
			'prefix'   => '',
			'message'  => __( 'Enter a valid UK postcode.', 'somvio' ),
		);
	}

	$outward = substr( $postcode, 0, -3 );
	$prefix  = $outward;
	$zones   = somvio_get_allowed_postcode_zones();
	$ok      = false;

	foreach ( $zones as $zone ) {
		if ( $outward === $zone || 0 === strpos( $outward, $zone ) ) {
			$ok = true;
			break;
		}
	}

	if ( ! $ok ) {
		return array(
			'valid'    => false,
			'postcode' => $postcode,
			'prefix'   => $prefix,
			'message'  => __( 'We do not cover this postcode.', 'somvio' ),
		);
	}

	return array(
		'valid'    => true,
		'postcode' => $postcode,
		'prefix'   => $prefix,
	);
}

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
```

Localize: `ajaxUrl` = `admin_url( 'admin-ajax.php' )`, `nonce` = `wp_create_nonce( 'somvio_validate_postcode' )`.

## JS

```js
async function somvioValidatePostcode(postcode, { ajaxUrl, nonce }) {
  const body = new FormData();
  body.append('action', 'somvio_validate_postcode');
  body.append('nonce', nonce);
  body.append('postcode', postcode);

  const res = await fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
  const json = await res.json();
  const data = json.data || {};

  return {
    valid: Boolean(json.success && data.valid),
    postcode: data.postcode || '',
    prefix: data.prefix || '',
    message: data.message || '',
  };
}
```

## Checklist

- [ ] Prefix match against `somvio_allowed_postcode_zones` only
- [ ] `check_ajax_referer( 'somvio_validate_postcode', 'nonce' )`
- [ ] `wp_send_json_success` / `wp_send_json_error` — keys `valid`, `postcode`, `prefix`
- [ ] Confirm `somvio_validate_postcode` exists in the theme before citing it elsewhere
