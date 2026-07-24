<?php
/**
 * Legal pages — Privacy Policy / Terms of Use.
 *
 * Hero: 300:2218 / 300:2239. Article body: 300:2222 / 300:2243.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var int Bump to force re-seed legal page post_content from Figma. */
const SOMVIO_LEGAL_CONTENT_VERSION = 2;

/**
 * Whether the current view is a legal page with a dark hero.
 *
 * @return bool
 */
function somvio_is_legal_page() {
	if ( is_page( 'privacy-policy' ) || is_page_template( 'page-privacy-policy.php' ) ) {
		return true;
	}

	if ( is_page( 'terms-of-use' ) || is_page_template( 'page-terms-of-use.php' ) ) {
		return true;
	}

	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy_id > 0 && is_page( $privacy_id ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_legal_page', false );
}

/**
 * Mark legal pages so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_legal_body_class( $classes ) {
	if ( somvio_is_legal_page() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-legal-page';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_legal_body_class' );

/**
 * Resolve the canonical Privacy Policy page ID (incl. drafts / WP setting).
 *
 * Prefer `wp_page_for_privacy_policy`, then exact slug `privacy-policy`
 * regardless of status (avoids creating privacy-policy-2/-3 duplicates).
 *
 * @return int
 */
function somvio_get_privacy_policy_page_id() {
	$option_id = (int) get_option( 'wp_page_for_privacy_policy' );

	if ( $option_id > 0 ) {
		$post = get_post( $option_id );
		if ( $post instanceof WP_Post && 'page' === $post->post_type && 'trash' !== $post->post_status ) {
			return $option_id;
		}
	}

	$pages = get_posts(
		array(
			'name'             => 'privacy-policy',
			'post_type'        => 'page',
			'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
			'numberposts'      => 1,
			'suppress_filters' => true,
		)
	);

	if ( ! empty( $pages[0] ) && $pages[0] instanceof WP_Post ) {
		return (int) $pages[0]->ID;
	}

	if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		return somvio_get_page_id_by_slug( 'privacy-policy' );
	}

	return 0;
}

/**
 * Privacy Policy article HTML — Figma 300:2222 (exact copy).
 *
 * @return string
 */
function somvio_get_privacy_policy_seed_content() {
	return <<<'HTML'
<div class="legal-content__section">
<h2>Introduction</h2>
<p>At Somvio, we respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use and safeguard your data when you use our website and services.</p>
</div>
<div class="legal-content__section">
<h2>Information We Collect</h2>
<p>We may collect the following information:</p>
<ul>
<li>Full name</li>
<li>Email address</li>
<li>Phone number</li>
<li>Property address</li>
<li>Booking information</li>
<li>Payment details</li>
<li>Website usage data</li>
<li>Device information</li>
</ul>
</div>
<div class="legal-content__section">
<h2>Cookies</h2>
<p>Our website uses cookies to improve your browsing experience, remember preferences and analyse website performance.</p>
</div>
<div class="legal-content__section">
<h2>Data Protection</h2>
<p>We use industry-standard security measures including SSL encryption and secure payment gateways to protect your personal information.</p>
</div>
HTML;
}

/**
 * Terms of Use article HTML — Figma 300:2243.
 *
 * @return string
 */
function somvio_get_terms_of_use_seed_content() {
	return <<<'HTML'
<div class="legal-content__section">
<h2>Booking</h2>
<p>All prices displayed on our website are transparent and include applicable taxes unless otherwise stated.</p>
<p>Additional services requested during the appointment may result in extra charges.</p>
</div>
<div class="legal-content__section">
<h2>Pricing</h2>
<p>A booking becomes confirmed once payment has been successfully processed or written confirmation has been provided by Somvio.</p>
</div>
<div class="legal-content__section">
<h2>Cancellation Policy</h2>
<p>Customers may cancel or reschedule free of charge up to 24 hours before the scheduled appointment.</p>
<p>Late cancellations may incur a cancellation fee.</p>
</div>
<div class="legal-content__section">
<h2>Payments</h2>
<p>Payments are securely processed through Stripe and PayPal.</p>
<p>Somvio does not store your payment card details.</p>
</div>
HTML;
}

