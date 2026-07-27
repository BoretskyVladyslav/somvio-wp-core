<?php
/**
 * Booking / quote HTML email notifications (admin + customer).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin notification recipient.
 *
 * @return string
 */
function somvio_get_booking_admin_email() {
	$email = apply_filters( 'somvio_booking_admin_email', 'info@somvio.co.uk' );
	$email = sanitize_email( (string) $email );

	return $email ? $email : 'info@somvio.co.uk';
}

/**
 * Site “from” address for booking mail.
 *
 * @return string
 */
function somvio_get_booking_mail_from_email() {
	$from = apply_filters( 'somvio_booking_mail_from_email', somvio_get_booking_admin_email() );
	$from = sanitize_email( (string) $from );

	return $from ? $from : get_bloginfo( 'admin_email' );
}

/**
 * Site “from” name for booking mail.
 *
 * @return string
 */
function somvio_get_booking_mail_from_name() {
	$name = apply_filters( 'somvio_booking_mail_from_name', get_bloginfo( 'name', 'display' ) );
	$name = sanitize_text_field( (string) $name );

	return $name ? $name : 'Somvio';
}

/**
 * Force HTML content type for booking emails.
 *
 * @return string
 */
function somvio_booking_mail_content_type() {
	return 'text/html; charset=UTF-8';
}

/**
 * Set From headers for booking mail via PHPMailer.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer.
 * @return void
 */
function somvio_booking_phpmailer_init( $phpmailer ) {
	$phpmailer->setFrom(
		somvio_get_booking_mail_from_email(),
		somvio_get_booking_mail_from_name(),
		false
	);
	$phpmailer->Sender = somvio_get_booking_mail_from_email();
}

/**
 * Send an HTML email with booking mail defaults.
 *
 * @param string          $to      Recipient.
 * @param string          $subject Subject.
 * @param string          $html    HTML body.
 * @param string[]|string $headers Extra headers.
 * @return bool
 */
function somvio_send_html_mail( $to, $subject, $html, $headers = array() ) {
	$to = sanitize_email( (string) $to );
	if ( ! $to || ! is_email( $to ) ) {
		return false;
	}

	$headers = (array) $headers;
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = sprintf(
		'From: %s <%s>',
		somvio_get_booking_mail_from_name(),
		somvio_get_booking_mail_from_email()
	);

	add_filter( 'wp_mail_content_type', 'somvio_booking_mail_content_type' );
	add_action( 'phpmailer_init', 'somvio_booking_phpmailer_init' );

	$sent = wp_mail( $to, $subject, $html, $headers );

	remove_action( 'phpmailer_init', 'somvio_booking_phpmailer_init' );
	remove_filter( 'wp_mail_content_type', 'somvio_booking_mail_content_type' );

	return (bool) $sent;
}

/**
 * Human-readable label helpers for email rows.
 *
 * @param array<string, mixed> $payload Sanitized submission payload.
 * @return array<string, string>
 */
function somvio_booking_email_labels( array $payload ) {
	$services = function_exists( 'somvio_get_quote_service_options' ) ? somvio_get_quote_service_options() : array();
	$props    = function_exists( 'somvio_get_quote_property_options' ) ? somvio_get_quote_property_options() : array();
	$rates    = function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array();
	$addons   = isset( $rates['addons'] ) && is_array( $rates['addons'] ) ? $rates['addons'] : array();

	$service_key = isset( $payload['service'] ) ? (string) $payload['service'] : '';
	$property    = isset( $payload['property'] ) ? (string) $payload['property'] : '';
	$addon_keys  = isset( $payload['addons'] ) && is_array( $payload['addons'] ) ? $payload['addons'] : array();

	$addon_labels = array();
	foreach ( $addon_keys as $key ) {
		$key = sanitize_key( (string) $key );
		if ( isset( $addons[ $key ]['label'] ) ) {
			$addon_labels[] = (string) $addons[ $key ]['label'];
		} else {
			$addon_labels[] = $key;
		}
	}

	$payment = isset( $payload['payment_method'] ) ? sanitize_key( (string) $payload['payment_method'] ) : 'cash';
	$payment_label = ( 'online' === $payment || 'stripe' === $payment )
		? __( 'Pay online (Stripe)', 'somvio' )
		: __( 'Pay on completion / Cash', 'somvio' );

	$welcome = isset( $payload['welcome_pack'] ) && 'yes' === $payload['welcome_pack']
		? __( 'Yes', 'somvio' )
		: __( 'No', 'somvio' );

	$symbol = isset( $rates['symbol'] ) ? (string) $rates['symbol'] : '£';
	$total  = isset( $payload['total'] ) ? (float) $payload['total'] : 0.0;

	return array(
		'service'       => isset( $services[ $service_key ] ) ? (string) $services[ $service_key ] : $service_key,
		'property'      => isset( $props[ $property ] ) ? (string) $props[ $property ] : $property,
		'addons'        => $addon_labels ? implode( ', ', $addon_labels ) : __( 'None', 'somvio' ),
		'payment'       => $payment_label,
		'welcome_pack'  => $welcome,
		'total_formatted' => $symbol . number_format_i18n( $total, 2 ),
		'source'        => ( isset( $payload['source'] ) && 'booking' === $payload['source'] )
			? __( 'Booking form', 'somvio' )
			: __( 'Quick quote', 'somvio' ),
	);
}

