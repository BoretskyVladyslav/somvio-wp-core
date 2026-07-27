<?php
/**
 * Temporary one-shot seeder for an empty WP database.
 *
 * Creates Home (static front), Booking, Services + 5 single-service pages,
 * and the Legal Index. Runs once via `somvio_pages_seeded`.
 *
 * Remove this file (and its require in functions.php) after local/staging bootstrap.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert or return a published page by slug.
 *
 * @param string $slug      Page slug.
 * @param string $title     Page title.
 * @param int    $parent_id Optional parent page ID.
 * @return int Page ID or 0.
 */
function somvio_seeder_insert_page( $slug, $title, $parent_id = 0 ) {
	if ( function_exists( 'somvio_ensure_page' ) ) {
		return somvio_ensure_page( $slug, $title, $parent_id );
	}

	$slug      = sanitize_title( $slug );
	$parent_id = absint( $parent_id );

	$existing = get_page_by_path(
		$parent_id > 0
			? get_post_field( 'post_name', $parent_id ) . '/' . $slug
			: $slug,
		OBJECT,
		'page'
	);

	if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
		return (int) $existing->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'page',
			'post_parent' => $parent_id,
			'post_author' => get_current_user_id() ? get_current_user_id() : 1,
		),
		true
	);

	return ( is_wp_error( $page_id ) || ! $page_id ) ? 0 : (int) $page_id;
}

/**
 * Assign a page template meta value.
 *
 * @param int    $page_id  Page ID.
 * @param string $template Template file relative to theme (e.g. page-booking.php).
 * @return void
 */
function somvio_seeder_assign_template( $page_id, $template ) {
	$page_id  = absint( $page_id );
	$template = sanitize_file_name( (string) $template );

	if ( $page_id <= 0 || '' === $template ) {
		return;
	}

	if ( get_post_meta( $page_id, '_wp_page_template', true ) !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	}
}

/**
 * Seed required pages once (empty DB bootstrap).
 *
 * @return void
 */
function somvio_run_pages_seeder() {
	if ( get_option( 'somvio_pages_seeded' ) ) {
		return;
	}

	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	/* Prefer the full idempotent setup when available (menus, legal pack, Reading). */
	if ( function_exists( 'somvio_setup_core_pages' ) ) {
		somvio_setup_core_pages();
	}

	if ( function_exists( 'somvio_ensure_site_identity' ) ) {
		somvio_ensure_site_identity();
	}

	/* Explicit guarantees for the empty-DB checklist. */
	$home_id = somvio_seeder_insert_page( 'home', 'Home' );

	$booking_id = function_exists( 'somvio_ensure_booking_page' )
		? somvio_ensure_booking_page()
		: somvio_seeder_insert_page( 'booking', 'Booking' );
	somvio_seeder_assign_template( $booking_id, 'page-booking.php' );

	$thank_you_id = function_exists( 'somvio_ensure_thank_you_page' )
		? somvio_ensure_thank_you_page()
		: somvio_seeder_insert_page( 'thank-you', 'Thank You' );
	somvio_seeder_assign_template( $thank_you_id, 'page-thank-you.php' );

	$services_id = somvio_seeder_insert_page( 'services', 'Services' );

	$service_pages = function_exists( 'somvio_get_single_service_pages' )
		? somvio_get_single_service_pages()
		: array(
			'regular-cleaning' => 'Regular Cleaning',
			'deep-cleaning'    => 'Deep Cleaning',
			'end-of-tenancy'   => 'End of Tenancy',
			'airbnb-cleaning'  => 'Airbnb Cleaning',
			'after-builders'   => 'After Builders',
		);

	foreach ( $service_pages as $slug => $title ) {
		if ( function_exists( 'somvio_ensure_service_page' ) ) {
			somvio_ensure_service_page( $slug, $title, $services_id );
			continue;
		}

		$sid = somvio_seeder_insert_page( $slug, $title, $services_id );
		somvio_seeder_assign_template( $sid, 'page-single-service.php' );
	}

	$legal_title = __( 'Somvio Legal & Policy Pack', 'somvio' );
	$legal_id    = 0;

	if ( function_exists( 'somvio_ensure_legal_page' ) ) {
		$legal_id = somvio_ensure_legal_page( 'legal', $legal_title );
	} else {
		$legal_id = somvio_seeder_insert_page( 'legal', $legal_title );
		somvio_seeder_assign_template( $legal_id, 'page-legal.php' );
	}

	if ( $home_id > 0 ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}

	update_option( 'somvio_pages_seeded', true, false );

	flush_rewrite_rules( false );

	unset( $booking_id, $thank_you_id, $legal_id );
}
add_action( 'admin_init', 'somvio_run_pages_seeder', 5 );
