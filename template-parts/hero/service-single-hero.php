<?php
/**
 * Single Service page hero — Figma 362:4968.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url     = esc_url( home_url( '/' ) );
$somvio_services_url = esc_url( home_url( '/services/' ) );
$somvio_quote_url    = '#somvio-instant-quote';
$somvio_title        = get_the_title();

if ( ! is_string( $somvio_title ) || '' === $somvio_title ) {
	$somvio_title = __( 'Regular Cleaning', 'somvio' );
}
?>
<section
	class="service-single-hero"
	aria-label="<?php echo esc_attr( $somvio_title ); ?>"
>
	<div class="service-single-hero__bg" aria-hidden="true"></div>

	<div class="service-single-hero__inner">
		<div class="service-single-hero__grid">
			<div class="service-single-hero__content">
				<nav
					class="service-single-hero__breadcrumbs reveal-on-scroll"
					aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
				>
					<ol class="service-single-hero__breadcrumb-list">
						<li class="service-single-hero__breadcrumb-item">
							<a class="service-single-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
								<?php esc_html_e( 'Home', 'somvio' ); ?>
							</a>
						</li>
						<li
							class="service-single-hero__breadcrumb-item service-single-hero__breadcrumb-item--current"
							aria-current="page"
						>
							<span class="service-single-hero__breadcrumb-sep" aria-hidden="true">
								<?php
								// Trusted local theme SVG from assets/icons/.
								echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
							<span class="service-single-hero__breadcrumb-current">
								<?php esc_html_e( 'Services', 'somvio' ); ?>
							</span>
						</li>
					</ol>
				</nav>

				<h1 class="service-single-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
					<?php echo esc_html( $somvio_title ); ?>
				</h1>

				<p class="service-single-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
					<?php
					esc_html_e(
						'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.',
						'somvio'
					);
					?>
				</p>

				<div class="service-single-hero__actions reveal-on-scroll" style="--reveal-delay: 0.15s;">
					<a class="btn btn--primary btn--md" href="<?php echo esc_url( $somvio_quote_url ); ?>">
						<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
					</a>
					<a class="btn btn--outline btn--md" href="<?php echo $somvio_services_url; ?>">
						<span class="btn__label"><?php esc_html_e( 'Our Services', 'somvio' ); ?></span>
					</a>
				</div>
			</div>

			<?php
			$somvio_default_service = function_exists( 'somvio_quote_service_key_from_title' )
				? somvio_quote_service_key_from_title( $somvio_title )
				: 'regular-cleaning';

			get_template_part(
				'template-parts/components/quote',
				'calculator',
				array(
					'variant'         => 'glass',
					'id'              => 'somvio-instant-quote',
					'class'           => 'service-single-hero__quote reveal-on-scroll',
					'default_service' => $somvio_default_service,
				)
			);
			?>
		</div>
	</div>
</section>
