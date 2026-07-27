<?php
/**
 * Legal pages — registry, seeding, body class, redirects.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/legal-content-seeds.php';

/** @var int Bump to force re-seed all legal page post_content. */
const SOMVIO_LEGAL_CONTENT_VERSION = 3;

/**
 * Whether the current view is a legal page with a dark hero.
 *
 * @return bool
 */
function somvio_is_legal_page() {
	$registry = somvio_get_legal_pages_registry();

	foreach ( array_keys( $registry ) as $slug ) {
		if ( is_page( $slug ) ) {
			return true;
		}
	}

	if ( is_page( 'terms-of-use' ) ) {
		return true;
	}

	if (
		is_page_template( 'page-legal.php' )
		|| is_page_template( 'page-privacy-policy.php' )
		|| is_page_template( 'page-terms-of-use.php' )
	) {
		return true;
	}

	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy_id > 0 && is_page( $privacy_id ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_legal_page', false );
}

/**
 * Mark legal pages so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_legal_body_class( $classes ) {
	if ( somvio_is_legal_page() ) {
		$classes[] = 'somvio-has-hero';
		$classes[] = 'somvio-legal-page';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_legal_body_class' );

/**
 * Resolve the canonical Privacy Policy page ID (incl. drafts / WP setting).
 *
 * @return int
 */
function somvio_get_privacy_policy_page_id() {
	$option_id = (int) get_option( 'wp_page_for_privacy_policy' );

	if ( $option_id > 0 ) {
		$post = get_post( $option_id );
		if ( $post instanceof WP_Post && 'page' === $post->post_type && 'trash' !== $post->post_status ) {
			return $option_id;
		}
	}

	$pages = get_posts(
		array(
			'name'             => 'privacy-policy',
			'post_type'        => 'page',
			'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
			'numberposts'      => 1,
			'suppress_filters' => true,
		)
	);

	if ( ! empty( $pages[0] ) && $pages[0] instanceof WP_Post ) {
		return (int) $pages[0]->ID;
	}

	if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		return somvio_get_page_id_by_slug( 'privacy-policy' );
	}

	return 0;
}

/**
 * Canonical Terms & Conditions page ID.
 *
 * @return int
 */
function somvio_get_terms_conditions_page_id() {
	if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		$id = somvio_get_page_id_by_slug( 'terms-conditions' );
		if ( $id > 0 ) {
			return $id;
		}
		return somvio_get_page_id_by_slug( 'terms-of-use' );
	}

	return 0;
}

/**
 * Seed / refresh legal page post_content.
 *
 * @param int    $page_id Page ID.
 * @param string $html    Seed HTML.
 * @param bool   $force   When true, always overwrite post_content.
 * @return bool True when content was written.
 */
function somvio_seed_legal_page_content( $page_id, $html, $force = false ) {
	$page_id = absint( $page_id );
	$html    = is_string( $html ) ? trim( $html ) : '';

	if ( $page_id <= 0 || '' === $html ) {
		return false;
	}

	$page = get_post( $page_id );

	if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type ) {
		return false;
	}

	$version = (int) get_post_meta( $page_id, '_somvio_legal_content_version', true );
	$hash    = md5( $html );
	$stored  = (string) get_post_meta( $page_id, '_somvio_legal_content_hash', true );

	if ( ! $force && $version >= SOMVIO_LEGAL_CONTENT_VERSION && $stored === $hash && trim( (string) $page->post_content ) === $html ) {
		return false;
	}

	$result = wp_update_post(
		array(
			'ID'           => $page_id,
			'post_content' => $html,
			'post_status'  => 'publish',
		),
		true
	);

	if ( is_wp_error( $result ) || ! $result ) {
		return false;
	}

	update_post_meta( $page_id, '_somvio_legal_content_version', SOMVIO_LEGAL_CONTENT_VERSION );
	update_post_meta( $page_id, '_somvio_legal_content_hash', $hash );

	return true;
}

/**
 * Ensure a single legal page exists, has template + seeded content.
 *
 * @param string $slug  Page slug.
 * @param string $title Page title.
 * @return int Page ID or 0.
 */
