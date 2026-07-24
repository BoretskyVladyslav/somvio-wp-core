<?php
/**
 * Booking page — full-width quote calculator section.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="booking-quote" aria-labelledby="booking-quote-heading">
	<div class="booking-quote__inner">
		<header class="booking-quote__header">
			<h2 id="booking-quote-heading" class="booking-quote__title">
				<?php esc_html_e( 'Book Your Cleaning', 'somvio' ); ?>
			</h2>
			<p class="booking-quote__subtitle">
				<?php esc_html_e( 'Get an instant quote in four quick steps.', 'somvio' ); ?>
			</p>
		</header>
		<?php
		get_template_part(
			'template-parts/components/quote',
			'calculator',
			array(
				'variant' => 'solid',
				'id'      => 'somvio-instant-quote',
				'class'   => 'booking-quote__card',
			)
		);
		?>
	</div>
</section>
