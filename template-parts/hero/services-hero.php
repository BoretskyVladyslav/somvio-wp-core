<?php
/**
 * Services page compact inner hero — Figma 300:2161.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section class="services-hero" aria-label="<?php esc_attr_e( 'Services', 'somvio' ); ?>">
	<div class="services-hero__bg" aria-hidden="true"></div>

	<div class="services-hero__inner">
		<nav class="services-hero__breadcrumbs reveal-on-scroll" aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>">
			<ol class="services-hero__breadcrumb-list">
				<li class="services-hero__breadcrumb-item">
					<a class="services-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li class="services-hero__breadcrumb-item services-hero__breadcrumb-item--current" aria-current="page">
					<span class="services-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="services-hero__breadcrumb-current">
						<?php esc_html_e( 'Services', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="services-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Services', 'somvio' ); ?>
		</h1>

		<p class="services-hero__subtitle reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php esc_html_e( 'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.', 'somvio' ); ?>
		</p>
	</div>
</section>
