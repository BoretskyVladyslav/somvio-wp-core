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
const SOMVIO_CORE_PAGES_VERSION = 17;

/**
 * Whether a site title looks like a Local / stock WordPress default.
 *
 * @param string $name Site title.
 * @return bool
 */
function somvio_is_default_site_name( $name ) {
	$normalized = strtolower( trim( (string) $name ) );

	$defaults = array(
		'',
		'my wordpress',
		'wordpress',
		'just another wordpress site',
		'my local wordpress site',
		'local wordpress site',
		'local site',
	);

	return in_array( $normalized, $defaults, true );
}

/**
 * Whether a tagline looks like a stock WordPress default.
 *
 * @param string $desc Tagline.
 * @return bool
 */
function somvio_is_default_site_tagline( $desc ) {
	$normalized = strtolower( trim( (string) $desc ) );

	$defaults = array(
		'',
		'just another wordpress site',
		'just another wordpress site.',
	);

	return in_array( $normalized, $defaults, true );
}

/**
 * Ensure site title + tagline are Somvio (not default "My WordPress").
 *
 * Only overwrites empty or stock WordPress / Local defaults so intentional
 * Customizer edits stick. Reads raw option values (filters removed) so
 * display-time filters cannot hide a bad DB value from persistence.
 *
 * @return void
 */
function somvio_ensure_site_identity() {
	$had_name = has_filter( 'option_blogname', 'somvio_filter_option_blogname' );
	$had_desc = has_filter( 'option_blogdescription', 'somvio_filter_option_blogdescription' );

	if ( $had_name ) {
		remove_filter( 'option_blogname', 'somvio_filter_option_blogname' );
	}
	if ( $had_desc ) {
		remove_filter( 'option_blogdescription', 'somvio_filter_option_blogdescription' );
	}

	$name = (string) get_option( 'blogname', '' );
	$desc = (string) get_option( 'blogdescription', '' );

	if ( somvio_is_default_site_name( $name ) ) {
		update_option( 'blogname', 'Somvio' );
	}

	if ( somvio_is_default_site_tagline( $desc ) ) {
		update_option( 'blogdescription', 'Clean Spaces. Better Living.' );
	}

	if ( $had_name ) {
		add_filter( 'option_blogname', 'somvio_filter_option_blogname' );
	}
	if ( $had_desc ) {
		add_filter( 'option_blogdescription', 'somvio_filter_option_blogdescription' );
	}
}

/**
 * Run identity cleanup early on every request (front + admin).
 *
 * Page seeding is version-gated; identity must still correct Local defaults
 * that reappear without a version bump.
 *
 * @return void
 */
function somvio_maybe_ensure_site_identity() {
	somvio_ensure_site_identity();
}
add_action( 'init', 'somvio_maybe_ensure_site_identity', 5 );

/**
 * Scrub document &lt;title&gt; parts of leftover WordPress defaults.
 *
 * Front page typically becomes "Somvio - Clean Spaces. Better Living." when
 * WP uses site + tagline; inner pages keep "Page – Somvio".
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function somvio_filter_document_title_parts( $parts ) {
	if ( ! is_array( $parts ) ) {
		return $parts;
	}

	if ( isset( $parts['title'] ) && somvio_is_default_site_name( $parts['title'] ) ) {
		$parts['title'] = 'Somvio';
	}

	if ( isset( $parts['site'] ) && somvio_is_default_site_name( $parts['site'] ) ) {
		$parts['site'] = 'Somvio';
	}

	if ( isset( $parts['tagline'] ) && somvio_is_default_site_tagline( $parts['tagline'] ) ) {
		$parts['tagline'] = 'Clean Spaces. Better Living.';
	}

	return $parts;
}
add_filter( 'document_title_parts', 'somvio_filter_document_title_parts', 20 );

/**
 * Safety net for bloginfo( 'name' ) / bloginfo( 'description' ) consumers.
 *
 * @param string $output Filtered value.
 * @param string $show   bloginfo field.
 * @return string
 */
function somvio_filter_bloginfo( $output, $show ) {
	if ( 'name' === $show && somvio_is_default_site_name( $output ) ) {
		return 'Somvio';
	}

	if ( 'description' === $show && somvio_is_default_site_tagline( $output ) ) {
		return 'Clean Spaces. Better Living.';
	}

	return $output;
}
add_filter( 'bloginfo', 'somvio_filter_bloginfo', 20, 2 );

/**
 * Catch plugins / SEO tools that read blogname / blogdescription options directly.
 *
 * @param mixed $value Option value.
 * @return mixed
 */
