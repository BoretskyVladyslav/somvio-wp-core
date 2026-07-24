<?php
/**
 * Booking form — Figma 418:6214 / 418:6213 (4-step flow, no order summary).
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
if ( ! $somvio_bf_privacy_url ) {
	$somvio_bf_privacy_url = home_url( '/privacy-policy/' );
}
$somvio_bf_terms_id = 0;
if ( function_exists( 'somvio_get_page_id_by_slug' ) ) {
	$somvio_bf_terms_id = (int) somvio_get_page_id_by_slug( 'terms-of-use' );
}
$somvio_bf_terms_url = $somvio_bf_terms_id > 0
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
							tabindex="-1"
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

			<?php /* —— Step 2: Extra Services — Figma 418:6259 —— */ ?>
			<div class="booking-form__card booking-form__card--step2" data-booking-step="2" data-booking-panel hidden>
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
						$somvio_bf_auri   = '';
						if ( '' !== $somvio_bf_aicon ) {
							$somvio_bf_auri  = $somvio_bf_icons_uri . $somvio_bf_aicon;
							$somvio_bf_apath = get_stylesheet_directory() . '/assets/icons/' . $somvio_bf_aicon;
							if ( file_exists( $somvio_bf_apath ) ) {
								$somvio_bf_auri .= '?v=' . rawurlencode( (string) filemtime( $somvio_bf_apath ) );
							}
						}
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
										decoding="async"
									>
								<?php endif; ?>
							</span>
							<span class="booking-form__addon-footer">
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
								<span class="booking-form__addon-label"><?php echo esc_html( $somvio_bf_alabel ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
				<input type="hidden" name="addons" data-booking-field="addons" value="">

				<div class="booking-form__footer">
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

			<?php /* —— Step 3: Get Your Date — Figma 418:6269 —— */ ?>
			<div class="booking-form__card booking-form__card--step3" data-booking-step="3" data-booking-panel hidden>
				<h2 class="booking-form__step-title">
					<span class="booking-form__step-num" aria-hidden="true">3.</span>
					<?php esc_html_e( 'Get Your Date', 'somvio' ); ?>
				</h2>

				<div class="booking-form__date-block" data-booking-date-block>
					<div class="booking-form__field booking-form__field--date">
						<label class="booking-form__label" for="<?php echo esc_attr( $somvio_bf_uid ); ?>-date-display">
							<?php esc_html_e( 'Estimated move date:', 'somvio' ); ?>
						</label>
						<div
							class="booking-form__select-wrap"
							data-booking-date-toggle
							role="button"
							tabindex="0"
							aria-controls="<?php echo esc_attr( $somvio_bf_uid ); ?>-calendar"
							aria-expanded="false"
						>
							<input
								type="text"
								class="booking-form__input booking-form__input--date"
								id="<?php echo esc_attr( $somvio_bf_uid ); ?>-date-display"
								data-booking-date-display
								value=""
								placeholder="<?php esc_attr_e( 'Select date', 'somvio' ); ?>"
								readonly
								aria-live="polite"
								aria-required="true"
								aria-haspopup="dialog"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $somvio_bf_uid ); ?>-calendar"
							>
							<input type="hidden" name="date" data-booking-field="date" value="" required>
							<span class="booking-form__chevron" aria-hidden="true">
								<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>
					</div>

					<div
						class="booking-form__calendar"
						id="<?php echo esc_attr( $somvio_bf_uid ); ?>-calendar"
						data-booking-calendar
						role="dialog"
						aria-label="<?php esc_attr_e( 'Choose a date', 'somvio' ); ?>"
						hidden
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
						<div class="booking-form__cal-grid" data-booking-cal-grid role="group" aria-label="<?php esc_attr_e( 'Calendar days', 'somvio' ); ?>"></div>
					</div>
				</div>

				<div
					class="booking-form__slots is-disabled"
					data-booking-slots
					role="radiogroup"
					aria-label="<?php esc_attr_e( 'Preferred time', 'somvio' ); ?>"
					aria-required="true"
					aria-disabled="true"
				>
					<?php foreach ( $somvio_bf_slots as $somvio_bf_slot ) : ?>
						<?php
						$somvio_bf_slot_label = preg_replace( '/\b0(\d:)/', '$1', str_replace( '-', ' - ', (string) $somvio_bf_slot ) );
						?>
						<button
							type="button"
							class="booking-form__slot"
							data-booking-slot="<?php echo esc_attr( $somvio_bf_slot ); ?>"
							role="radio"
							aria-checked="false"
							tabindex="-1"
						>
							<?php echo esc_html( $somvio_bf_slot_label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<input type="hidden" name="time" data-booking-field="time" value="" required>
				<p class="booking-form__field-error" data-booking-field-error="time" hidden role="alert"></p>
				<p class="booking-form__field-error" data-booking-field-error="date" hidden role="alert"></p>

				<div class="booking-form__footer">
					<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next disabled aria-disabled="true" title="<?php esc_attr_e( 'Select a date and time to continue', 'somvio' ); ?>">
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
			<div class="booking-form__card booking-form__card--step4" data-booking-step="4" data-booking-panel hidden>
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
							placeholder="<?php esc_attr_e( 'John', 'somvio' ); ?>"
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
							placeholder="<?php esc_attr_e( 'Smith', 'somvio' ); ?>"
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
							placeholder="<?php esc_attr_e( '+44 7000 000000', 'somvio' ); ?>"
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
							placeholder="<?php esc_attr_e( 'john@example.com', 'somvio' ); ?>"
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
							placeholder="<?php esc_attr_e( '123 Main St, Postcode', 'somvio' ); ?>"
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
							placeholder="<?php esc_attr_e( 'Any special instructions for our team...', 'somvio' ); ?>"
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
								__( 'I have read and accepted the <a href="%1$s" target="_blank" rel="noopener noreferrer">Terms &amp; Conditions</a> and <a href="%2$s" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.', 'somvio' ),
								esc_url( (string) $somvio_bf_terms_url ),
								esc_url( (string) $somvio_bf_privacy_url )
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
					<button type="button" class="booking-form__next btn btn--primary btn--has-icon" data-booking-next disabled aria-disabled="true" aria-busy="false" title="<?php esc_attr_e( 'Complete required fields and accept the terms to continue', 'somvio' ); ?>">
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
		</form>
	</div>

	<?php /* Success modal — out of page flow; shown only after REST success */ ?>
	<div
		class="booking-form__success-modal"
		data-booking-success-modal
		hidden
		aria-hidden="true"
	>
		<div
			class="booking-form__success-dialog"
			role="dialog"
			aria-modal="true"
			aria-labelledby="<?php echo esc_attr( $somvio_bf_uid ); ?>-success-title"
			tabindex="-1"
		>
			<div class="booking-form__card booking-form__card--success">
				<div class="booking-form__success">
					<span class="booking-form__success-icon" aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<p class="booking-form__success-title" id="<?php echo esc_attr( $somvio_bf_uid ); ?>-success-title">
						<?php esc_html_e( 'Thank you!', 'somvio' ); ?>
					</p>
					<p class="booking-form__success-subtitle"><?php esc_html_e( 'Your request has been sent', 'somvio' ); ?></p>
					<p class="booking-form__success-text">
						<?php esc_html_e( 'We’ll contact you shortly to confirm the details.', 'somvio' ); ?>
					</p>

					<dl class="booking-form__success-recap" data-booking-success-recap>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Service', 'somvio' ); ?></dt>
							<dd data-booking-success="service"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Date', 'somvio' ); ?></dt>
							<dd data-booking-success="date"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Time', 'somvio' ); ?></dt>
							<dd data-booking-success="time"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Name', 'somvio' ); ?></dt>
							<dd data-booking-success="name"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Phone', 'somvio' ); ?></dt>
							<dd data-booking-success="phone"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Email', 'somvio' ); ?></dt>
							<dd data-booking-success="email"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Address', 'somvio' ); ?></dt>
							<dd data-booking-success="address"></dd>
						</div>
						<div class="booking-form__success-row">
							<dt><?php esc_html_e( 'Estimated total', 'somvio' ); ?></dt>
							<dd data-booking-success="total"></dd>
						</div>
					</dl>

					<a class="booking-form__success-home btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Back to Home', 'somvio' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
