<?php
/**
 * Booking form — Block 1 isolation (Figma 418:6214).
 * Pixel-aligned Choose service card. Steps 2–5 temporarily omitted.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_bf_services = function_exists( 'somvio_get_quote_service_options' ) ? somvio_get_quote_service_options() : array();
$somvio_bf_uid      = 'bf-' . wp_unique_id();

$somvio_bf_img_path = get_stylesheet_directory() . '/assets/images/booking/service-card.jpg';
$somvio_bf_img_uri  = get_stylesheet_directory_uri() . '/assets/images/booking/service-card.jpg';
if ( file_exists( $somvio_bf_img_path ) ) {
	$somvio_bf_img_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bf_img_path ) );
}

$somvio_bf_icons_uri = get_stylesheet_directory_uri() . '/assets/icons/';

$somvio_bf_counters = array(
	'bedrooms'  => array(
		'label' => __( 'Bedrooms', 'somvio' ),
		'min'   => 1,
		'max'   => 5,
		'value' => 1,
	),
	'toilets'   => array(
		'label' => __( 'Toilets', 'somvio' ),
		'min'   => 0,
		'max'   => 5,
		'value' => 1,
	),
	'kitchens'  => array(
		'label' => __( 'Kitchens', 'somvio' ),
		'min'   => 0,
		'max'   => 5,
		'value' => 1,
	),
	'bathrooms' => array(
		'label' => __( 'Bathrooms', 'somvio' ),
		'min'   => 1,
		'max'   => 4,
		'value' => 1,
	),
);
?>
<section
	class="booking-form booking-form--block1"
	aria-label="<?php esc_attr_e( 'Book your cleaning', 'somvio' ); ?>"
	data-booking-form
	data-booking-isolate="1"
	data-step="1"
>
	<div class="booking-form__layout">
		<form class="booking-form__form" data-booking-form-el novalidate>
			<p class="booking-form__error" data-booking-error hidden role="alert"></p>

			<?php /* —— Step 1: Choose service — Figma 418:6214 —— */ ?>
			<div class="booking-form__card booking-form__card--step1" data-booking-step="1" data-booking-panel>
				<h2 class="booking-form__step-title">
					<span class="booking-form__step-num" aria-hidden="true">1.</span>
					<?php esc_html_e( 'Choose service', 'somvio' ); ?>
				</h2>

				<div
					class="booking-form__services"
					role="radiogroup"
					aria-label="<?php esc_attr_e( 'Service type', 'somvio' ); ?>"
					aria-required="true"
				>
					<?php foreach ( $somvio_bf_services as $somvio_bf_key => $somvio_bf_label ) : ?>
						<button
							type="button"
							class="booking-form__service"
							data-booking-service="<?php echo esc_attr( $somvio_bf_key ); ?>"
							role="radio"
							aria-checked="false"
						>
							<span class="booking-form__service-media">
								<img
									class="booking-form__service-img"
									src="<?php echo esc_url( $somvio_bf_img_uri ); ?>"
									alt=""
									width="240"
									height="200"
									loading="lazy"
									decoding="async"
								>
							</span>
							<span class="booking-form__service-footer">
								<span class="booking-form__service-check" aria-hidden="true">
									<img
										class="booking-form__service-check-img booking-form__service-check-img--off"
										src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-outline.svg' ); ?>"
										alt=""
										width="24"
										height="24"
									>
									<img
										class="booking-form__service-check-img booking-form__service-check-img--on"
										src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-filled.svg' ); ?>"
										alt=""
										width="24"
										height="24"
									>
								</span>
								<span class="booking-form__service-label"><?php echo esc_html( $somvio_bf_label ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
				<input type="hidden" name="service" data-booking-field="service" value="">

				<div class="booking-form__counters">
					<?php foreach ( $somvio_bf_counters as $somvio_bf_ckey => $somvio_bf_counter ) : ?>
						<div
							class="booking-form__counter"
							data-booking-counter="<?php echo esc_attr( $somvio_bf_ckey ); ?>"
						>
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid . '-' . $somvio_bf_ckey ); ?>">
								<?php echo esc_html( $somvio_bf_counter['label'] ); ?>
							</label>
							<div class="booking-form__counter-control" data-booking-counter-control>
								<button
									type="button"
									class="booking-form__counter-btn booking-form__counter-btn--minus"
									data-booking-counter-dec
									aria-label="<?php echo esc_attr( sprintf( /* translators: %s: room type */ __( 'Decrease %s', 'somvio' ), $somvio_bf_counter['label'] ) ); ?>"
								>
									<span aria-hidden="true"><?php echo somvio_get_icon( 'icon-minus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</button>
								<input
									type="number"
									class="booking-form__counter-value"
									id="<?php echo esc_attr( $somvio_bf_uid . '-' . $somvio_bf_ckey ); ?>"
									name="<?php echo esc_attr( $somvio_bf_ckey ); ?>"
									data-booking-field="<?php echo esc_attr( $somvio_bf_ckey ); ?>"
									value="<?php echo esc_attr( (string) $somvio_bf_counter['value'] ); ?>"
									min="<?php echo esc_attr( (string) $somvio_bf_counter['min'] ); ?>"
									max="<?php echo esc_attr( (string) $somvio_bf_counter['max'] ); ?>"
									readonly
									aria-live="polite"
								>
								<button
									type="button"
									class="booking-form__counter-btn booking-form__counter-btn--plus"
									data-booking-counter-inc
									aria-label="<?php echo esc_attr( sprintf( /* translators: %s: room type */ __( 'Increase %s', 'somvio' ), $somvio_bf_counter['label'] ) ); ?>"
								>
									<span aria-hidden="true"><?php echo somvio_get_icon( 'icon-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="booking-form__footer">
					<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next disabled aria-disabled="true" title="<?php esc_attr_e( 'Select a service to continue', 'somvio' ); ?>">
						<span class="btn__label" data-booking-next-label><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
						<span class="btn__icon" data-booking-next-icon aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</button>
					<p class="booking-form__step-label" data-booking-step-label>
						<?php
						/* translators: 1: current step, 2: total steps */
						echo esc_html( sprintf( __( 'Step %1$d of %2$d', 'somvio' ), 1, 4 ) );
						?>
					</p>
				</div>
			</div>

			<?php
			/*
			 * Steps 2–5 temporarily removed for Block 1 isolation.
			 * Restore Extra Services / Date / Contact / Success from git history when ready.
			 */
			?>
		</form>
	</div>
</section>
