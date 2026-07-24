<?php
/**
 * Blog page — template routing when Blog is also page_for_posts.
 *
 * WordPress ignores `_wp_page_template` on the posts page; force page-blog.php.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the Blog page / posts index.
 *
 * @return bool
 */
function somvio_is_blog_page() {
	if ( is_page_template( 'page-blog.php' ) ) {
		return true;
	}

	if ( is_page( 'blog' ) ) {
		return true;
	}

	// Blog seeded as page_for_posts → is_home() && ! is_front_page().
	if ( is_home() && ! is_front_page() ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_blog_page', false );
}

/**
 * Mark Blog so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_blog_body_class( $classes ) {
	if ( somvio_is_blog_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_blog_body_class' );

/**
 * Enqueue blog category filter script on the Blog page.
 *
 * @return void
 */
function somvio_enqueue_blog_assets() {
	if ( ! somvio_is_blog_page() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/blog-filter.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'somvio-blog-filter',
		get_stylesheet_directory_uri() . '/assets/js/blog-filter.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'somvio_enqueue_blog_assets' );

/**
 * Load page-blog.php for the posts index (page_for_posts ignores page templates).
 *
 * @param string $template Path to template.
 * @return string
 */
function somvio_blog_template_include( $template ) {
	if ( ! is_home() || is_front_page() ) {
		return $template;
	}

	$custom = locate_template( 'page-blog.php' );

	if ( $custom ) {
		return $custom;
	}

	return $template;
}
add_filter( 'template_include', 'somvio_blog_template_include', 99 );
