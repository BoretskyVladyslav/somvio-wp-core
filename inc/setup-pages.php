<?php
/**
 * Programmatic core pages + single service pages + static front page setup.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var int Bump to re-run page seeding on admin_init / theme switch. */
const SOMVIO_CORE_PAGES_VERSION = 6;

/**
 * Core pages to ensure exist (slug => title).
 *
 * @return array<string, string>
 */
function somvio_get_core_pages() {
	return array(
		'home'     => 'Home',
		'services' => 'Services',
		'about-us' => 'About Us',
		'reviews'  => 'Reviews',
		'faq'      => 'FAQ',
		'booking'  => 'Booking',
		'contact'  => 'Contact',
		'blog'     => 'Blog',
	);
}

/**
 * Single service pages (slug => title). Office Cleaning excluded.
 *
 * @return array<string, string>
 */
function somvio_get_single_service_pages() {
	return array(
		'regular-cleaning' => 'Regular Cleaning',
		'deep-cleaning'    => 'Deep Cleaning',
		'end-of-tenancy'   => 'End of Tenancy',
		'airbnb-cleaning'  => 'Airbnb Cleaning',
		'after-builders'   => 'After Builders',
	);
}

/**
 * Find a published page ID by path (supports nested paths like services/regular-cleaning).
 *
 * @param string $path Page path relative to site root.
 * @return int
 */
function somvio_get_page_id_by_path( $path ) {
	$path = trim( (string) $path, '/' );

	if ( '' === $path ) {
		return 0;
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return (int) $page->ID;
	}

	return 0;
}

/**
 * Find a published page ID by top-level slug, or 0 if missing.
 *
 * @param string $slug Page slug.
 * @return int
 */
function somvio_get_page_id_by_slug( $slug ) {
	return somvio_get_page_id_by_path( sanitize_title( $slug ) );
}

/**
 * Resolve a single-service page ID (under Services parent or top-level).
 *
 * @param string $slug Service slug.
 * @return int
 */
function somvio_get_service_page_id( $slug ) {
	$slug = sanitize_title( $slug );

	if ( '' === $slug ) {
		return 0;
	}

	$id = somvio_get_page_id_by_path( 'services/' . $slug );

	if ( $id > 0 ) {
		return $id;
	}

	return somvio_get_page_id_by_slug( $slug );
}

/**
 * Permalink for a single-service page (fallback path if not created yet).
 *
 * @param string $slug Service slug.
 * @return string
 */
function somvio_get_service_page_url( $slug ) {
	$id = somvio_get_service_page_id( $slug );

	if ( $id > 0 ) {
		$url = get_permalink( $id );
		return $url ? (string) $url : home_url( '/services/' . sanitize_title( $slug ) . '/' );
	}

	return home_url( '/services/' . sanitize_title( $slug ) . '/' );
}

/**
 * Create a page if it does not already exist.
 *
 * @param string $slug      Page slug.
 * @param string $title     Page title.
 * @param int    $parent_id Optional parent page ID.
 * @return int Page ID (existing or newly created), or 0 on failure.
 */