function somvio_ensure_legal_page( $slug, $title ) {
	$slug  = sanitize_title( (string) $slug );
	$title = (string) $title;

	if ( '' === $slug || '' === $title || ! function_exists( 'somvio_ensure_page' ) ) {
		return 0;
	}

	$page_id = 0;

	if ( 'privacy-policy' === $slug ) {
		$page_id = somvio_get_privacy_policy_page_id();
	}

	if ( $page_id <= 0 ) {
		$page_id = somvio_ensure_page( $slug, $title );
	}

	if ( $page_id <= 0 ) {
		return 0;
	}

	$updates = array(
		'ID'          => $page_id,
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
	);

	wp_update_post( $updates );

	update_post_meta( $page_id, '_wp_page_template', 'page-legal.php' );

	$html = somvio_get_legal_page_seed_content( $slug );
	if ( '' !== $html ) {
		somvio_seed_legal_page_content( $page_id, $html, true );
	}

	if ( 'privacy-policy' === $slug && (int) get_option( 'wp_page_for_privacy_policy' ) !== $page_id ) {
		update_option( 'wp_page_for_privacy_policy', $page_id );
	}

	return $page_id;
}

/**
 * Ensure / seed every registered legal page.
 *
 * Migrates legacy `terms-of-use` → `terms-conditions` when needed.
 *
 * @return array<string, int> Slug => page ID.
 */
function somvio_ensure_all_legal_pages() {
	$ids = array();

	/* Prefer renaming legacy terms-of-use to terms-conditions when target missing. */
	if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
		$legacy = somvio_get_page_id_by_slug( 'terms-of-use' );
		$modern = somvio_get_page_id_by_slug( 'terms-conditions' );
		if ( $legacy > 0 && $modern <= 0 ) {
			wp_update_post(
				array(
					'ID'         => $legacy,
					'post_name'  => 'terms-conditions',
					'post_title' => 'Terms & Conditions',
				)
			);
		}
	}

	foreach ( somvio_get_legal_pages_registry() as $slug => $meta ) {
		$title       = isset( $meta['title'] ) ? (string) $meta['title'] : $slug;
		$ids[ $slug ] = somvio_ensure_legal_page( $slug, $title );
	}

	return $ids;
}

/**
 * Force-overwrite Privacy Policy (kept for older callers).
 *
 * @return int Canonical page ID or 0.
 */
function somvio_force_seed_privacy_policy_content() {
	$ids = somvio_ensure_all_legal_pages();
	return isset( $ids['privacy-policy'] ) ? (int) $ids['privacy-policy'] : 0;
}

/**
 * One-shot / versioned force reseed for legal pages (front + admin).
 *
 * @return void
 */
function somvio_maybe_force_seed_legal_content() {
	if ( (int) get_option( 'somvio_legal_content_seed_version', 0 ) >= SOMVIO_LEGAL_CONTENT_VERSION ) {
		return;
	}

	somvio_ensure_all_legal_pages();
	update_option( 'somvio_legal_content_seed_version', SOMVIO_LEGAL_CONTENT_VERSION, false );
}
add_action( 'init', 'somvio_maybe_force_seed_legal_content', 25 );

/**
 * Redirect legacy /terms-of-use/ → /terms-conditions/.
 *
 * @return void
 */
function somvio_redirect_legacy_terms_of_use() {
	if ( is_admin() || ! is_page( 'terms-of-use' ) ) {
		return;
	}

	$target = home_url( '/terms-conditions/' );
	if ( function_exists( 'somvio_get_terms_conditions_page_id' ) ) {
		$id = somvio_get_terms_conditions_page_id();
		if ( $id > 0 ) {
			$permalink = get_permalink( $id );
			if ( $permalink ) {
				$target = $permalink;
			}
		}
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'somvio_redirect_legacy_terms_of_use', 5 );

/**
 * Hero args for the current legal page.
 *
 * @return array{title:string,breadcrumb:string,lead:string,aria_label:string}
 */
function somvio_get_current_legal_hero_args() {
	$slug     = '';
	$post_obj = get_queried_object();

	if ( $post_obj instanceof WP_Post ) {
		$slug = (string) $post_obj->post_name;
	}

	if ( 'terms-of-use' === $slug ) {
		$slug = 'terms-conditions';
	}

	$registry = somvio_get_legal_pages_registry();
	$meta     = isset( $registry[ $slug ] ) ? $registry[ $slug ] : array();
	$title    = isset( $meta['title'] ) ? (string) $meta['title'] : get_the_title();
	$lead     = isset( $meta['lead'] ) ? (string) $meta['lead'] : '';

	if ( '' === $title ) {
		$title = __( 'Legal', 'somvio' );
	}

	return array(
		'title'      => $title,
		'breadcrumb' => $title,
		'lead'       => $lead,
		'aria_label' => $title,
	);
}
