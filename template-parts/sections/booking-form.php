<?php
/**
 * Booking form + order summary — Figma 418:6213 / Step 1: 418:6214.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_bf_services = function_exists( 'somvio_get_quote_service_options' ) ? somvio_get_quote_service_options() : array();
$somvio_bf_rates    = function_exists( 'somvio_get_quote_rates' ) ? somvio_get_quote_rates() : array();
$somvio_bf_addons   = isset( $somvio_bf_rates['addons'] ) && is_array( $somvio_bf_rates['addons'] ) ? $somvio_bf_rates['addons'] : array();
$somvio_bf_slots    = isset( $somvio_bf_rates['time_slots'] ) && is_array( $somvio_bf_rates['time_slots'] ) ? $somvio_bf_rates['time_slots'] : array();
$somvio_bf_symbol   = isset( $somvio_bf_rates['symbol'] ) ? (string) $somvio_bf_rates['symbol'] : '£';
$somvio_bf_uid      = 'bf-' . wp_unique_id();

$somvio_bf_img_path = get_stylesheet_directory() . '/assets/images/booking/service-card.jpg';
$somvio_bf_img_uri  = get_stylesheet_directory_uri() . '/assets/images/booking/service-card.jpg';
if ( file_exists( $somvio_bf_img_path ) ) {
	$somvio_bf_img_uri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bf_img_path ) );
}

$somvio_bf_icons_uri = get_stylesheet_directory_uri() . '/assets/icons/';

$somvio_bf_privacy_url = function_exists( 'somvio_get_privacy_policy_page_id' )
	? get_permalink( somvio_get_privacy_policy_page_id() )
	: home_url( '/privacy-policy/' );
$somvio_bf_terms_id    = function_exists( 'somvio_get_page_id_by_slug' )
	? somvio_get_page_id_by_slug( 'terms-of-use' )
	: 0;
$somvio_bf_terms_url   = $somvio_bf_terms_id > 0
	? get_permalink( $somvio_bf_terms_id )
	: home_url( '/terms-of-use/' );

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
	class="booking-form"
	aria-label="<?php esc_attr_e( 'Book your cleaning', 'somvio' ); ?>"
	data-booking-form
	data-step="1"
>
	<div class="booking-form__layout">
		<div class="booking-form__main">
			<form class="booking-form__form" data-booking-form-el novalidate>
				<p class="booking-form__error" data-booking-error hidden role="alert"></p>

				<?php /* —— Step 1: Choose service (Figma 418:6214) —— */ ?>
				<div class="booking-form__card booking-form__card--step1" data-booking-step="1" data-booking-panel>
					<h2 class="booking-form__step-title">
						<span class="booking-form__step-num" aria-hidden="true">1.</span>
						<?php esc_html_e( 'Choose service', 'somvio' ); ?>
					</h2>

					<div
						class="booking-form__services"
						role="radiogroup"
						aria-label="<?php esc_attr_e( 'Service type', 'somvio' ); ?>"
					>
						<?php
						$somvio_bf_first = true;
						foreach ( $somvio_bf_services as $somvio_bf_key => $somvio_bf_label ) :
							$somvio_bf_active = $somvio_bf_first;
							$somvio_bf_first  = false;
							?>
							<button
								type="button"
								class="booking-form__service<?php echo $somvio_bf_active ? ' is-selected' : ''; ?>"
								data-booking-service="<?php echo esc_attr( $somvio_bf_key ); ?>"
								role="radio"
								aria-checked="<?php echo $somvio_bf_active ? 'true' : 'false'; ?>"
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
											src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-outline.png' ); ?>"
											alt=""
											width="24"
											height="24"
										>
										<img
											class="booking-form__service-check-img booking-form__service-check-img--on"
											src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-filled.png' ); ?>"
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
					<input type="hidden" name="service" data-booking-field="service" value="regular-cleaning">

					<div class="booking-form__counters">
						<?php
						$somvio_bf_counter_i = 0;
						foreach ( $somvio_bf_counters as $somvio_bf_ckey => $somvio_bf_counter ) :
							$somvio_bf_counter_active = 0 === $somvio_bf_counter_i;
							++$somvio_bf_counter_i;
							?>
							<div
								class="booking-form__counter<?php echo $somvio_bf_counter_active ? ' is-active' : ''; ?>"
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
						<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next>
							<span class="booking-form__spinner" data-booking-spinner hidden aria-hidden="true"></span>
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

				<?php /* —— Step 2: Extra Services —— */ ?>
				<div class="booking-form__card" data-booking-step="2" data-booking-panel hidden>
					<h2 class="booking-form__step-title">
						<span class="booking-form__step-num" aria-hidden="true">2.</span>
						<?php esc_html_e( 'Extra Services', 'somvio' ); ?>
					</h2>

					<div class="booking-form__addons" role="group" aria-label="<?php esc_attr_e( 'Extra services', 'somvio' ); ?>">
						<?php foreach ( $somvio_bf_addons as $somvio_bf_akey => $somvio_bf_addon ) : ?>
							<?php
							$somvio_bf_alabel = isset( $somvio_bf_addon['label'] ) ? (string) $somvio_bf_addon['label'] : $somvio_bf_akey;
							$somvio_bf_aprice = isset( $somvio_bf_addon['price'] ) ? (float) $somvio_bf_addon['price'] : 0;
							$somvio_bf_aicon  = isset( $somvio_bf_addon['icon'] ) ? (string) $somvio_bf_addon['icon'] : '';
							$somvio_bf_auri   = '' !== $somvio_bf_aicon ? $somvio_bf_icons_uri . $somvio_bf_aicon : '';
							?>
							<button
								type="button"
								class="booking-form__addon"
								data-booking-addon="<?php echo esc_attr( $somvio_bf_akey ); ?>"
								aria-pressed="false"
							>
								<span class="booking-form__addon-top">
									<span class="booking-form__addon-price">
										<?php
										/* translators: %s: price with currency */
										echo esc_html( sprintf( __( 'From %s', 'somvio' ), $somvio_bf_symbol . number_format_i18n( $somvio_bf_aprice, 0 ) ) );
										?>
									</span>
									<?php if ( '' !== $somvio_bf_auri ) : ?>
										<img
											class="booking-form__addon-icon"
											src="<?php echo esc_url( $somvio_bf_auri ); ?>"
											alt=""
											width="60"
											height="60"
										>
									<?php endif; ?>
								</span>
								<span class="booking-form__addon-footer">
									<span class="booking-form__service-check" aria-hidden="true">
										<img
											class="booking-form__service-check-img booking-form__service-check-img--off"
											src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-outline.png' ); ?>"
											alt=""
											width="24"
											height="24"
										>
										<img
											class="booking-form__service-check-img booking-form__service-check-img--on"
											src="<?php echo esc_url( $somvio_bf_icons_uri . 'icon-check-circle-filled.png' ); ?>"
											alt=""
											width="24"
											height="24"
										>
									</span>
									<span class="booking-form__addon-label"><?php echo esc_html( $somvio_bf_alabel ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="booking-form__footer">
						<button type="button" class="booking-form__back btn btn--outline btn--has-icon" data-booking-back>
							<span class="btn__icon" aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<span class="btn__label"><?php esc_html_e( 'Back', 'somvio' ); ?></span>
						</button>
						<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next>
							<span class="btn__label" data-booking-next-label><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
							<span class="btn__icon" data-booking-next-icon aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</button>
						<p class="booking-form__step-label" data-booking-step-label>
							<?php echo esc_html( sprintf( __( 'Step %1$d of %2$d', 'somvio' ), 2, 4 ) ); ?>
						</p>
					</div>
				</div>

				<?php /* —— Step 3: Get Your Date —— */ ?>
				<div class="booking-form__card" data-booking-step="3" data-booking-panel hidden>
					<h2 class="booking-form__step-title">
						<span class="booking-form__step-num" aria-hidden="true">3.</span>
						<?php esc_html_e( 'Get Your Date', 'somvio' ); ?>
					</h2>

					<div class="booking-form__field">
						<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-date-display">
							<?php esc_html_e( 'Estimated move date:', 'somvio' ); ?>
						</label>
						<div class="booking-form__select-wrap">
							<input
								type="text"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-date-display"
								data-booking-date-display
								value=""
								placeholder="<?php esc_attr_e( 'Select date', 'somvio' ); ?>"
								readonly
								aria-live="polite"
							>
							<input type="hidden" name="date" data-booking-field="date" value="">
							<span class="booking-form__chevron" aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>
					</div>

					<div
						class="booking-form__calendar"
						data-booking-calendar
						role="group"
						aria-label="<?php esc_attr_e( 'Choose a date', 'somvio' ); ?>"
					>
						<div class="booking-form__cal-header">
							<button
								type="button"
								class="booking-form__cal-nav"
								data-booking-cal-prev
								aria-label="<?php esc_attr_e( 'Previous month', 'somvio' ); ?>"
							>
								<span aria-hidden="true"><?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</button>
							<p class="booking-form__cal-month" data-booking-cal-label></p>
							<button
								type="button"
								class="booking-form__cal-nav"
								data-booking-cal-next
								aria-label="<?php esc_attr_e( 'Next month', 'somvio' ); ?>"
							>
								<span class="booking-form__cal-nav-icon--next" aria-hidden="true"><?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</button>
						</div>
						<div class="booking-form__cal-weekdays" data-booking-cal-weekdays aria-hidden="true"></div>
						<div class="booking-form__cal-grid" data-booking-cal-grid role="listbox"></div>
					</div>

					<div
						class="booking-form__slots"
						data-booking-slots
						role="radiogroup"
						aria-label="<?php esc_attr_e( 'Preferred time', 'somvio' ); ?>"
					>
						<?php foreach ( $somvio_bf_slots as $somvio_bf_slot ) : ?>
							<button
								type="button"
								class="booking-form__slot"
								data-booking-slot="<?php echo esc_attr( $somvio_bf_slot ); ?>"
								role="radio"
								aria-checked="false"
							>
								<?php echo esc_html( str_replace( '-', ' - ', $somvio_bf_slot ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
					<input type="hidden" name="time" data-booking-field="time" value="">
					<p class="booking-form__field-error" data-booking-field-error="time" hidden role="alert"></p>

					<div class="booking-form__footer">
						<button type="button" class="booking-form__back btn btn--outline btn--has-icon" data-booking-back>
							<span class="btn__icon" aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<span class="btn__label"><?php esc_html_e( 'Back', 'somvio' ); ?></span>
						</button>
						<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next>
							<span class="btn__label" data-booking-next-label><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
							<span class="btn__icon" data-booking-next-icon aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</button>
						<p class="booking-form__step-label" data-booking-step-label>
							<?php echo esc_html( sprintf( __( 'Step %1$d of %2$d', 'somvio' ), 3, 4 ) ); ?>
						</p>
					</div>
				</div>

				<?php /* —— Step 4: Contact —— */ ?>
				<div class="booking-form__card" data-booking-step="4" data-booking-panel hidden>
					<h2 class="booking-form__step-title">
						<span class="booking-form__step-num" aria-hidden="true">4.</span>
						<?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?>
					</h2>

					<div class="booking-form__contact-grid">
						<div class="booking-form__field">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-first">
								<?php esc_html_e( 'First Name', 'somvio' ); ?>
							</label>
							<input
								type="text"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-first"
								name="first_name"
								data-booking-field="first_name"
								autocomplete="given-name"
								required
							>
							<p class="booking-form__field-error" data-booking-field-error="first_name" hidden role="alert"></p>
						</div>
						<div class="booking-form__field">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-last">
								<?php esc_html_e( 'Last Name', 'somvio' ); ?>
							</label>
							<input
								type="text"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-last"
								name="last_name"
								data-booking-field="last_name"
								autocomplete="family-name"
								required
							>
							<p class="booking-form__field-error" data-booking-field-error="last_name" hidden role="alert"></p>
						</div>
						<div class="booking-form__field">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-phone">
								<?php esc_html_e( 'Phone', 'somvio' ); ?>
							</label>
							<input
								type="tel"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-phone"
								name="phone"
								data-booking-field="phone"
								autocomplete="tel"
								inputmode="tel"
								required
							>
							<p class="booking-form__field-error" data-booking-field-error="phone" hidden role="alert"></p>
						</div>
						<div class="booking-form__field">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-email">
								<?php esc_html_e( 'Email', 'somvio' ); ?>
							</label>
							<input
								type="email"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-email"
								name="email"
								data-booking-field="email"
								autocomplete="email"
								inputmode="email"
								required
							>
							<p class="booking-form__field-error" data-booking-field-error="email" hidden role="alert"></p>
						</div>
						<div class="booking-form__field booking-form__field--full">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-address">
								<?php esc_html_e( 'Street Address', 'somvio' ); ?>
							</label>
							<input
								type="text"
								class="booking-form__input"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-address"
								name="address"
								data-booking-field="address"
								autocomplete="street-address"
								required
							>
							<p class="booking-form__field-error" data-booking-field-error="address" hidden role="alert"></p>
						</div>
						<div class="booking-form__field booking-form__field--full">
							<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-comment">
								<?php esc_html_e( 'Comment', 'somvio' ); ?>
							</label>
							<textarea
								class="booking-form__textarea"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-comment"
								name="comment"
								data-booking-field="comment"
								rows="5"
							></textarea>
						</div>
					</div>

					<label class="booking-form__terms">
						<input
							type="checkbox"
							class="booking-form__terms-input"
							name="terms_accepted"
							data-booking-field="terms_accepted"
							value="1"
							required
						>
						<span class="booking-form__terms-box" aria-hidden="true"></span>
						<span class="booking-form__terms-text">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: 1: terms URL, 2: privacy URL */
									__( 'I have read and accepted the <a href="%1$s">Terms &amp; Conditions</a> and <a href="%2$s">Privacy Policy</a>.', 'somvio' ),
									esc_url( $somvio_bf_terms_url ? (string) $somvio_bf_terms_url : home_url( '/terms-of-use/' ) ),
									esc_url( $somvio_bf_privacy_url ? (string) $somvio_bf_privacy_url : home_url( '/privacy-policy/' ) )
								),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
										'rel'    => array(),
									),
								)
							);
							?>
						</span>
					</label>
					<p class="booking-form__field-error" data-booking-field-error="terms_accepted" hidden role="alert"></p>

					<div class="booking-form__footer">
						<button type="button" class="booking-form__back btn btn--outline btn--has-icon" data-booking-back>
							<span class="btn__icon" aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<span class="btn__label"><?php esc_html_e( 'Back', 'somvio' ); ?></span>
						</button>
						<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next aria-busy="false">
							<span class="booking-form__spinner" data-booking-spinner hidden aria-hidden="true"></span>
							<span class="btn__label" data-booking-next-label><?php esc_html_e( 'Complete Booking', 'somvio' ); ?></span>
							<span class="btn__icon" data-booking-next-icon aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</button>
						<p class="booking-form__step-label" data-booking-step-label>
							<?php echo esc_html( sprintf( __( 'Step %1$d of %2$d', 'somvio' ), 4, 4 ) ); ?>
						</p>
					</div>
				</div>

				<?php /* —— Step 5: Success —— */ ?>
				<div class="booking-form__card booking-form__card--success" data-booking-step="5" data-booking-panel hidden>
					<div class="booking-form__success">
						<span class="booking-form__success-icon" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<p class="booking-form__success-title"><?php esc_html_e( 'Thank you!', 'somvio' ); ?></p>
						<p class="booking-form__success-subtitle"><?php esc_html_e( 'Your request has been sent', 'somvio' ); ?></p>
						<p class="booking-form__success-text">
							<?php esc_html_e( 'We’ll contact you shortly to confirm the details.', 'somvio' ); ?>
						</p>
					</div>
				</div>
			</form>
		</div>

		<aside class="booking-form__summary" data-booking-summary aria-labelledby="booking-summary-heading">
			<div class="booking-form__summary-card">
				<h2 id="booking-summary-heading" class="booking-form__summary-title">
					<?php esc_html_e( 'Order Summary', 'somvio' ); ?>
				</h2>

				<dl class="booking-form__summary-list">
					<div class="booking-form__summary-row">
						<dt><?php esc_html_e( 'Service', 'somvio' ); ?></dt>
						<dd data-summary-service><?php esc_html_e( 'Regular Cleaning', 'somvio' ); ?></dd>
					</div>
					<div class="booking-form__summary-row">
						<dt><?php esc_html_e( 'Rooms', 'somvio' ); ?></dt>
						<dd data-summary-rooms>1 bed · 1 toilet · 1 kitchen · 1 bath</dd>
					</div>
					<div class="booking-form__summary-row">
						<dt><?php esc_html_e( 'Extras', 'somvio' ); ?></dt>
						<dd data-summary-extras><?php esc_html_e( 'None', 'somvio' ); ?></dd>
					</div>
					<div class="booking-form__summary-row">
						<dt><?php esc_html_e( 'Date &amp; time', 'somvio' ); ?></dt>
						<dd data-summary-datetime><?php esc_html_e( 'Not selected', 'somvio' ); ?></dd>
					</div>
					<div class="booking-form__summary-row">
						<dt><?php esc_html_e( 'Address', 'somvio' ); ?></dt>
						<dd data-summary-address><?php esc_html_e( 'Not selected', 'somvio' ); ?></dd>
					</div>
				</dl>

				<div class="booking-form__summary-total-wrap">
					<p class="booking-form__summary-total-label"><?php esc_html_e( 'Estimated total', 'somvio' ); ?></p>
					<p class="booking-form__summary-total" data-summary-total aria-hidden="false"><?php echo esc_html( $somvio_bf_symbol ); ?>0.00</p>
					<p class="booking-form__summary-note">
						<?php esc_html_e( 'Preview only — final price confirmed after review.', 'somvio' ); ?>
					</p>
					<p class="sr-only" data-summary-live aria-live="polite" aria-atomic="true"></p>
				</div>
			</div>
		</aside>
	</div>
</section>
