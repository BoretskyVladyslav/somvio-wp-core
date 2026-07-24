<?php
/**
 * Legal page hero — Privacy Policy (300:2218) / Terms of Use (300:2239).
 *
 * Expected $args:
 * - title       (string) H1 text
 * - breadcrumb  (string) Current crumb label
 * - lead        (string) Subtitle / meta line
 * - aria_label  (string) Section aria-label
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_title      = isset( $args['title'] ) ? (string) $args['title'] : '';
$somvio_breadcrumb = isset( $args['breadcrumb'] ) ? (string) $args['breadcrumb'] : $somvio_title;
$somvio_lead       = isset( $args['lead'] ) ? (string) $args['lead'] : '';
$somvio_aria       = isset( $args['aria_label'] ) ? (string) $args['aria_label'] : $somvio_title;
$somvio_home_url   = esc_url( home_url( '/' ) );

if ( '' === $somvio_title ) {
	return;
}
?>
<section
	class="legal-hero"
	aria-label="<?php echo esc_attr( $somvio_aria ); ?>"
>
	<div class="legal-hero__bg" aria-hidden="true"></div>

	<div class="legal-hero__inner">
		<nav
			class="legal-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="legal-hero__breadcrumb-list">
				<li class="legal-hero__breadcrumb-item">
					<a class="legal-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="legal-hero__breadcrumb-item legal-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="legal-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="legal-hero__breadcrumb-current">
						<?php echo esc_html( $somvio_breadcrumb ); ?>
					</span>
				</li>
			</ol>
		</nav>

		<h1 class="legal-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php echo esc_html( $somvio_title ); ?>
		</h1>

		<?php if ( '' !== $somvio_lead ) : ?>
			<p class="legal-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
				<?php echo esc_html( $somvio_lead ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
