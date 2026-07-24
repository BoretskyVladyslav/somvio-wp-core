<?php
/**
 * Somvio Child Theme functions and definitions.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeneratePress already enqueues parent CSS (`generate-style`) and the child
 * theme's style.css (`generate-child`). Do not enqueue those files again —
 * it can break Customizer settings and load stylesheets twice.
 *
 * @see https://docs.generatepress.com/article/using-child-theme/
 * @see https://docs.generatepress.com/article/child-theme-issues/
 */

require_once get_stylesheet_directory() . '/inc/header.php';
require_once get_stylesheet_directory() . '/inc/hero.php';
require_once get_stylesheet_directory() . '/inc/services-page.php';
require_once get_stylesheet_directory() . '/inc/about-page.php';
require_once get_stylesheet_directory() . '/inc/blog-page.php';
require_once get_stylesheet_directory() . '/inc/blog-single.php';
require_once get_stylesheet_directory() . '/inc/service-single.php';
require_once get_stylesheet_directory() . '/inc/services-list.php';
require_once get_stylesheet_directory() . '/inc/why-choose.php';
require_once get_stylesheet_directory() . '/inc/services-grid.php';
require_once get_stylesheet_directory() . '/inc/how-it-works.php';
require_once get_stylesheet_directory() . '/inc/before-after.php';
require_once get_stylesheet_directory() . '/inc/testimonials.php';
require_once get_stylesheet_directory() . '/inc/faq.php';
require_once get_stylesheet_directory() . '/inc/booking-page.php';
require_once get_stylesheet_directory() . '/inc/legal-page.php';
require_once get_stylesheet_directory() . '/inc/error-404.php';
require_once get_stylesheet_directory() . '/inc/footer.php';
require_once get_stylesheet_directory() . '/inc/scroll-reveal.php';
require_once get_stylesheet_directory() . '/inc/setup-pages.php';
require_once get_stylesheet_directory() . '/inc/calculator.php';
require_once get_stylesheet_directory() . '/inc/layout.php';

/**
 * Return inline SVG markup from assets/icons/ by icon name.
 *
 * Accepts a filename with or without the `.svg` extension
 * (e.g. `icon-arrow-right` or `icon-arrow-right.svg`).
 *
 * @param string $name Icon file name.
 * @return string SVG markup, or empty string if the file is missing.
 */
function somvio_get_icon( $name ) {
	$name = sanitize_file_name( (string) $name );
	$name = preg_replace( '/\.svg$/i', '', $name );

	if ( ! is_string( $name ) || '' === $name ) {
		return '';
	}

	$icons_dir = realpath( get_stylesheet_directory() . '/assets/icons' );
	$path      = realpath( get_stylesheet_directory() . '/assets/icons/' . $name . '.svg' );

	if ( false === $icons_dir || false === $path || 0 !== strpos( $path, $icons_dir ) ) {
		return '';
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
	$svg = file_get_contents( $path );

	return false === $svg ? '' : $svg;
}

/**
 * Set the ACF JSON save path to the child theme directory.
 *
 * @param string $path Default ACF JSON save path.
 * @return string
 */
function somvio_acf_json_save_path( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'somvio_acf_json_save_path' );

/**
 * Set the ACF JSON load path to the child theme directory.
 *
 * @param array $paths Default ACF JSON load paths.
 * @return array
 */
function somvio_acf_json_load_paths( $paths ) {
	return array( get_stylesheet_directory() . '/acf-json' );
}
add_filter( 'acf/settings/load_json', 'somvio_acf_json_load_paths' );
