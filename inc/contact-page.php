<?php
/**
 * Contact Us page — detection, helpers, form REST, assets.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the Contact Us page.
 *
 * @return bool
 */
function somvio_is_contact_page() {
	if ( is_page( 'contact' ) || is_page( 'contacts' ) || is_page( 'contact-us' ) ) {
		return true;
	}

	if ( is_page_template( 'page-contact.php' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_contact_page', false );
}

/**
 * Permalink for the Contact Us page.
 *
 * @return string
 */
function somvio_get_contact_url() {
	$page_id = function_exists( 'somvio_get_page_id_by_slug' )
		? (int) somvio_get_page_id_by_slug( 'contact' )
		: 0;

	if ( $page_id <= 0 && function_exists( 'somvio_get_page_id_by_slug' ) ) {
		$page_id = (int) somvio_get_page_id_by_slug( 'contacts' );
	}

	if ( $page_id > 0 ) {
		$url = get_permalink( $page_id );
		if ( $url ) {
			return (string) $url;
		}
	}

	return home_url( '/contact/' );
}

/**
 * Working hours lines (filterable).
 *
 * @return string[]
 */
function somvio_get_working_hours() {
	$hours = apply_filters(
		'somvio_working_hours',
		array(
			__( 'Mon–Sat: 08:00–20:00', 'somvio' ),
			__( 'Sun: Closed', 'somvio' ),
		)
	);

	if ( ! is_array( $hours ) ) {
		return array();
	}

	$clean = array();
	foreach ( $hours as $line ) {
		$line = trim( (string) $line );
		if ( '' !== $line ) {
			$clean[] = $line;
		}
	}

	return $clean;
}

/**
 * Google Maps embed URL for the service area (filterable).
 *
 * @return string
 */
function somvio_get_contact_map_embed_url() {
	$location = function_exists( 'somvio_get_location' )
		? somvio_get_location()
		: __( 'Glasgow, United Kingdom', 'somvio' );

	$url = add_query_arg(
		array(
			'q'      => $location,
			'z'      => '11',
			'output' => 'embed',
		),
		'https://maps.google.com/maps'
	);

	return esc_url( apply_filters( 'somvio_contact_map_embed_url', $url ) );
}

/**
 * Mark Contact so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_contact_body_class( $classes ) {
	if ( somvio_is_contact_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_contact_body_class' );

/**
 * Ensure the Contact page exists with the Contact Us template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_contact_page() {
	$page_id = function_exists( 'somvio_ensure_page' )
		? somvio_ensure_page( 'contact', 'Contact' )
		: 0;

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post && 'publish' !== $page->post_status ) {
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-contact.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-contact.php' );
	}

	return $page_id;
}

/**
 * Enqueue contact form script on the Contact page only.
 *
 * @return void
 */
function somvio_enqueue_contact_form_assets() {
	if ( ! somvio_is_contact_page() ) {
		return;
	}

	if ( ! somvio_enqueue_theme_script( 'somvio-contact-form', 'assets/js/contact-form.js' ) ) {
		return;
	}

	wp_localize_script(
		'somvio-contact-form',
		'somvioContactForm',
		array(
			'restUrl' => esc_url_raw( rest_url( 'somvio/v1/contact/submit' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'i18n'    => array(
				'required'     => __( 'Please complete the required fields.', 'somvio' ),
				'invalidEmail'  => __( 'Please enter a valid email address.', 'somvio' ),
				'invalidPhone'  => __( 'Please enter a valid phone number.', 'somvio' ),
				'invalidName'   => __( 'Please enter your name.', 'somvio' ),
				'invalidMsg'    => __( 'Please enter a message.', 'somvio' ),
				'requiredField' => __( 'This field is required.', 'somvio' ),
				'submitting'    => __( 'Sending…', 'somvio' ),
				'submit'        => __( 'Send', 'somvio' ),
				'termsRequired' => __( 'Please accept the Terms & Conditions and Privacy Policy.', 'somvio' ),
				'success'       => __( 'Thanks — your message has been sent. We’ll get back to you shortly.', 'somvio' ),
				'successTitle'  => __( 'Thank You!', 'somvio' ),
				'successText'   => __( 'Your message has been sent.', 'somvio' ),
				'submitError'   => __( 'Something went wrong. Please try again.', 'somvio' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_contact_form_assets' );

/**
 * REST permission: valid wp_rest nonce (public contact form).
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function somvio_rest_can_submit_contact( WP_REST_Request $request ) {
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
 * Handle contact form submit — sanitize, honeypot, email admin.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function somvio_rest_submit_contact( WP_REST_Request $request ) {
	$honeypot = sanitize_text_field( (string) $request->get_param( 'company_website' ) );
	if ( '' !== $honeypot ) {
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Thanks — your message has been sent.', 'somvio' ),
			)
		);
	}

	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

	if ( '' === $name ) {
		return new WP_Error( 'invalid_name', __( 'Please enter your name.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( '' === $email || ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( '' === $phone ) {
		return new WP_Error( 'invalid_phone', __( 'Please enter a valid phone number.', 'somvio' ), array( 'status' => 400 ) );
	}

	if ( '' === $message ) {
		return new WP_Error( 'invalid_message', __( 'Please enter a message.', 'somvio' ), array( 'status' => 400 ) );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'somvio_contact_rl_' . md5( $ip . '|' . strtolower( $email ) );
	if ( get_transient( $rate_key ) ) {
		return new WP_Error(
			'rate_limited',
			__( 'Please wait a moment before sending another message.', 'somvio' ),
			array( 'status' => 429 )
		);
	}

	$to_email = function_exists( 'somvio_get_email' )
		? (string) somvio_get_email()['href']
		: 'mailto:Info@somvio.co.uk';
	$to_email = preg_replace( '/^mailto:/i', '', $to_email );
	$to_email = sanitize_email( (string) $to_email );

	if ( ! is_email( $to_email ) ) {
		$to_email = get_option( 'admin_email' );
	}

	$subject = sprintf(
		/* translators: %s: sender name */
		__( '[Somvio Contact] Message from %s', 'somvio' ),
		$name
	);

	$body_lines = array(
		__( 'New contact form submission', 'somvio' ),
		'',
		sprintf( /* translators: %s: name */ __( 'Name: %s', 'somvio' ), $name ),
		sprintf( /* translators: %s: email */ __( 'Email: %s', 'somvio' ), $email ),
		sprintf( /* translators: %s: phone */ __( 'Phone: %s', 'somvio' ), $phone ),
		'',
		__( 'Message:', 'somvio' ),
		$message,
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	);

	$sent = wp_mail( $to_email, $subject, implode( "\n", $body_lines ), $headers );

	if ( ! $sent ) {
		return new WP_Error(
			'mail_failed',
			__( 'Something went wrong. Please try again.', 'somvio' ),
			array( 'status' => 500 )
		);
	}

	set_transient( $rate_key, 1, 60 );

	return rest_ensure_response(
		array(
			'success' => true,
			'message' => __( 'Thanks — your message has been sent. We’ll get back to you shortly.', 'somvio' ),
		)
	);
}

/**
 * Register contact form REST route.
 *
 * @return void
 */
function somvio_register_contact_rest_routes() {
	register_rest_route(
		'somvio/v1',
		'/contact/submit',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'somvio_rest_submit_contact',
			'permission_callback' => 'somvio_rest_can_submit_contact',
			'args'                => array(
				'name'             => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'email'            => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_email',
				),
				'phone'            => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'message'          => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_textarea_field',
				),
				'company_website'  => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'somvio_register_contact_rest_routes' );
