<?php
/**
 * Booking page hero — Figma 418:6207.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section
	class="booking-hero"
	aria-label="<?php esc_attr_e( 'Booking', 'somvio' ); ?>"
>
	<div class="booking-hero__bg" aria-hidden="true"></div>

	<div class="booking-hero__inner">
		<nav
			class="booking-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="booking-hero__breadcrumb-list">
				<li class="booking-hero__breadcrumb-item">
					<a class="booking-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="booking-hero__breadcrumb-item booking-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="booking-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="booking-hero__breadcrumb-current">
						<?php esc_html_e( 'Booking', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="booking-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Booking', 'somvio' ); ?>
		</h1>

		<p class="booking-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				'Get an instant quote in four quick steps.',
				'somvio'
			);
			?>
		</p>
	</div>
</section>
