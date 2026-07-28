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

/**
 * Theme supports for SEO plugins (Rank Math hooks document title via title-tag).
 * Parent GeneratePress also declares this; re-declare so the child is self-sufficient.
 * wp_head() / wp_footer() live in the parent header.php / footer.php (not overridden).
 *
 * @return void
 */
function somvio_theme_setup() {
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'somvio_theme_setup' );

/**
 * filemtime-based version for a child-theme asset (cache bust).
 *
 * @param string $relative_path Path relative to the child theme root.
 * @return string
 */
function somvio_asset_version( $relative_path ) {
	$path = get_stylesheet_directory() . '/' . ltrim( (string) $relative_path, '/' );

	if ( file_exists( $path ) ) {
		return (string) filemtime( $path );
	}

	$theme = wp_get_theme( get_stylesheet() );
	$ver   = $theme->get( 'Version' );

	return is_string( $ver ) && '' !== $ver ? $ver : '1.0.5';
}

/**
 * Force ?ver=filemtime on all child-theme CSS/JS URLs.
 *
 * GeneratePress already versions generate-child via filemtime; enqueues in inc/
 * do the same. This filter keeps versions correct if a plugin freezes/strips them.
 *
 * @param string $src    Asset URL.
 * @param string $handle Script/style handle.
 * @return string
 */
function somvio_cache_bust_theme_asset_src( $src, $handle ) {
	unset( $handle );

	if ( ! is_string( $src ) || '' === $src ) {
		return $src;
	}

	$theme_uri = get_stylesheet_directory_uri();
	if ( 0 !== strpos( $src, $theme_uri ) ) {
		return $src;
	}

	$path_part = wp_parse_url( $src, PHP_URL_PATH );
	$base_path = wp_parse_url( $theme_uri, PHP_URL_PATH );
	if ( ! is_string( $path_part ) || ! is_string( $base_path ) ) {
		return $src;
	}

	$relative = ltrim( substr( $path_part, strlen( $base_path ) ), '/' );
	if ( '' === $relative ) {
		return $src;
	}

	$file = get_stylesheet_directory() . '/' . $relative;
	if ( ! is_file( $file ) ) {
		return $src;
	}

	return add_query_arg( 'ver', (string) filemtime( $file ), remove_query_arg( 'ver', $src ) );
}
add_filter( 'style_loader_src', 'somvio_cache_bust_theme_asset_src', 20, 2 );
add_filter( 'script_loader_src', 'somvio_cache_bust_theme_asset_src', 20, 2 );

require_once get_stylesheet_directory() . '/inc/acf-fields.php';
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
require_once get_stylesheet_directory() . '/inc/thank-you-page.php';
require_once get_stylesheet_directory() . '/inc/contact-page.php';
require_once get_stylesheet_directory() . '/inc/legal-page.php';
require_once get_stylesheet_directory() . '/inc/error-404.php';
require_once get_stylesheet_directory() . '/inc/footer.php';
require_once get_stylesheet_directory() . '/inc/cookie-consent.php';
require_once get_stylesheet_directory() . '/inc/scroll-reveal.php';
require_once get_stylesheet_directory() . '/inc/setup-pages.php';
require_once get_stylesheet_directory() . '/inc/page-seeder.php';
require_once get_stylesheet_directory() . '/inc/calculator.php';
require_once get_stylesheet_directory() . '/inc/booking/bootstrap.php';
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
