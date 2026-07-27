<?php
/**
 * Cookie consent banner (Figma 450:5227).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue cookie consent script sitewide.
 *
 * @return void
 */
function somvio_enqueue_cookie_consent_assets() {
	$script_path = get_stylesheet_directory() . '/assets/js/cookie-consent.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-cookie-consent',
		get_stylesheet_directory_uri() . '/assets/js/cookie-consent.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_cookie_consent_assets' );

/**
 * Render cookie consent banner before </body>.
 *
 * @return void
 */
function somvio_render_cookie_consent() {
	get_template_part( 'template-parts/components/cookie', 'consent' );
}
add_action( 'wp_footer', 'somvio_render_cookie_consent', 5 );
