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

/** @var int Bump to re-seed legal page post_content from Figma. */
const SOMVIO_LEGAL_CONTENT_VERSION = 1;

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
 * Privacy Policy article HTML — Figma 300:2222.
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
 * Seed / refresh legal page post_content when version is stale.
 *
 * @param int    $page_id Page ID.
 * @param string $html    Seed HTML.
 * @return void
 */
function somvio_seed_legal_page_content( $page_id, $html ) {
	$page_id = absint( $page_id );
	$html    = is_string( $html ) ? trim( $html ) : '';

	if ( $page_id <= 0 || '' === $html ) {
		return;
	}

	$version = (int) get_post_meta( $page_id, '_somvio_legal_content_version', true );

	if ( $version >= SOMVIO_LEGAL_CONTENT_VERSION ) {
		return;
	}

	wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => $html,
		)
	);

	update_post_meta( $page_id, '_somvio_legal_content_version', SOMVIO_LEGAL_CONTENT_VERSION );
}
