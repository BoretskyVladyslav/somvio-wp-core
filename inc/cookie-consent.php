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
	somvio_enqueue_theme_script( 'somvio-cookie-consent', 'assets/js/cookie-consent.js' );
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
