<?php
/**
 * About Us page hero — Figma 384:5980.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url     = esc_url( home_url( '/' ) );
$somvio_services_url = esc_url( home_url( '/services/' ) );
$somvio_booking_url  = function_exists( 'somvio_get_book_now_url' )
	? somvio_get_book_now_url()
	: esc_url( home_url( '/booking/' ) );
?>
<section class="about-hero" aria-label="<?php esc_attr_e( 'About Somvio', 'somvio' ); ?>">
	<div class="about-hero__bg" aria-hidden="true"></div>

	<div class="about-hero__inner">
		<nav
			class="about-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="about-hero__breadcrumb-list">
				<li class="about-hero__breadcrumb-item">
					<a class="about-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="about-hero__breadcrumb-item about-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="about-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="about-hero__breadcrumb-current">
						<?php esc_html_e( 'About Us', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="about-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'About', 'somvio' ); ?>
			<span class="about-hero__title-accent"><?php esc_html_e( 'Somvio', 'somvio' ); ?></span>
		</h1>

		<p class="about-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				'At Somvio, we believe a clean environment creates a healthier, happier and more productive life. Our mission is simple: deliver exceptional cleaning services with complete reliability, transparent pricing and outstanding customer care.',
				'somvio'
			);
			?>
		</p>

		<div class="about-hero__actions reveal-on-scroll" style="--reveal-delay: 0.15s;">
			<a class="btn btn--primary btn--md" href="<?php echo esc_url( $somvio_booking_url ); ?>">
				<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
			</a>
			<a class="btn btn--outline btn--md" href="<?php echo $somvio_services_url; ?>">
				<span class="btn__label"><?php esc_html_e( 'Our Services', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</section>
