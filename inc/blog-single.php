<?php
/**
 * Single blog post — hero body class + demo post seeding.
 *
 * Hero: Figma 300:2415.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var int Bump to re-run demo blog post seeding. */
const SOMVIO_DEMO_BLOG_POST_VERSION = 3;

/**
 * Whether the current view is a single blog post.
 *
 * @return bool
 */
function somvio_is_blog_single() {
	return is_singular( 'post' );
}

/**
 * Mark single posts so the transparent sticky header merges with the hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_blog_single_body_class( $classes ) {
	if ( somvio_is_blog_single() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_blog_single_body_class' );

/**
 * Demo single-post definition (Figma 300:2415 hero + 300:2421 body).
 *
 * @return array{slug:string,title:string,category:string,date:string,content:string,image:string}
 */
function somvio_get_demo_blog_post_definition() {
	$content  = '<p>Keeping your home clean is about more than appearance—it\'s about creating a healthier, more comfortable environment for you and your family. While regular tidying helps maintain order, professional cleaning reaches areas that are often overlooked, removing dust, bacteria and allergens that accumulate over time.</p>';
	$content .= "\n\n";
	$content .= '<p>The ideal cleaning schedule depends on your lifestyle, household size and daily routine. Families with children or pets generally benefit from weekly or bi-weekly cleaning, while smaller households may only require professional cleaning once a month. High-traffic areas such as kitchens, bathrooms and living rooms usually need more frequent attention than guest rooms or storage spaces.</p>';
	$content .= "\n\n";
	$content .= '<p>Professional cleaners use specialized equipment and high-quality cleaning products to achieve results that are difficult to replicate with everyday household supplies. From sanitizing surfaces to removing stubborn dirt and polishing finishes, a professional service helps preserve the condition of your home while saving you valuable time.</p>';
	$content .= "\n\n";
	$content .= '<p>Regular cleaning also improves indoor air quality by reducing dust, pollen and other airborne particles. This can be especially beneficial for people who suffer from allergies or respiratory conditions. Clean floors, fresh upholstery and sanitized bathrooms contribute to a healthier living space throughout the year.</p>';
	$content .= "\n\n";
	$content .= '<p>Many homeowners choose to combine routine maintenance with seasonal deep cleaning. While weekly or bi-weekly visits keep the property looking its best, deep cleaning every three to six months focuses on hard-to-reach areas such as behind furniture, inside appliances, window frames and baseboards. This approach ensures every part of your home receives the attention it deserves.</p>';
	$content .= "\n\n";
	$content .= '<p>If you\'re preparing for a special event, moving into a new property or leaving a rental home, scheduling a professional deep clean can make the process significantly easier. It provides peace of mind and ensures your property looks immaculate from top to bottom.</p>';
	$content .= "\n\n";
	$content .= '<p>At Somvio, we offer flexible cleaning plans designed to fit your schedule and your home\'s unique needs. Whether you\'re looking for a one-time deep clean or regular maintenance, our experienced team is committed to delivering exceptional results with every visit.</p>';
	$content .= "\n\n";
	$content .= '<p>A clean home is an investment in your health, comfort and well-being. By scheduling professional cleaning on a regular basis, you can enjoy more free time while keeping your living space spotless all year round.</p>';

	return array(
		'slug'     => 'how-often-should-you-schedule-professional-cleaning',
		'title'    => 'How Often Should You Schedule Professional Cleaning?',
		'category' => 'Cleaning Tips',
		'date'     => '2024-12-16 10:00:00',
		'content'  => $content,
		'image'    => 'assets/images/blog-single-hero-bg.jpg',
	);
}

/**
 * Ensure a category exists by name; return term ID.
 *
 * @param string $name Category name.
 * @return int
 */
function somvio_ensure_blog_category( $name ) {
	$name = sanitize_text_field( (string) $name );

	if ( '' === $name ) {
		return 0;
	}

	$existing = get_term_by( 'name', $name, 'category' );

	if ( $existing instanceof WP_Term ) {
		return (int) $existing->term_id;
	}

	$inserted = wp_insert_term( $name, 'category' );

	if ( is_wp_error( $inserted ) || empty( $inserted['term_id'] ) ) {
		return 0;
	}

	return (int) $inserted['term_id'];
}

