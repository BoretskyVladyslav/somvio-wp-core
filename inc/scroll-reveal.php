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
	$script_path = get_stylesheet_directory() . '/assets/js/scroll-reveal.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-scroll-reveal',
		get_stylesheet_directory_uri() . '/assets/js/scroll-reveal.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_scroll_reveal_assets' );
