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

	return is_string( $ver ) && '' !== $ver ? $ver : '1.0.6';
}

/**
 * Whether to prefer minified theme assets (disable via SCRIPT_DEBUG).
 *
 * @return bool
 */
function somvio_use_minified_assets() {
	return ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
}

/**
 * Resolve a theme-relative JS path, preferring `.min.js` when available.
 *
 * @param string $relative_js Path relative to the child theme root (e.g. assets/js/header.js).
 * @return string
 */
function somvio_theme_script_path( $relative_js ) {
	$relative_js = ltrim( (string) $relative_js, '/' );

	if ( somvio_use_minified_assets() && preg_match( '/\.js$/i', $relative_js ) && ! preg_match( '/\.min\.js$/i', $relative_js ) ) {
		$min_rel = (string) preg_replace( '/\.js$/i', '.min.js', $relative_js );
		if ( is_file( get_stylesheet_directory() . '/' . $min_rel ) ) {
			return $min_rel;
		}
	}

	return $relative_js;
}

/**
 * Enqueue a child-theme script in the footer with a defer-friendly strategy.
 *
 * Stable handles + `defer` keep assets compatible with caching plugins
 * (WP Rocket, LiteSpeed, Autoptimize) that delay/defer JS further.
 *
 * @param string   $handle      Script handle.
 * @param string   $relative_js Path relative to the child theme root.
 * @param string[] $deps        Script dependencies.
 * @param string   $strategy    Loading strategy: defer|async|empty string.
 * @return bool True when enqueued.
 */
function somvio_enqueue_theme_script( $handle, $relative_js, $deps = array(), $strategy = 'defer' ) {
	$relative = somvio_theme_script_path( $relative_js );
	$path     = get_stylesheet_directory() . '/' . $relative;

	if ( ! is_file( $path ) ) {
		return false;
	}

	$args = array(
		'in_footer' => true,
	);

	if ( is_string( $strategy ) && '' !== $strategy ) {
		$args['strategy'] = $strategy;
	}

	wp_enqueue_script(
		$handle,
		get_stylesheet_directory_uri() . '/' . $relative,
		$deps,
		(string) filemtime( $path ),
		$args
	);

	return true;
}

/**
 * Prefer minified child stylesheet when GeneratePress enqueues `generate-child`.
 *
 * @param string $src    Stylesheet URL.
 * @param string $handle Style handle.
 * @return string
 */
function somvio_prefer_minified_child_style( $src, $handle ) {
	if ( 'generate-child' !== $handle || ! somvio_use_minified_assets() ) {
		return $src;
	}

	$min_path = get_stylesheet_directory() . '/style.min.css';
	if ( ! is_file( $min_path ) ) {
		return $src;
	}

	return get_stylesheet_directory_uri() . '/style.min.css';
}
add_filter( 'style_loader_src', 'somvio_prefer_minified_child_style', 15, 2 );

/**
 * Resource hints for Google Fonts (PageSpeed).
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function somvio_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'somvio_resource_hints', 10, 2 );

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
