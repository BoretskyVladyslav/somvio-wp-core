<?php
/**
 * Pre-footer CTA banner — Figma 325:5030.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_book = function_exists( 'somvio_get_book_now_url' ) ? somvio_get_book_now_url() : home_url( '/booking/' );
?>
<section class="cta-banner" aria-labelledby="cta-banner-title">
	<div class="cta-banner__overlay" aria-hidden="true"></div>

	<div class="cta-banner__inner reveal-on-scroll">
		<p class="cta-banner__badge"><?php esc_html_e( 'Call to Action', 'somvio' ); ?></p>
		<h2 id="cta-banner-title" class="cta-banner__title">
			<?php esc_html_e( 'Ready for a Cleaner Home?', 'somvio' ); ?>
		</h2>
		<p class="cta-banner__subtitle">
			<?php esc_html_e( 'Book your professional cleaner in less than 2 minutes.', 'somvio' ); ?>
		</p>
		<a class="btn btn--primary btn--md cta-banner__btn" href="<?php echo esc_url( $somvio_book ); ?>">
			<span class="btn__label"><?php esc_html_e( 'Book Now', 'somvio' ); ?></span>
		</a>
	</div>
</section>