function somvio_filter_option_blogname( $value ) {
	if ( somvio_is_default_site_name( (string) $value ) ) {
		return 'Somvio';
	}

	return $value;
}
add_filter( 'option_blogname', 'somvio_filter_option_blogname' );

/**
 * @param mixed $value Option value.
 * @return mixed
 */
function somvio_filter_option_blogdescription( $value ) {
	if ( somvio_is_default_site_tagline( (string) $value ) ) {
		return 'Clean Spaces. Better Living.';
	}

	return $value;
}
add_filter( 'option_blogdescription', 'somvio_filter_option_blogdescription' );

/**
 * Prefer a hyphen separator consistent with brand titles.
 *
 * @param string $sep Separator.
 * @return string
 */
function somvio_document_title_separator( $sep ) {
	return '-';
}
add_filter( 'document_title_separator', 'somvio_document_title_separator' );

/**
 * Core pages to ensure exist (slug => title).
 *
 * @return array<string, string>
 */
function somvio_get_core_pages() {
	$pages = array(
		'home'             => 'Home',
		'services'         => 'Services',
		'about-us'         => 'About Us',
		'reviews'          => 'Reviews',
		'faq'              => 'FAQ',
		'booking'          => 'Booking',
		'thank-you'        => 'Thank You',
		'contact'          => 'Contact',
		'blog'             => 'Blog',
		'privacy-policy'   => 'Privacy Policy',
		'terms-conditions' => 'Terms & Conditions',
		'legal'            => 'Somvio Legal & Policy Pack',
	);

	if ( function_exists( 'somvio_get_legal_pages_registry' ) ) {
		foreach ( somvio_get_legal_pages_registry() as $slug => $meta ) {
			$pages[ $slug ] = isset( $meta['title'] ) ? (string) $meta['title'] : $slug;
		}
	}

	return $pages;
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
 * Header Services dropdown entries matching the homepage 6-card grid
 * (includes a second Regular Cleaning entry for the hallway card).
 *
 * @return array<int, array{slug:string,title:string}>
 */
function somvio_get_services_menu_entries() {
	$pages = somvio_get_single_service_pages();
	$entries = array();

	foreach ( $pages as $slug => $title ) {
		$entries[] = array(
			'slug'  => $slug,
			'title' => $title,
		);
	}

	/* 6th grid card — duplicate Regular Cleaning link. */
	$entries[] = array(
		'slug'  => 'regular-cleaning',
		'title' => $pages['regular-cleaning'],
	);

	return $entries;
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
 * Ensure the Booking page exists with the Booking template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_booking_page() {
	$page_id = somvio_ensure_page( 'booking', 'Booking' );

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

	if ( 'page-booking.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-booking.php' );
	}

	return $page_id;
}

/**
 * Ensure the Thank You page exists with its template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_thank_you_page() {
	$page_id = somvio_ensure_page( 'thank-you', 'Thank You' );

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

	if ( 'page-thank-you.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-thank-you.php' );
	}

	return $page_id;
}

/**
 * Permalink for the Thank You page.
 *
 * @return string
 */
function somvio_get_thank_you_url() {
	$page_id = function_exists( 'somvio_get_page_id_by_slug' )
		? (int) somvio_get_page_id_by_slug( 'thank-you' )
		: 0;

	if ( $page_id > 0 ) {
		$url = get_permalink( $page_id );
		if ( $url ) {
			return (string) $url;
		}
	}

	return home_url( '/thank-you/' );
}

/**
 * Ensure the Contact page exists with the Contact Us template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_contact_page_seed() {
	if ( function_exists( 'somvio_ensure_contact_page' ) ) {
		return somvio_ensure_contact_page();
	}

	$page_id = somvio_ensure_page( 'contact', 'Contact' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	if ( 'page-contact.php' !== $template ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-contact.php' );
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
 * Ensure the Privacy Policy page exists with its template + Legal Pack content.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_privacy_policy_page() {
	if ( function_exists( 'somvio_ensure_legal_page' ) ) {
		return somvio_ensure_legal_page( 'privacy-policy', 'Privacy Policy' );
	}

	$page_id = somvio_ensure_page( 'privacy-policy', 'Privacy Policy' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'page-legal.php' );

	return $page_id;
}

/**
 * Ensure the Terms & Conditions page exists with its template.
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_terms_of_use_page() {
	if ( function_exists( 'somvio_ensure_legal_page' ) ) {
		return somvio_ensure_legal_page( 'terms-conditions', 'Terms & Conditions' );
	}

	$page_id = somvio_ensure_page( 'terms-conditions', 'Terms & Conditions' );

	if ( $page_id <= 0 ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'page-legal.php' );

	return $page_id;
}

/**
 * Ensure Terms & Conditions (canonical slug).
 *
 * @return int Page ID or 0.
 */
function somvio_ensure_terms_conditions_page() {
	return somvio_ensure_terms_of_use_page();
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
		somvio_ensure_site_identity();

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

			if ( 'booking' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_booking_page();
				continue;
			}

			if ( 'thank-you' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_thank_you_page();
				continue;
			}

			if ( 'contact' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_contact_page_seed();
				continue;
			}

			if ( 'blog' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_blog_page();
				continue;
			}

			if ( 'privacy-policy' === $slug ) {
				$page_ids[ $slug ] = somvio_ensure_privacy_policy_page();
				continue;
			}

			if ( 'terms-conditions' === $slug || 'terms-of-use' === $slug ) {
				$page_ids['terms-conditions'] = somvio_ensure_terms_conditions_page();
				continue;
			}

			if ( function_exists( 'somvio_get_legal_pages_registry' ) ) {
				$legal = somvio_get_legal_pages_registry();
				if ( isset( $legal[ $slug ] ) && function_exists( 'somvio_ensure_legal_page' ) ) {
					$title             = isset( $legal[ $slug ]['title'] ) ? (string) $legal[ $slug ]['title'] : $title;
					$page_ids[ $slug ] = somvio_ensure_legal_page( $slug, $title );
					continue;
				}
			}

			$page_ids[ $slug ] = somvio_ensure_page( $slug, $title );
		}

		if ( function_exists( 'somvio_ensure_all_legal_pages' ) ) {
			somvio_ensure_all_legal_pages();
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
		somvio_sync_primary_menu_structure();

		update_option( 'somvio_core_pages_version', SOMVIO_CORE_PAGES_VERSION, false );
	} finally {
		delete_option( 'somvio_core_pages_setup_lock' );
	}
}

