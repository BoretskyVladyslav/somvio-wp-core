<?php
/**
 * Front-end performance: emoji off, guest dashicons off, async Google Fonts.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strip WordPress emoji scripts, styles, and related DNS prefetch.
 *
 * @return void
 */
function somvio_disable_wp_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'login_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_block_assets', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'somvio_disable_emoji_tinymce' );
	add_filter( 'wp_resource_hints', 'somvio_disable_emoji_dns_prefetch', 10, 2 );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'somvio_disable_wp_emojis' );

/**
 * Remove the TinyMCE emoji plugin.
 *
 * @param array $plugins TinyMCE plugins.
 * @return array
 */
function somvio_disable_emoji_tinymce( $plugins ) {
	if ( ! is_array( $plugins ) ) {
		return array();
	}

	return array_values( array_diff( $plugins, array( 'wpemoji' ) ) );
}

/**
 * Drop s.w.org emoji CDN from DNS prefetch.
 *
 * @param array  $urls          Hint URLs.
 * @param string $relation_type Hint relation.
 * @return array
 */
function somvio_disable_emoji_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' !== $relation_type || ! is_array( $urls ) ) {
		return $urls;
	}

	return array_values(
		array_filter(
			$urls,
			static function ( $url ) {
				return false === strpos( (string) $url, 'https://s.w.org/images/core/emoji/' );
			}
		)
	);
}

/**
 * Do not load dashicons CSS for logged-out visitors (admin bar unused).
 *
 * @return void
 */
function somvio_dequeue_dashicons_for_guests() {
	if ( is_admin() || is_customize_preview() || is_user_logged_in() ) {
		return;
	}

	wp_deregister_style( 'dashicons' );
	wp_dequeue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'somvio_dequeue_dashicons_for_guests', 100 );

/**
 * Load Montserrat non-blocking: preload stylesheet, then apply via media swap.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 * @param string $href   Stylesheet URL.
 * @param string $media  Media attribute.
 * @return string
 */
function somvio_async_google_fonts_tag( $html, $handle, $href, $media ) {
	unset( $media );

	if ( 'somvio-montserrat' !== $handle || ! is_string( $href ) || '' === $href ) {
		return $html;
	}

	$url = esc_url( $href );

	return sprintf(
		'<link rel="preload" as="style" href="%1$s">' . "\n" .
		'<link rel="stylesheet" href="%1$s" media="print" onload="this.media=\'all\'">' . "\n" .
		'<noscript><link rel="stylesheet" href="%1$s"></noscript>' . "\n",
		$url
	);
}
add_filter( 'style_loader_tag', 'somvio_async_google_fonts_tag', 10, 4 );
