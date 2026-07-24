<?php
/**
 * Pre-footer CTA banner + global site footer.
 *
 * Figma node: 325:5030
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact email (filterable).
 *
 * @return array{display: string, href: string}
 */
function somvio_get_email() {
	$email = apply_filters(
		'somvio_email',
		array(
			'display' => 'Info@somvio.co.uk',
			'href'    => 'mailto:Info@somvio.co.uk',
		)
	);

	return array(
		'display' => isset( $email['display'] ) ? (string) $email['display'] : 'Info@somvio.co.uk',
		'href'    => isset( $email['href'] ) ? (string) $email['href'] : 'mailto:Info@somvio.co.uk',
	);
}

/**
 * Contact location (filterable).
 *
 * @return string
 */
function somvio_get_location() {
	return (string) apply_filters( 'somvio_location', __( 'London, United Kingdom', 'somvio' ) );
}

/**
 * WhatsApp chat URL (filterable).
 *
 * @return string
 */
function somvio_get_whatsapp_url() {
	return esc_url( apply_filters( 'somvio_whatsapp_url', 'https://wa.me/447402495410' ) );
}

/**
 * Social profile URLs (filterable). TikTok intentionally omitted.
 *
 * @return array<string, array{label: string, url: string, icon: string}>
 */
function somvio_get_social_links() {
	$links = array(
		'instagram' => array(
			'label' => __( 'Instagram', 'somvio' ),
			'url'   => 'https://www.instagram.com/',
			'icon'  => 'icon-instagram',
		),
		'facebook'  => array(
			'label' => __( 'Facebook', 'somvio' ),
			'url'   => 'https://www.facebook.com/',
			'icon'  => 'icon-facebook',
		),
		'whatsapp'  => array(
			'label' => __( 'WhatsApp', 'somvio' ),
			'url'   => somvio_get_whatsapp_url(),
			'icon'  => 'icon-whatsapp',
		),
	);

	return apply_filters( 'somvio_social_links', $links );
}

/**
 * Replace GeneratePress footer with Somvio footer + CTA.
 *
 * @return void
 */
function somvio_replace_default_footer() {
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer' );

	add_action( 'generate_before_footer', 'somvio_render_cta_banner', 10 );
	add_action( 'generate_footer', 'somvio_render_site_footer', 10 );
}
add_action( 'after_setup_theme', 'somvio_replace_default_footer', 20 );

/**
 * Whether the current view should omit the pre-footer CTA banner.
 *
 * Privacy Policy, Terms of Use, Booking, and 404 go straight to the footer.
 *
 * @return bool
 */
function somvio_should_skip_cta_banner() {
	if ( is_404() ) {
		return true;
	}

	if ( is_page( 'privacy-policy' ) || is_page_template( 'page-privacy-policy.php' ) ) {
		return true;
	}

	if ( is_page( 'terms-of-use' ) || is_page_template( 'page-terms-of-use.php' ) ) {
		return true;
	}

	if ( is_page( 'booking' ) || is_page_template( 'page-booking.php' ) ) {
		return true;
	}

	/**
	 * Filter whether to skip the pre-footer CTA.
	 *
	 * @param bool $skip Whether to skip.
	 */
	return (bool) apply_filters( 'somvio_should_skip_cta_banner', false );
}

/**
 * Render the pre-footer CTA banner.
 *
 * @return void
 */
function somvio_render_cta_banner() {
	if ( somvio_should_skip_cta_banner() ) {
		return;
	}

	get_template_part( 'template-parts/sections/cta', 'banner' );
}

/**
 * Render the custom site footer.
 *
 * @return void
 */
function somvio_render_site_footer() {
	get_template_part( 'template-parts/footer/site', 'footer' );
}
