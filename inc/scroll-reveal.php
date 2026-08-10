<?php
/**
 * Sitewide scroll-reveal script enqueue.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue lightweight IntersectionObserver scroll reveal script.
 *
 * @return void
 */
function somvio_enqueue_scroll_reveal_assets() {
	somvio_enqueue_theme_script( 'somvio-scroll-reveal', 'assets/js/scroll-reveal.js' );
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_scroll_reveal_assets' );
