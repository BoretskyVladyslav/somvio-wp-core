<?php
/**
 * FAQ page hero content — Figma 300:2371 (within header 300:2369).
 *
 * Overlay: photo at 40% opacity over #00050e (Figma header img opacity-40).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );

$somvio_bg_path = get_stylesheet_directory() . '/assets/images/faq-hero-bg.jpg';
$somvio_bg_uri  = get_stylesheet_directory_uri() . '/assets/images/faq-hero-bg.jpg';

if ( file_exists( $somvio_bg_path ) ) {
	$somvio_bg_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bg_path ) );
}
?>
<section
	class="faq-hero"
	aria-label="<?php esc_attr_e( 'Frequently Asked Questions', 'somvio' ); ?>"
	style="--faq-hero-bg: url('<?php echo esc_url( $somvio_bg_uri ); ?>');"
>
	<div class="faq-hero__media" aria-hidden="true"></div>
	<div class="faq-hero__bg" aria-hidden="true"></div>

	<div class="faq-hero__inner">
		<nav
			class="faq-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="faq-hero__breadcrumb-list">
				<li class="faq-hero__breadcrumb-item">
					<a class="faq-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="faq-hero__breadcrumb-item faq-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="faq-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="faq-hero__breadcrumb-current">
						<?php esc_html_e( 'Frequently Asked Questions', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="faq-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Frequently Asked Questions', 'somvio' ); ?>
		</h1>

		<p class="faq-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				'Everything you need to know before booking your cleaning service.',
				'somvio'
			);
			?>
		</p>
	</div>
</section>
