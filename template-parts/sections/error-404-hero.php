<?php
/**
 * 404 hero section — Figma 420:6896.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
$somvio_book_url = function_exists( 'somvio_get_book_now_url' )
	? somvio_get_book_now_url()
	: esc_url( home_url( '/booking/' ) );

$somvio_bg_path = get_stylesheet_directory() . '/assets/images/404-bg.png';
$somvio_bg_uri  = get_stylesheet_directory_uri() . '/assets/images/404-bg.png';

if ( file_exists( $somvio_bg_path ) ) {
	$somvio_bg_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bg_path ) );
}
?>
<section
	class="error-404-hero"
	aria-label="<?php esc_attr_e( 'Page not found', 'somvio' ); ?>"
	<?php if ( file_exists( $somvio_bg_path ) ) : ?>
		style="--error-404-hero-bg: url('<?php echo esc_url( $somvio_bg_uri ); ?>');"
	<?php endif; ?>
>
	<div class="error-404-hero__media" aria-hidden="true"></div>
	<div class="error-404-hero__bg" aria-hidden="true"></div>

	<div class="error-404-hero__inner">
		<nav
			class="error-404-hero__breadcrumbs reveal-on-scroll"
			aria-label="<?php esc_attr_e( 'Breadcrumb', 'somvio' ); ?>"
		>
			<ol class="error-404-hero__breadcrumb-list">
				<li class="error-404-hero__breadcrumb-item">
					<a class="error-404-hero__breadcrumb-link" href="<?php echo $somvio_home_url; ?>">
						<?php esc_html_e( 'Home', 'somvio' ); ?>
					</a>
				</li>
				<li
					class="error-404-hero__breadcrumb-item error-404-hero__breadcrumb-item--current"
					aria-current="page"
				>
					<span class="error-404-hero__breadcrumb-sep" aria-hidden="true">
						<?php
						// Trusted local theme SVG from assets/icons/.
						echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="error-404-hero__breadcrumb-current">404</span>
				</li>
			</ol>
		</nav>

		<h1 class="error-404-hero__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
			<?php esc_html_e( 'Oops! This Page Is Sparkling... Gone.', 'somvio' ); ?>
		</h1>

		<p class="error-404-hero__text reveal-on-scroll" style="--reveal-delay: 0.1s;">
			<?php
			esc_html_e(
				"The page you're looking for doesn't exist or may have been moved. Let's help you get back to a cleaner experience.",
				'somvio'
			);
			?>
		</p>

		<div class="error-404-hero__actions reveal-on-scroll" style="--reveal-delay: 0.15s;">
			<a class="btn btn--primary btn--md" href="<?php echo $somvio_home_url; ?>">
				<span class="btn__label"><?php esc_html_e( 'Back to Home', 'somvio' ); ?></span>
			</a>
			<a class="btn btn--outline btn--md" href="<?php echo esc_url( $somvio_book_url ); ?>">
				<span class="btn__label"><?php esc_html_e( 'Book a Cleaning', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</section>
