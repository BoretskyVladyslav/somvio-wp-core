<?php
/**
 * Homepage hero section via GeneratePress hooks.
 *
 * Figma node: 310:4965
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view should show the homepage hero.
 *
 * True for the WP Front Page (`is_front_page()`), and as a fallback for a
 * Page with slug `home` before it is assigned in Settings → Reading.
 *
 * @return bool
 */
function somvio_is_hero_page() {
	if ( is_front_page() ) {
		return true;
	}

	// Fallback while the static Front Page is not configured yet.
	if ( is_page( 'home' ) ) {
		return true;
	}

	return (bool) apply_filters( 'somvio_is_hero_page', false );
}

/**
 * Mark hero pages so CSS can merge the transparent header with the hero BG.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function somvio_hero_body_class( $classes ) {
	if ( somvio_is_hero_page() ) {
		$classes[] = 'somvio-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'somvio_hero_body_class' );

/**
 * Render the homepage hero directly below the custom header.
 *
 * @return void
 */
function somvio_render_hero() {
	if ( ! somvio_is_hero_page() ) {
		return;
	}

	get_template_part( 'template-parts/hero/hero' );
}
add_action( 'generate_after_header', 'somvio_render_hero', 5 );

/**
 * Current view LCP photo (theme path or remote URL).
 *
 * @return array{src: string, webp?: string, width: int, height: int}
 */
function somvio_get_current_lcp_image() {
	$relative = '';

	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		$relative = 'assets/images/hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_services_page' ) && somvio_is_services_page() ) {
		$relative = 'assets/images/hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_service_single_page' ) && somvio_is_service_single_page() ) {
		$relative = 'assets/images/service-single-hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_about_page' ) && somvio_is_about_page() ) {
		$relative = 'assets/images/about-hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_faq_page' ) && somvio_is_faq_page() ) {
		$relative = 'assets/images/faq-hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_contact_page' ) && somvio_is_contact_page() ) {
		$relative = 'assets/images/faq-hero-bg.jpg';
	} elseif ( function_exists( 'somvio_is_blog_page' ) && somvio_is_blog_page() ) {
		$relative = 'assets/images/blog-hero-bg.jpg';
	} elseif ( is_404() ) {
		$relative = 'assets/images/blog/featured-2.png';
	} elseif ( function_exists( 'somvio_is_blog_single' ) && somvio_is_blog_single() ) {
		$thumb_id = get_post_thumbnail_id();
		if ( $thumb_id ) {
			$img = wp_get_attachment_image_src( (int) $thumb_id, 'full' );
			if ( is_array( $img ) && ! empty( $img[0] ) ) {
				return array(
					'src'    => (string) $img[0],
					'width'  => isset( $img[1] ) ? (int) $img[1] : 1920,
					'height' => isset( $img[2] ) ? (int) $img[2] : 1080,
				);
			}
		}
		$relative = 'assets/images/blog-hero-bg.jpg';
	}

	if ( '' === $relative ) {
		return array(
			'src'    => '',
			'webp'   => '',
			'width'  => 0,
			'height' => 0,
		);
	}

	$path = get_stylesheet_directory() . '/' . $relative;
	if ( ! is_file( $path ) ) {
		return array(
			'src'    => '',
			'webp'   => '',
			'width'  => 0,
			'height' => 0,
		);
	}

	$width  = 1920;
	$height = 1080;
	$size   = getimagesize( $path );
	if ( is_array( $size ) ) {
		$width  = (int) $size[0];
		$height = (int) $size[1];
	}

	$src  = get_stylesheet_directory_uri() . '/' . $relative . '?v=' . rawurlencode( (string) filemtime( $path ) );
	$webp = somvio_theme_webp_uri( $relative );

	return array(
		'src'    => $src,
		'webp'   => $webp,
		'width'  => max( 1, $width ),
		'height' => max( 1, $height ),
	);
}

/**
 * WebP sibling URI for a theme-relative raster image, if the file exists.
 *
 * @param string $relative Theme-relative path (e.g. assets/images/hero-bg.jpg).
 * @return string
 */
function somvio_theme_webp_uri( $relative ) {
	$relative = ltrim( (string) $relative, '/' );
	$webp_rel = (string) preg_replace( '/\.(jpe?g|png)$/i', '.webp', $relative );
	if ( $webp_rel === $relative ) {
		return '';
	}

	$path = get_stylesheet_directory() . '/' . $webp_rel;
	if ( ! is_file( $path ) ) {
		return '';
	}

	return get_stylesheet_directory_uri() . '/' . $webp_rel . '?v=' . rawurlencode( (string) filemtime( $path ) );
}

/**
 * Print decorative LCP <img> with fetchpriority=high.
 *
 * @param string $class Extra class names.
 * @return void
 */
function somvio_render_lcp_image( $class = '' ) {
	$image = somvio_get_current_lcp_image();
	if ( '' === $image['src'] ) {
		return;
	}

	$classes = trim( 'somvio-lcp-img ' . (string) $class );
	$webp    = isset( $image['webp'] ) ? (string) $image['webp'] : '';

	echo '<picture class="somvio-lcp-picture">';
	if ( '' !== $webp ) {
		printf(
			'<source srcset="%s" type="image/webp">',
			esc_url( $webp )
		);
	}
	printf(
		'<img class="%1$s" src="%2$s" alt="" width="%3$d" height="%4$d" fetchpriority="high" loading="eager" decoding="async">',
		esc_attr( $classes ),
		esc_url( $image['src'] ),
		(int) $image['width'],
		(int) $image['height']
	);
	echo '</picture>';
}

/**
 * Preload the LCP hero photo as early as possible.
 *
 * @return void
 */
function somvio_preload_lcp_hero() {
	if ( is_admin() ) {
		return;
	}

	$image = somvio_get_current_lcp_image();
	if ( '' === $image['src'] ) {
		return;
	}

	if ( isset( $image['webp'] ) && '' !== (string) $image['webp'] ) {
		printf(
			'<link rel="preload" as="image" href="%s" type="image/webp" fetchpriority="high">' . "\n",
			esc_url( (string) $image['webp'] )
		);
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
		esc_url( $image['src'] )
	);
}
add_action( 'wp_head', 'somvio_preload_lcp_hero', 2 );