function somvio_ensure_page( $slug, $title, $parent_id = 0 ) {
	$slug      = sanitize_title( $slug );
	$parent_id = absint( $parent_id );

	if ( $parent_id > 0 ) {
		$parent = get_post( $parent_id );
		if ( $parent instanceof WP_Post && 'page' === $parent->post_type ) {
			$existing_id = somvio_get_page_id_by_path( $parent->post_name . '/' . $slug );
			if ( $existing_id > 0 ) {
				return $existing_id;
			}
		}
	}

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
			'post_parent'  => $parent_id,
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
 * Ensure the About Us page exists with the About Us template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_about_page() {
	$page_id = somvio_ensure_page( 'about-us', 'About Us' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post && 'publish' !== $page->post_status ) {
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-about.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-about.php' );
	}

	return $page_id;
}

/**
 * Ensure the FAQ page exists with the FAQ template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_faq_page() {
	$page_id = somvio_ensure_page( 'faq', 'FAQ' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post && 'publish' !== $page->post_status ) {
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-faq.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-faq.php' );
	}

	return $page_id;
}

/**
 * Ensure the Blog page exists with the Blog template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_blog_page() {
	$page_id = somvio_ensure_page( 'blog', 'Blog' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post && 'publish' !== $page->post_status ) {
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			)
		);
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-blog.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-blog.php' );
	}

	return $page_id;
}

/**
 * Ensure a single-service page exists with the Single Service template.
 *
 * @param string $slug      Service slug.
 * @param string $title     Page title.
 * @param int    $parent_id Services parent page ID.
 * @return int Page ID or 0.
 */
function somvio_ensure_service_page( $slug, $title, $parent_id = 0 ) {
	$page_id = somvio_ensure_page( $slug, $title, $parent_id );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$updates = array();

	$page = get_post( $page_id );

	if ( $page instanceof WP_Post ) {
		if ( $parent_id > 0 && (int) $page->post_parent !== $parent_id ) {
			$updates['post_parent'] = $parent_id;
		}

		if ( 'publish' !== $page->post_status ) {
			$updates['post_status'] = 'publish';
		}
	}

	if ( ! empty( $updates ) ) {
		$updates['ID'] = $page_id;
		wp_update_post( $updates );
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-single-service.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-single-service.php' );
	}

	return $page_id;
}

/**
 * Ensure all core + single-service pages exist and Reading settings use Home + Blog.
 *
 * Safe to call repeatedly: pages are looked up by path before insert.
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
			if ( 'about-us' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_about_page();
				continue;
			}

			if ( 'faq' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_faq_page();
				continue;
			}

			if ( 'blog' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_blog_page();
				continue;
			}

			$page_ids[ $slug ] = somvio_ensure_page( $slug, $title );
		}

		$home_id     = isset( $page_ids['home'] ) ? (int) $page_ids['home'] : 0;
		$blog_id     = isset( $page_ids['blog'] ) ? (int) $page_ids['blog'] : 0;
		$services_id = isset( $page_ids['services'] ) ? (int) $page_ids['services'] : 0;

		foreach ( somvio_get_single_service_pages() as $slug => $title ) {
			somvio_ensure_service_page( $slug, $title, $services_id );
		}

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

		somvio_sync_primary_menu_service_links();

		update_option( 'somvio_core_pages_version', SOMVIO_CORE_PAGES_VERSION, false );
	} finally {
		delete_option( 'somvio_core_pages_setup_lock' );
	}
}

/**
 * Sync primary nav Services children to published single-service page URLs.
 *
 * Rewrites custom/hash links; creates missing child items under Services when needed.
 *
 * @return void
 */
function somvio_sync_primary_menu_service_links() {
	$locations = get_nav_menu_locations();

	if ( empty( $locations['primary'] ) ) {
		return;
	}

	$menu_id = (int) $locations['primary'];

	if ( $menu_id <= 0 || ! is_nav_menu( $menu_id ) ) {
		return;
	}

	$services = somvio_get_single_service_pages();
	$items    = wp_get_nav_menu_items( $menu_id );

	if ( ! is_array( $items ) ) {
		$items = array();
	}

	$services_parent_id = 0;

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		$path = wp_parse_url( (string) $item->url, PHP_URL_PATH );
		$path = is_string( $path ) ? untrailingslashit( $path ) : '';

		if ( 'Services' === $item->title || preg_match( '#/services$#', $path ) ) {
			$services_parent_id = (int) $item->ID;
			break;
		}
	}

	if ( $services_parent_id <= 0 ) {
		return;
	}

	$matched_slugs = array();

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		if ( (int) $item->menu_item_parent !== $services_parent_id ) {
			continue;
		}

		$slug = somvio_match_service_slug_from_menu_item( $item, $services );

		if ( null === $slug ) {
			continue;
		}

		$page_id = somvio_get_service_page_id( $slug );

		if ( $page_id <= 0 ) {
			continue;
		}

		$matched_slugs[] = $slug;

		wp_update_nav_menu_item(
			$menu_id,
			(int) $item->ID,
			array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $services_parent_id,
				'menu-item-title'     => $services[ $slug ],
			)
		);
	}

	$position = 1;

	foreach ( $services as $slug => $title ) {
		if ( in_array( $slug, $matched_slugs, true ) ) {
			++$position;
			continue;
		}

		$page_id = somvio_get_service_page_id( $slug );

		if ( $page_id <= 0 ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $services_parent_id,
				'menu-item-title'     => $title,
				'menu-item-position'  => $position,
			)
		);

		++$position;
	}
}

/**
 * Match a nav item to a single-service slug via title, path, or hash fragment.
 *
 * @param WP_Post              $item     Menu item.
 * @param array<string,string> $services slug => title.
 * @return string|null
 */
function somvio_match_service_slug_from_menu_item( $item, $services ) {
	$title = trim( (string) $item->title );

	foreach ( $services as $slug => $label ) {
		if ( 0 === strcasecmp( $title, $label ) ) {
			return $slug;
		}
	}

	$fragment = wp_parse_url( (string) $item->url, PHP_URL_FRAGMENT );
	if ( is_string( $fragment ) && isset( $services[ $fragment ] ) ) {
		return $fragment;
	}

	$path = wp_parse_url( (string) $item->url, PHP_URL_PATH );
	$path = is_string( $path ) ? untrailingslashit( $path ) : '';

	foreach ( array_keys( $services ) as $slug ) {
		if ( preg_match( '#/(?:services/)?' . preg_quote( $slug, '#' ) . '$#', $path ) ) {
			return $slug;
		}
	}

	return null;
}

/**
 * Runtime nav rewrite: hash/legacy service URLs → published page permalinks.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  wp_nav_menu() args.
 * @return WP_Post[]
 */
function somvio_nav_menu_service_page_urls( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || empty( $items ) ) {
		return $items;
	}

	$services = somvio_get_single_service_pages();

	foreach ( $items as $item ) {
		$slug = somvio_match_service_slug_from_menu_item( $item, $services );

		if ( null === $slug ) {
			continue;
		}

		$page_id = somvio_get_service_page_id( $slug );

		if ( $page_id <= 0 ) {
			continue;
		}

		$permalink = get_permalink( $page_id );

		if ( $permalink ) {
			$item->url = $permalink;
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'somvio_nav_menu_service_page_urls', 20, 2 );

/**
 * Run page setup once per version (admin / theme switch).
 *
 * @return void
 */
function somvio_maybe_setup_core_pages() {
	if ( (int) get_option( 'somvio_core_pages_version', 0 ) >= SOMVIO_CORE_PAGES_VERSION ) {
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