/**
 * Sideload a theme image into the Media Library (idempotent via option).
 *
 * @param string $relative_path Path under the child theme root.
 * @param string $title         Attachment title.
 * @return int Attachment ID or 0.
 */
function somvio_ensure_theme_image_attachment( $relative_path, $title ) {
	$relative_path = ltrim( str_replace( '\\', '/', (string) $relative_path ), '/' );
	$option_key    = 'somvio_attachment_' . md5( $relative_path );
	$existing_id   = (int) get_option( $option_key, 0 );

	if ( $existing_id > 0 && get_post( $existing_id ) instanceof WP_Post ) {
		return $existing_id;
	}

	$path = get_stylesheet_directory() . '/' . $relative_path;

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();

	if ( ! empty( $upload_dir['error'] ) ) {
		return 0;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
	$bytes = file_get_contents( $path );

	if ( false === $bytes ) {
		return 0;
	}

	$filename = wp_unique_filename( $upload_dir['path'], basename( $path ) );
	$upload   = wp_upload_bits( $filename, null, $bytes );

	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		return 0;
	}

	$filetype  = wp_check_filetype( $upload['file'], null );
	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => ! empty( $filetype['type'] ) ? $filetype['type'] : 'image/jpeg',
			'post_title'     => sanitize_text_field( $title ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( (int) $attach_id, $upload['file'] );
	wp_update_attachment_metadata( (int) $attach_id, $meta );
	update_option( $option_key, (int) $attach_id, false );

	return (int) $attach_id;
}

/**
 * Seed the Figma demo blog post (idempotent).
 *
 * @return int Post ID or 0.
 */
function somvio_ensure_demo_blog_post() {
	$def  = somvio_get_demo_blog_post_definition();
	$slug = sanitize_title( $def['slug'] );

	$existing = get_page_by_path( $slug, OBJECT, 'post' );

	if ( $existing instanceof WP_Post ) {
		$post_id = (int) $existing->ID;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_title'    => $def['title'],
				'post_name'     => $slug,
				'post_status'   => 'publish',
				'post_type'     => 'post',
				'post_content'  => $def['content'],
				'post_date'     => $def['date'],
				'post_date_gmt' => get_gmt_from_date( $def['date'] ),
				'post_author'   => 1,
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}

		$post_id = (int) $post_id;
	}

	// Keep title / date / slug / body aligned with the Figma demo.
	wp_update_post(
		array(
			'ID'            => $post_id,
			'post_title'    => $def['title'],
			'post_name'     => $slug,
			'post_content'  => $def['content'],
			'post_status'   => 'publish',
			'post_date'     => $def['date'],
			'post_date_gmt' => get_gmt_from_date( $def['date'] ),
		)
	);

	$cat_id = somvio_ensure_blog_category( $def['category'] );

	if ( $cat_id > 0 ) {
		wp_set_post_categories( $post_id, array( $cat_id ), false );
	}

	$attachment_id = somvio_ensure_theme_image_attachment(
		$def['image'],
		$def['title'] . ' — hero'
	);

	if ( $attachment_id > 0 ) {
		set_post_thumbnail( $post_id, $attachment_id );
	}

	update_option( 'somvio_demo_blog_post_id', $post_id, false );

	return $post_id;
}

/**
 * Run demo blog post seed once per version.
 *
 * @return void
 */
function somvio_maybe_seed_demo_blog_post() {
	if ( (int) get_option( 'somvio_demo_blog_post_version', 0 ) >= SOMVIO_DEMO_BLOG_POST_VERSION ) {
		return;
	}

	if ( get_option( 'somvio_demo_blog_post_setup_lock' ) ) {
		return;
	}

	update_option( 'somvio_demo_blog_post_setup_lock', 1, false );

	try {
		$post_id = somvio_ensure_demo_blog_post();

		if ( $post_id > 0 ) {
			update_option( 'somvio_demo_blog_post_version', SOMVIO_DEMO_BLOG_POST_VERSION, false );
		}
	} finally {
		delete_option( 'somvio_demo_blog_post_setup_lock' );
	}
}
add_action( 'init', 'somvio_maybe_seed_demo_blog_post', 20 );
add_action( 'admin_init', 'somvio_maybe_seed_demo_blog_post', 20 );
add_action( 'after_switch_theme', 'somvio_ensure_demo_blog_post' );
