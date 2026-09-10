<?php
/**
 * Contact Us page hero.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section
	class="contact-hero"
	aria-label="<?php esc_attr_e( 'Contact Us', 'somvio' ); ?>"
>
	<div class="contact-hero__media" aria-hidden="true">
		<?php
		if ( function_exists( 'somvio_render_lcp_image' ) ) {
			somvio_render_lcp_image();
		}
		?>
	</div>
	<div class="contact-hero__bg" aria-hidden="true"></div>

	<div class="contact-hero__inner">
		<nav
			class="contact-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="contact-hero__breadcrumb-list">
				<li class="contact-hero__breadcrumb-item">
					<a class="contact-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="contact-hero__breadcrumb-item contact-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="contact-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="contact-hero__breadcrumb-current">
						<?php esc_html_e( 'Contact', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 id="contact-hero-title" class="contact-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Contact Somvio Cleaning', 'somvio' ); ?>
		</h1>

		<p class="contact-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.',
				'somvio'
			);
			?>
		</p>
	</div>
</section>