/**
 * Seed / refresh legal page post_content.
 *
 * @param int    $page_id Page ID.
 * @param string $html    Seed HTML.
 * @param bool   $force   When true, always overwrite post_content.
 * @return bool True when content was written.
 */
function somvio_seed_legal_page_content( $page_id, $html, $force = false ) {
	$page_id = absint( $page_id );
	$html    = is_string( $html ) ? trim( $html ) : '';

	if ( $page_id <= 0 || '' === $html ) {
		return false;
	}

	$page = get_post( $page_id );

	if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type ) {
		return false;
	}

	$version = (int) get_post_meta( $page_id, '_somvio_legal_content_version', true );
	$hash    = md5( $html );
	$stored  = (string) get_post_meta( $page_id, '_somvio_legal_content_hash', true );

	if ( ! $force && $version >= SOMVIO_LEGAL_CONTENT_VERSION && $stored === $hash && trim( (string) $page->post_content ) === $html ) {
		return false;
	}

	$result = wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => $html,
			'post_status'  => 'publish',
		),
		true
	);

	if ( is_wp_error( $result ) || ! $result ) {
		return false;
	}

	update_post_meta( $page_id, '_somvio_legal_content_version', SOMVIO_LEGAL_CONTENT_VERSION );
	update_post_meta( $page_id, '_somvio_legal_content_hash', $hash );

	return true;
}

/**
 * Force-overwrite Privacy Policy post_content from Figma 300:2222.
 *
 * Targets the canonical privacy page (WP setting / slug), publishes it,
 * assigns the legal template, and trashes duplicate privacy-policy-* pages.
 *
 * @return int Canonical page ID or 0.
 */
function somvio_force_seed_privacy_policy_content() {
	$page_id = somvio_get_privacy_policy_page_id();

	if ( $page_id <= 0 && function_exists( 'somvio_ensure_page' ) ) {
		$page_id = somvio_ensure_page( 'privacy-policy', 'Privacy Policy' );
	}

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	$updates = array(
		'ID'          => $page_id,
		'post_status' => 'publish',
		'post_title'  => 'Privacy Policy',
		'post_name'   => 'privacy-policy',
	);

	if ( $page instanceof WP_Post && 'privacy-policy' !== $page->post_name ) {
		$updates['post_name'] = 'privacy-policy';
	}

	wp_update_post( $updates );

	somvio_seed_legal_page_content( $page_id, somvio_get_privacy_policy_seed_content(), true );

	update_post_meta( $page_id, '_wp_page_template', 'page-privacy-policy.php' );

	if ( (int) get_option( 'wp_page_for_privacy_policy' ) !== $page_id ) {
		update_option( 'wp_page_for_privacy_policy', $page_id );
	}

	// Trash duplicate slug variants created while the canonical page was draft.
	$dupes = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
			'numberposts'      => 20,
			'suppress_filters' => true,
			'exclude'          => array( $page_id ),
		)
	);

	foreach ( $dupes as $dupe ) {
		if ( ! ( $dupe instanceof WP_Post ) ) {
			continue;
		}

		$name = (string) $dupe->post_name;
		if ( 0 === strpos( $name, 'privacy-policy-' ) || 'privacy-policy' === $name ) {
			wp_trash_post( (int) $dupe->ID );
		}
	}

	return $page_id;
}

/**
 * One-shot / versioned force reseed for legal pages (front + admin).
 *
 * @return void
 */
function somvio_maybe_force_seed_legal_content() {
	if ( (int) get_option( 'somvio_legal_content_seed_version', 0 ) >= SOMVIO_LEGAL_CONTENT_VERSION ) {
		return;
	}

	somvio_force_seed_privacy_policy_content();

	if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		$terms_id = somvio_get_page_id_by_slug( 'terms-of-use' );
		if ( $terms_id > 0 ) {
			somvio_seed_legal_page_content( $terms_id, somvio_get_terms_of_use_seed_content(), true );
			update_post_meta( $terms_id, '_wp_page_template', 'page-terms-of-use.php' );
		}
	}

	update_option( 'somvio_legal_content_seed_version', SOMVIO_LEGAL_CONTENT_VERSION, false );
}
add_action( 'init', 'somvio_maybe_force_seed_legal_content', 25 );
