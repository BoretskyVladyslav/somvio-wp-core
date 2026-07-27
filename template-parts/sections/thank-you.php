<?php
/**
 * Thank You section — booking confirmation summary.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_home_url = esc_url( home_url( '/' ) );
?>
<section
	class="thank-you"
	aria-labelledby="thank-you-title"
	data-thank-you
>
	<div class="thank-you__inner">
		<span class="thank-you__icon" aria-hidden="true">
			<?php echo somvio_get_icon( 'icon-check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>

		<h1 id="thank-you-title" class="thank-you__title">
			<?php esc_html_e( 'Thank you!', 'somvio' ); ?>
		</h1>

		<p class="thank-you__subtitle" data-thank-you-subtitle>
			<?php esc_html_e( 'Your booking request has been received.', 'somvio' ); ?>
		</p>

		<p class="thank-you__text">
			<?php esc_html_e( 'We’ll contact you shortly to confirm the details.', 'somvio' ); ?>
		</p>

		<dl class="thank-you__recap" data-thank-you-recap hidden>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Service', 'somvio' ); ?></dt>
				<dd data-thank-you-field="service"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Date', 'somvio' ); ?></dt>
				<dd data-thank-you-field="date"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Time', 'somvio' ); ?></dt>
				<dd data-thank-you-field="time"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Name', 'somvio' ); ?></dt>
				<dd data-thank-you-field="name"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Phone', 'somvio' ); ?></dt>
				<dd data-thank-you-field="phone"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Email', 'somvio' ); ?></dt>
				<dd data-thank-you-field="email"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Address', 'somvio' ); ?></dt>
				<dd data-thank-you-field="address"></dd>
			</div>
			<div class="thank-you__row">
				<dt><?php esc_html_e( 'Estimated total', 'somvio' ); ?></dt>
				<dd data-thank-you-field="total"></dd>
			</div>
			<div class="thank-you__row" data-thank-you-booking-row hidden>
				<dt><?php esc_html_e( 'Booking ref', 'somvio' ); ?></dt>
				<dd data-thank-you-field="booking_id"></dd>
			</div>
		</dl>

		<a class="btn btn--primary btn--md thank-you__home" href="<?php echo $somvio_home_url; ?>">
			<span class="btn__label"><?php esc_html_e( 'Back to Home', 'somvio' ); ?></span>
		</a>
	</div>
</section>
