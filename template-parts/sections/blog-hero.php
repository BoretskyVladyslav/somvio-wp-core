<?php
/**
 * Blog page hero — Figma 300:2181.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section
	class="blog-hero"
	aria-label="<?php esc_attr_e( 'Blog', 'somvio' ); ?>"
>
	<div class="blog-hero__media" aria-hidden="true">
		<?php
		if ( function_exists( 'somvio_render_lcp_image' ) ) {
			somvio_render_lcp_image();
		}
		?>
	</div>
	<div class="blog-hero__bg" aria-hidden="true"></div>

	<div class="blog-hero__inner">
		<nav
			class="blog-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="blog-hero__breadcrumb-list">
				<li class="blog-hero__breadcrumb-item">
					<a class="blog-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="blog-hero__breadcrumb-item blog-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="blog-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="blog-hero__breadcrumb-current">
						<?php esc_html_e( 'Blog', 'somvio' ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="blog-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Blog', 'somvio' ); ?>
		</h1>

		<p class="blog-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				'Clean spaces. Better living. High-quality cleaning services for homes and businesses across the UK.',
				'somvio'
			);
			?>
		</p>
	</div>
</section>
