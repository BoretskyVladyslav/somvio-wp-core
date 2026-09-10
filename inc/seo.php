<?php
/**
 * SEO fallbacks compatible with Rank Math / Yoast (title-tag + wp_head).
 *
 * Document <title> and meta descriptions are owned by SEO plugins when active.
 * Theme fallbacks apply only when a plugin has not set a value.
 *
 * wp_head() lives in the parent GeneratePress header.php — do not copy it here.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Rank Math or Yoast is managing head titles/descriptions.
 *
 * @return bool
 */
function somvio_seo_plugin_active() {
	return defined( 'RANK_MATH_VERSION' )
		|| class_exists( 'RankMath' )
		|| defined( 'WPSEO_VERSION' )
		|| defined( 'WPSEO_FILE' );
}

/**
 * Default visible H1 / fallback document title per view.
 *
 * @return string
 */
function somvio_seo_page_title() {
	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		return __( 'Professional Cleaning Services in Glasgow', 'somvio' );
	}

	if ( function_exists( 'somvio_is_about_page' ) && somvio_is_about_page() ) {
		return __( 'About Somvio Cleaning', 'somvio' );
	}

	if ( function_exists( 'somvio_is_booking_page' ) && somvio_is_booking_page() ) {
		return __( 'Book a Cleaning Service', 'somvio' );
	}

	if ( function_exists( 'somvio_is_services_page' ) && somvio_is_services_page() ) {
		return __( 'Cleaning Services in Glasgow', 'somvio' );
	}

	if ( function_exists( 'somvio_is_contact_page' ) && somvio_is_contact_page() ) {
		return __( 'Contact Somvio Cleaning', 'somvio' );
	}

	if ( function_exists( 'somvio_is_faq_page' ) && somvio_is_faq_page() ) {
		return __( 'Frequently Asked Questions', 'somvio' );
	}

	if ( function_exists( 'somvio_is_service_single_page' ) && somvio_is_service_single_page() ) {
		$title = get_the_title();
		if ( ! is_string( $title ) || '' === $title ) {
			return __( 'Cleaning Services in Glasgow', 'somvio' );
		}
		if ( false === stripos( $title, 'glasgow' ) ) {
			/* translators: %s: service name */
			return sprintf( __( '%s in Glasgow', 'somvio' ), $title );
		}

		return $title;
	}

	return '';
}

/**
 * Fallback meta description when no SEO plugin output exists.
 *
 * @return string
 */
function somvio_seo_page_description() {
	if ( function_exists( 'somvio_is_hero_page' ) && somvio_is_hero_page() ) {
		return __( 'Professional home cleaning in Glasgow and nearby areas. Instant quotes, vetted cleaners, and fully insured service from Somvio.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_about_page' ) && somvio_is_about_page() ) {
		return __( 'Somvio is a fully insured Glasgow cleaning company. Background-checked professionals, transparent pricing, and a satisfaction guarantee.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_booking_page' ) && somvio_is_booking_page() ) {
		return __( 'Book a Somvio cleaning in Glasgow. Choose your service, rooms, and date — get an instant quote and confirm online.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_services_page' ) && somvio_is_services_page() ) {
		return __( 'Regular, deep, end of tenancy, Airbnb, and after-builders cleaning across Glasgow, Renfrewshire, and Dunbartonshire.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_contact_page' ) && somvio_is_contact_page() ) {
		return __( 'Contact Somvio Cleaning in Glasgow. Call, WhatsApp, or send a message for quotes and bookings across Greater Glasgow.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_faq_page' ) && somvio_is_faq_page() ) {
		return __( 'Answers about Somvio cleaning in Glasgow: booking, insurance, cancellations, supplies, and secure online payment.', 'somvio' );
	}

	if ( function_exists( 'somvio_is_service_single_page' ) && somvio_is_service_single_page() ) {
		$title = get_the_title();
		if ( is_string( $title ) && '' !== $title ) {
			/* translators: %s: service name */
			return sprintf(
				__( '%s by Somvio in Glasgow. Instant online quotes, insured cleaners, and flexible booking.', 'somvio' ),
				$title
			);
		}
	}

	return '';
}

/**
 * Fill WP document title parts when Rank Math / Yoast are not active.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function somvio_seo_document_title_parts( $parts ) {
	if ( ! is_array( $parts ) || somvio_seo_plugin_active() ) {
		return $parts;
	}

	$title = somvio_seo_page_title();
	if ( '' === $title ) {
		return $parts;
	}

	$parts['title'] = $title;
	unset( $parts['tagline'] );
	if ( empty( $parts['site'] ) ) {
		$parts['site'] = get_bloginfo( 'name', 'display' );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'somvio_seo_document_title_parts', 25 );

/**
 * Rank Math: only fill an empty title (never override editor-set titles).
 *
 * @param string $title Title.
 * @return string
 */
function somvio_rank_math_fallback_title( $title ) {
	if ( is_string( $title ) && '' !== trim( $title ) ) {
		return $title;
	}

	$fallback = somvio_seo_page_title();

	return '' !== $fallback ? $fallback : $title;
}
add_filter( 'rank_math/frontend/title', 'somvio_rank_math_fallback_title', 20 );

/**
 * Rank Math: only fill an empty description.
 *
 * @param string $description Description.
 * @return string
 */
function somvio_rank_math_fallback_description( $description ) {
	if ( is_string( $description ) && '' !== trim( wp_strip_all_tags( $description ) ) ) {
		return $description;
	}

	$fallback = somvio_seo_page_description();

	return '' !== $fallback ? $fallback : $description;
}
add_filter( 'rank_math/frontend/description', 'somvio_rank_math_fallback_description', 20 );

/**
 * Yoast: only fill an empty title.
 *
 * @param string $title Title.
 * @return string
 */
function somvio_yoast_fallback_title( $title ) {
	return somvio_rank_math_fallback_title( $title );
}
add_filter( 'wpseo_title', 'somvio_yoast_fallback_title', 20 );

/**
 * Yoast: only fill an empty description.
 *
 * @param string $description Description.
 * @return string
 */
function somvio_yoast_fallback_description( $description ) {
	return somvio_rank_math_fallback_description( $description );
}
add_filter( 'wpseo_metadesc', 'somvio_yoast_fallback_description', 20 );

/**
 * Native meta description when no SEO plugin is present.
 *
 * @return void
 */
function somvio_output_fallback_meta_description() {
	if ( somvio_seo_plugin_active() || is_admin() ) {
		return;
	}

	$description = somvio_seo_page_description();
	if ( '' === $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'somvio_output_fallback_meta_description', 1 );
