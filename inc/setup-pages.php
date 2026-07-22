<?php
/**
 * Programmatic core pages + static front page setup.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core pages to ensure exist (slug => title).
 *
 * @return array<string, string>
 */
function somvio_get_core_pages() {
	return array(
		'home'      => 'Home',
		'services'  => 'Services',
		'about-us'  => 'About Us',
		'reviews'   => 'Reviews',
		'faq'       => 'FAQ',
		'booking'   => 'Booking',
		'contact'   => 'Contact',
		'blog'      => 'Blog',
	);
}

/**
 * Find a published page ID by slug, or 0 if missing.
 *
 * @param string $slug Page slug.
 * @return int
 */
function somvio_get_page_id_by_slug( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ), OBJECT, 'page' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return (int) $page->ID;
	}

	return 0;
}

/**
 * Create a page if it does not already exist.
 *
 * @param string $slug  Page slug.
 * @param string $title Page title.
 * @return int Page ID (existing or newly created), or 0 on failure.
 */
function somvio_ensure_page( $slug, $title ) {
	$existing_id = somvio_get_page_id_by_slug( $slug );

	if ( $existing_id > 0 ) {
		return $existing_id;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return 0;
	}

	return (int) $page_id;
}

/**
 * Ensure all core pages exist and Reading settings use Home + Blog.
 *
 * Safe to call repeatedly: pages are looked up by slug before insert,
 * and Reading options are only updated when values differ.
 *
 * @return void
 */
function somvio_setup_core_pages() {
	if ( get_option( 'somvio_core_pages_setup_lock' ) ) {
		return;
	}

	update_option( 'somvio_core_pages_setup_lock', 1, false );

	try {
		$page_ids = array();

		foreach ( somvio_get_core_pages() as $slug => $title ) {
			$page_ids[ $slug ] = somvio_ensure_page( $slug, $title );
		}

		$home_id = isset( $page_ids['home'] ) ? (int) $page_ids['home'] : 0;
		$blog_id = isset( $page_ids['blog'] ) ? (int) $page_ids['blog'] : 0;

		if ( $home_id > 0 && $blog_id > 0 && $home_id !== $blog_id ) {
			if ( 'page' !== get_option( 'show_on_front' ) ) {
				update_option( 'show_on_front', 'page' );
			}

			if ( (int) get_option( 'page_on_front' ) !== $home_id ) {
				update_option( 'page_on_front', $home_id );
			}

			if ( (int) get_option( 'page_for_posts' ) !== $blog_id ) {
				update_option( 'page_for_posts', $blog_id );
			}
		}

		update_option( 'somvio_core_pages_version', 1, false );
	} finally {
		delete_option( 'somvio_core_pages_setup_lock' );
	}
}

/**
 * Run page setup once per version (admin / theme switch).
 *
 * @return void
 */
function somvio_maybe_setup_core_pages() {
	$version = 1;

	if ( (int) get_option( 'somvio_core_pages_version', 0 ) >= $version ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	somvio_setup_core_pages();
}
add_action( 'admin_init', 'somvio_maybe_setup_core_pages' );

/**
 * Also seed pages when the child theme is activated.
 *
 * @return void
 */
function somvio_setup_core_pages_on_theme_switch() {
	// Theme switch may run before a user capability context is ready.
	somvio_setup_core_pages();
}
add_action( 'after_switch_theme', 'somvio_setup_core_pages_on_theme_switch' );