/**
 * Sync primary nav Services children to the 6 homepage grid entries
 * (5 unique services + duplicate Regular Cleaning).
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

	$entries = somvio_get_services_menu_entries();
	$items   = wp_get_nav_menu_items( $menu_id );

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

	$children = array();

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		if ( (int) $item->menu_item_parent === $services_parent_id ) {
			$children[] = $item;
		}
	}

	$used_ids = array();
	$position = 1;

	foreach ( $entries as $entry ) {
		$slug  = isset( $entry['slug'] ) ? (string) $entry['slug'] : '';
		$title = isset( $entry['title'] ) ? (string) $entry['title'] : '';

		if ( '' === $slug || '' === $title ) {
			continue;
		}

		$page_id = somvio_get_service_page_id( $slug );

		if ( $page_id <= 0 ) {
			continue;
		}

		$menu_item_id = 0;

		foreach ( $children as $index => $child ) {
			if ( ! $child instanceof WP_Post ) {
				continue;
			}

			if ( in_array( (int) $child->ID, $used_ids, true ) ) {
				continue;
			}

			$child_slug = somvio_match_service_slug_from_menu_item( $child, somvio_get_single_service_pages() );

			if ( $child_slug === $slug ) {
				$menu_item_id = (int) $child->ID;
				$used_ids[]   = $menu_item_id;
				unset( $children[ $index ] );
				break;
			}
		}

		wp_update_nav_menu_item(
			$menu_id,
			$menu_item_id,
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

	/* Remove leftover Commercial / unmatched children. */
	foreach ( $children as $child ) {
		if ( $child instanceof WP_Post && ! in_array( (int) $child->ID, $used_ids, true ) ) {
			wp_delete_post( (int) $child->ID, true );
		}
	}
}

/**
 * Align primary header menu top-level items:
 * Home | Services | About Us | Blog | FAQ | Booking | Contact
 *
 * Removes Reviews; ensures Blog → /blog/ and Contact → /contact/.
 *
 * @return void
 */
