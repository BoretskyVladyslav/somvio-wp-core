<?php
/**
 * FAQ page hero — Figma 300:2369.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section
	class="faq-hero"
	aria-label="<?php esc_attr_e( 'Frequently Asked Questions', 'somvio' ); ?>"
>
	<div class="faq-hero__media" aria-hidden="true">
		<?php
		if ( function_exists( 'somvio_render_lcp_image' ) ) {
			somvio_render_lcp_image();
		}
		?>
	</div>
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
						<?php esc_html_e( 'FAQ', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 id="faq-hero-title" class="faq-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
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
