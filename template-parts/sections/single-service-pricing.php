<?php
/**
 * Single Service — Transparent Pricing section.
 *
 * Figma node: 366:5409
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_image_path = get_stylesheet_directory() . '/assets/images/service-single-pricing.jpg';
$somvio_image_uri  = get_stylesheet_directory_uri() . '/assets/images/service-single-pricing.jpg';

$somvio_service_key = function_exists( 'somvio_get_current_service_key' )
	? somvio_get_current_service_key()
	: 'regular-cleaning';
$somvio_quote_url   = function_exists( 'somvio_get_book_now_url' )
	? somvio_get_book_now_url( $somvio_service_key )
	: home_url( '/booking/?service=' . rawurlencode( $somvio_service_key ) );

$somvio_pricing_rows = array(
	array(
		'label'    => __( 'Studio Apartment', 'somvio' ),
		'bedrooms' => 1,
		'property' => 'apartment',
	),
	array(
		'label'    => __( '1 Bedroom', 'somvio' ),
		'bedrooms' => 1,
		'property' => 'house',
	),
	array(
		'label'    => __( '2 Bedroom', 'somvio' ),
		'bedrooms' => 2,
		'property' => 'house',
	),
	array(
		'label'    => __( '3 Bedroom', 'somvio' ),
		'bedrooms' => 3,
		'property' => 'house',
	),
	array(
		'label'    => __( '4+ Bedrooms', 'somvio' ),
		'bedrooms' => 4,
		'property' => 'house',
	),
);
?>
<section class="service-pricing" aria-labelledby="service-pricing-title">
	<div class="service-pricing__inner">
		<header class="service-pricing__header">
			<p class="service-pricing__badge"><?php esc_html_e( 'Pricing', 'somvio' ); ?></p>
			<h2 id="service-pricing-title" class="service-pricing__title reveal-on-scroll">
				<?php esc_html_e( 'Transparent Pricing', 'somvio' ); ?>
			</h2>
			<p class="service-pricing__subtitle reveal-on-scroll" style="--reveal-delay: 0.05s;">
				<?php esc_html_e( 'Prices from Somvio Settings. Final total depends on property size and extras.', 'somvio' ); ?>
			</p>
		</header>

		<div class="service-pricing__grid">
			<figure class="service-pricing__media reveal-on-scroll">
				<?php if ( file_exists( $somvio_image_path ) ) : ?>
					<img
						class="service-pricing__image"
						src="<?php echo esc_url( $somvio_image_uri ); ?>"
						alt="<?php esc_attr_e( 'Modern dark interior hallway with wood floors', 'somvio' ); ?>"
						width="795"
						height="516"
						loading="lazy"
						decoding="async"
					>
				<?php else : ?>
					<span class="service-pricing__media-missing">
						<?php esc_html_e( 'Missing image: assets/images/service-single-pricing.jpg', 'somvio' ); ?>
					</span>
				<?php endif; ?>
			</figure>

			<div class="service-pricing__panel reveal-on-scroll" style="--reveal-delay: 0.08s;">
				<ul class="service-pricing__list">
					<?php foreach ( $somvio_pricing_rows as $row ) : ?>
						<?php
						$amount = function_exists( 'somvio_get_bedroom_display_price' )
							? somvio_get_bedroom_display_price( $somvio_service_key, (int) $row['bedrooms'], $row['property'] )
							: 0;
						$label_price = function_exists( 'somvio_format_from_price' )
							? somvio_format_from_price( $amount )
							: '';
						?>
						<li class="service-pricing__row">
							<span class="service-pricing__label"><?php echo esc_html( $row['label'] ); ?></span>
							<span class="service-pricing__price"><?php echo esc_html( $label_price ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<a class="btn btn--primary btn--md service-pricing__cta" href="<?php echo esc_url( $somvio_quote_url ); ?>">
					<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
				</a>
			</div>
		</div>
	</div>
</section>