function somvio_sync_primary_menu_structure() {
	$locations = get_nav_menu_locations();

	if ( empty( $locations['primary'] ) ) {
		return;
	}

	$menu_id = (int) $locations['primary'];

	if ( $menu_id <= 0 || ! is_nav_menu( $menu_id ) ) {
		return;
	}

	$items = wp_get_nav_menu_items( $menu_id );

	if ( ! is_array( $items ) ) {
		$items = array();
	}

	$remove_titles = array( 'reviews' );
	$remove_paths  = array( '/reviews' );
	$has_blog      = false;
	$has_contact   = false;

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		if ( (int) $item->menu_item_parent > 0 ) {
			continue;
		}

		$title = strtolower( trim( (string) $item->title ) );
		$path  = wp_parse_url( (string) $item->url, PHP_URL_PATH );
		$path  = is_string( $path ) ? untrailingslashit( $path ) : '';

		if ( in_array( $title, $remove_titles, true ) || in_array( $path, $remove_paths, true ) ) {
			wp_delete_post( (int) $item->ID, true );
			continue;
		}

		if ( 'blog' === $title || preg_match( '#/blog$#', $path ) ) {
			$has_blog = true;

			$blog_id = function_exists( 'somvio_get_page_id_by_slug' )
				? (int) somvio_get_page_id_by_slug( 'blog' )
				: 0;

			if ( $blog_id > 0 ) {
				wp_update_nav_menu_item(
					$menu_id,
					(int) $item->ID,
					array(
						'menu-item-object-id' => $blog_id,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => 0,
						'menu-item-title'     => __( 'Blog', 'somvio' ),
					)
				);
			} else {
				wp_update_nav_menu_item(
					$menu_id,
					(int) $item->ID,
					array(
						'menu-item-type'      => 'custom',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => 0,
						'menu-item-title'     => __( 'Blog', 'somvio' ),
						'menu-item-url'       => home_url( '/blog/' ),
					)
				);
			}
		}

		if (
			'contact' === $title
			|| 'contact us' === $title
			|| preg_match( '#/contacts?$#', $path )
		) {
			$has_contact = true;

			$contact_id = function_exists( 'somvio_get_page_id_by_slug' )
				? (int) somvio_get_page_id_by_slug( 'contact' )
				: 0;

			if ( $contact_id > 0 ) {
				wp_update_nav_menu_item(
					$menu_id,
					(int) $item->ID,
					array(
						'menu-item-object-id' => $contact_id,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => 0,
						'menu-item-title'     => __( 'Contact', 'somvio' ),
					)
				);
			} else {
				wp_update_nav_menu_item(
					$menu_id,
					(int) $item->ID,
					array(
						'menu-item-type'      => 'custom',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => 0,
						'menu-item-title'     => __( 'Contact', 'somvio' ),
						'menu-item-url'       => home_url( '/contact/' ),
					)
				);
			}
		}
	}

	if ( ! $has_blog ) {
		$blog_id = function_exists( 'somvio_get_page_id_by_slug' )
			? (int) somvio_get_page_id_by_slug( 'blog' )
			: 0;

		if ( $blog_id > 0 ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $blog_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
					'menu-item-title'     => __( 'Blog', 'somvio' ),
				)
			);
		} else {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-type'      => 'custom',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
					'menu-item-title'     => __( 'Blog', 'somvio' ),
					'menu-item-url'       => home_url( '/blog/' ),
				)
			);
		}
	}

	if ( ! $has_contact ) {
		$contact_id = function_exists( 'somvio_get_page_id_by_slug' )
			? (int) somvio_get_page_id_by_slug( 'contact' )
			: 0;

		if ( $contact_id > 0 ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $contact_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
					'menu-item-title'     => __( 'Contact', 'somvio' ),
				)
			);
		} else {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-type'      => 'custom',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
					'menu-item-title'     => __( 'Contact', 'somvio' ),
					'menu-item-url'       => home_url( '/contact/' ),
				)
			);
		}
	}
}

/**
 * Filter primary nav objects: drop Reviews until DB sync runs.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  wp_nav_menu() args.
 * @return WP_Post[]
 */
function somvio_filter_primary_nav_structure( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || empty( $items ) ) {
		return $items;
	}

	$remove_titles = array( 'reviews' );
	$remove_paths  = array( '/reviews' );
	$filtered      = array();

	foreach ( $items as $item ) {
		$parent = isset( $item->menu_item_parent ) ? (int) $item->menu_item_parent : 0;
		$title  = strtolower( trim( (string) $item->title ) );
		$path   = wp_parse_url( (string) $item->url, PHP_URL_PATH );
		$path   = is_string( $path ) ? untrailingslashit( $path ) : '';

		if ( 0 === $parent && ( in_array( $title, $remove_titles, true ) || in_array( $path, $remove_paths, true ) ) ) {
			continue;
		}

		$filtered[] = $item;
	}

	return $filtered;
}
add_filter( 'wp_nav_menu_objects', 'somvio_filter_primary_nav_structure', 25, 2 );

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