/**
 * Build cost breakdown rows for email.
 *
 * @param array<string, mixed> $payload Payload.
 * @return array<int, array{label:string,value:string}>
 */
function somvio_booking_email_cost_rows( array $payload ) {
	$rates      = function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array();
	$symbol     = isset( $rates['symbol'] ) ? (string) $rates['symbol'] : '£';
	$addon_defs = isset( $rates['addons'] ) && is_array( $rates['addons'] ) ? $rates['addons'] : array();
	$rows       = array();

	$base = function_exists( 'somvio_calculate_quote_price' )
		? somvio_calculate_quote_price(
			(string) ( $payload['service'] ?? '' ),
			(string) ( $payload['property'] ?? 'house' ),
			(int) ( $payload['bedrooms'] ?? 1 ),
			(int) ( $payload['bathrooms'] ?? 1 ),
			array()
		)
		: 0.0;

	$rows[] = array(
		'label' => __( 'Base service', 'somvio' ),
		'value' => $symbol . number_format_i18n( $base, 2 ),
	);

	$addon_keys = isset( $payload['addons'] ) && is_array( $payload['addons'] ) ? $payload['addons'] : array();
	foreach ( $addon_keys as $key ) {
		$key = sanitize_key( (string) $key );
		if ( ! isset( $addon_defs[ $key ] ) ) {
			continue;
		}
		$price = (float) ( $addon_defs[ $key ]['price'] ?? 0 );
		$rows[] = array(
			'label' => (string) ( $addon_defs[ $key ]['label'] ?? $key ),
			'value' => $symbol . number_format_i18n( $price, 2 ),
		);
	}

	$total = isset( $payload['total'] ) ? (float) $payload['total'] : $base;
	$rows[] = array(
		'label' => __( 'Estimated total', 'somvio' ),
		'value' => $symbol . number_format_i18n( $total, 2 ),
	);

	return $rows;
}

/**
 * Render a booking email HTML body from a template part.
 *
 * @param string               $template Template slug (admin|customer).
 * @param array<string, mixed> $payload  Submission payload.
 * @param array<string, mixed> $extra    Extra template vars.
 * @return string
 */
function somvio_render_booking_email_html( $template, array $payload, array $extra = array() ) {
	$template = sanitize_key( (string) $template );
	$labels   = somvio_booking_email_labels( $payload );
	$cost_rows = somvio_booking_email_cost_rows( $payload );

	$vars = array_merge(
		array(
			'payload'   => $payload,
			'labels'    => $labels,
			'cost_rows' => $cost_rows,
			'site_name' => get_bloginfo( 'name', 'display' ),
			'site_url'  => home_url( '/' ),
			'admin_email' => somvio_get_booking_admin_email(),
		),
		$extra
	);

	ob_start();
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- scoped email template vars.
	extract( $vars, EXTR_SKIP );
	$part = get_stylesheet_directory() . '/template-parts/emails/booking-' . $template . '.php';
	if ( file_exists( $part ) ) {
		include $part;
	}
	return (string) ob_get_clean();
}

/**
 * Send admin + customer notification emails for a submission.
 *
 * @param array<string, mixed> $payload Sanitized payload with server total.
 * @return array{admin:bool,customer:bool}
 */
function somvio_send_booking_notification_emails( array $payload ) {
	$labels  = somvio_booking_email_labels( $payload );
	$is_booking = isset( $payload['source'] ) && 'booking' === $payload['source'];
	$name    = isset( $payload['name'] ) ? (string) $payload['name'] : '';
	$date    = isset( $payload['date'] ) ? (string) $payload['date'] : '';
	$time    = isset( $payload['time'] ) ? (string) $payload['time'] : '';

	$admin_subject = sprintf(
		/* translators: 1: source label, 2: customer name */
		__( '[Somvio] New %1$s from %2$s', 'somvio' ),
		$labels['source'],
		$name ? $name : __( 'customer', 'somvio' )
	);

	$customer_subject = $is_booking
		? sprintf(
			/* translators: %s: date */
			__( 'Your Somvio booking request — %s', 'somvio' ),
			$date ? $date . ( $time ? ' ' . $time : '' ) : __( 'received', 'somvio' )
		)
		: __( 'Your Somvio quote request has been received', 'somvio' );

	$admin_html = somvio_render_booking_email_html( 'admin', $payload );
	$customer_html = somvio_render_booking_email_html( 'customer', $payload );

	$admin_sent = somvio_send_html_mail(
		somvio_get_booking_admin_email(),
		$admin_subject,
		$admin_html
	);

	$customer_email = isset( $payload['email'] ) ? sanitize_email( (string) $payload['email'] ) : '';
	$customer_sent  = false;
	if ( $customer_email ) {
		$customer_sent = somvio_send_html_mail( $customer_email, $customer_subject, $customer_html );
	}

	/**
	 * After booking notification emails are attempted.
	 *
	 * @param array<string, mixed> $payload Payload.
	 * @param array{admin:bool,customer:bool} $result Send results.
	 */
	do_action(
		'somvio_booking_emails_sent',
		$payload,
		array(
			'admin'    => $admin_sent,
			'customer' => $customer_sent,
		)
	);

	return array(
		'admin'    => $admin_sent,
		'customer' => $customer_sent,
	);
}
