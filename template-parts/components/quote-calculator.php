<?php
/**
 * Multi-step Instant Quote calculator component.
 *
 * Figma: 300:1766 (step1), 300:1852 (step2 date), 300:1818 (step3 time),
 * 300:1792 (step4 contact), 409:6039 (success).
 *
 * Args (via get_template_part 3rd param / $args):
 * - variant: 'glass'|'solid' (default glass)
 * - id: optional DOM id (e.g. somvio-instant-quote)
 * - class: extra classes on root
 * - default_service: service key
 * - show_title_steps: bool — hide static title on success (default true)
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_qc_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();

$somvio_qc_variant = isset( $somvio_qc_args['variant'] ) ? sanitize_key( $somvio_qc_args['variant'] ) : 'glass';
$somvio_qc_id      = isset( $somvio_qc_args['id'] ) ? sanitize_html_class( (string) $somvio_qc_args['id'] ) : '';
$somvio_qc_extra   = isset( $somvio_qc_args['class'] ) ? sanitize_text_field( (string) $somvio_qc_args['class'] ) : '';
$somvio_qc_default = isset( $somvio_qc_args['default_service'] )
	? sanitize_key( (string) $somvio_qc_args['default_service'] )
	: 'regular-cleaning';

$somvio_qc_services  = somvio_get_quote_service_options();
$somvio_qc_props     = somvio_get_quote_property_options();
$somvio_qc_rates     = somvio_get_quote_rates();
$somvio_qc_slots     = isset( $somvio_qc_rates['time_slots'] ) ? $somvio_qc_rates['time_slots'] : array();
$somvio_qc_addons    = isset( $somvio_qc_rates['addons'] ) && is_array( $somvio_qc_rates['addons'] ) ? $somvio_qc_rates['addons'] : array();
$somvio_qc_symbol    = isset( $somvio_qc_rates['symbol'] ) ? (string) $somvio_qc_rates['symbol'] : '£';
$somvio_qc_uid       = 'qc-' . wp_unique_id();
$somvio_qc_icons_uri = get_stylesheet_directory_uri() . '/assets/icons/';

$somvio_qc_counters = array(
	'main_rooms'    => array(
		'label' => __( 'Main rooms', 'somvio' ),
		'min'   => 1,
		'max'   => 10,
		'value' => 1,
	),
	'bedrooms'      => array(
		'label' => __( 'Bedrooms', 'somvio' ),
		'min'   => 1,
		'max'   => 5,
		'value' => 1,
	),
	'bathrooms'     => array(
		'label' => __( 'Bathrooms', 'somvio' ),
		'min'   => 1,
		'max'   => 4,
		'value' => 1,
	),
	'linen_changes' => array(
		'label' => __( 'No. of Linen Changes', 'somvio' ),
		'min'   => 0,
		'max'   => 10,
		'value' => 0,
	),
);

if ( ! isset( $somvio_qc_services[ $somvio_qc_default ] ) ) {
	$somvio_qc_default = 'regular-cleaning';
}

$somvio_qc_classes = array( 'quote-card', 'quote-calculator' );
if ( 'solid' === $somvio_qc_variant ) {
	$somvio_qc_classes[] = 'quote-card--solid';
	$somvio_qc_classes[] = 'quote-calculator--solid';
}
if ( '' !== $somvio_qc_extra ) {
	foreach ( preg_split( '/\s+/', $somvio_qc_extra ) as $somvio_qc_extra_class ) {
		if ( '' !== $somvio_qc_extra_class ) {
			$somvio_qc_classes[] = $somvio_qc_extra_class;
		}
	}
}

$somvio_qc_class_attr = implode( ' ', array_map( 'sanitize_html_class', $somvio_qc_classes ) );
?>
<aside
	<?php if ( $somvio_qc_id ) : ?>
		id="<?php echo esc_attr( $somvio_qc_id ); ?>"
	<?php endif; ?>
	class="<?php echo esc_attr( $somvio_qc_class_attr ); ?>"
	data-quote-calculator
	data-step="1"
	data-quote-uid="<?php echo esc_attr( $somvio_qc_uid ); ?>"
	aria-label="<?php esc_attr_e( 'Get Your Instant Quote', 'somvio' ); ?>"
>
	<h2 class="quote-card__title" data-quote-title>
		<?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?>
	</h2>

	<form class="quote-card__form quote-calculator__form" data-quote-form novalidate>
		<?php /* —— Step 1: Property details —— */ ?>
		<div class="quote-calculator__step" data-quote-step="1" data-quote-panel>
			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-service">
					<?php esc_html_e( 'Service Type', 'somvio' ); ?>
				</label>
				<div class="quote-card__select-wrap">
					<select
						class="quote-card__select"
						id="<?php echo esc_attr( $somvio_qc_uid ); ?>-service"
						name="service"
						data-quote-field="service"
						required
					>
						<?php foreach ( $somvio_qc_services as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $somvio_qc_default, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="quote-card__chevron" aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</div>
			</div>

			<div class="quote-card__field quote-card__field--full" data-quote-property-wrap>
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-property">
					<?php esc_html_e( 'Property Type:', 'somvio' ); ?>
				</label>
				<div class="quote-card__select-wrap">
					<select
						class="quote-card__select"
						id="<?php echo esc_attr( $somvio_qc_uid ); ?>-property"
						name="property"
						data-quote-field="property"
						required
					>
						<?php foreach ( $somvio_qc_props as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( 'house', $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="quote-card__chevron" aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</div>
			</div>

			<div class="quote-calculator__counters" data-quote-counters>
				<?php foreach ( $somvio_qc_counters as $somvio_qc_ckey => $somvio_qc_counter ) : ?>
					<div
						class="quote-calculator__counter"
						data-quote-counter="<?php echo esc_attr( $somvio_qc_ckey ); ?>"
						hidden
					>
						<label
							class="quote-card__label"
							for="<?php echo esc_attr( $somvio_qc_uid . '-' . $somvio_qc_ckey ); ?>"
							data-quote-counter-label
						>
							<?php echo esc_html( $somvio_qc_counter['label'] ); ?>
						</label>
						<div class="quote-calculator__counter-control">
							<button
								type="button"
								class="quote-calculator__counter-btn quote-calculator__counter-btn--minus"
								data-quote-counter-dec
								aria-label="<?php echo esc_attr( sprintf( /* translators: %s: room type */ __( 'Decrease %s', 'somvio' ), $somvio_qc_counter['label'] ) ); ?>"
							>
								<span aria-hidden="true"><?php echo somvio_get_icon( 'icon-minus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</button>
							<input
								type="number"
								class="quote-calculator__counter-value"
								id="<?php echo esc_attr( $somvio_qc_uid . '-' . $somvio_qc_ckey ); ?>"
								name="<?php echo esc_attr( $somvio_qc_ckey ); ?>"
								data-quote-field="<?php echo esc_attr( $somvio_qc_ckey ); ?>"
								value="<?php echo esc_attr( (string) $somvio_qc_counter['value'] ); ?>"
								min="<?php echo esc_attr( (string) $somvio_qc_counter['min'] ); ?>"
								max="<?php echo esc_attr( (string) $somvio_qc_counter['max'] ); ?>"
								readonly
								aria-live="polite"
							>
							<button
								type="button"
								class="quote-calculator__counter-btn quote-calculator__counter-btn--plus"
								data-quote-counter-inc
								aria-label="<?php echo esc_attr( sprintf( /* translators: %s: room type */ __( 'Increase %s', 'somvio' ), $somvio_qc_counter['label'] ) ); ?>"
							>
								<span aria-hidden="true"><?php echo somvio_get_icon( 'icon-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<fieldset class="quote-calculator__welcome" data-quote-welcome hidden>
				<legend class="quote-card__label"><?php esc_html_e( 'Welcome Pack Required?', 'somvio' ); ?></legend>
				<div class="quote-calculator__welcome-options" role="radiogroup" aria-label="<?php esc_attr_e( 'Welcome Pack Required?', 'somvio' ); ?>">
					<label class="quote-calculator__welcome-option">
						<input type="radio" name="<?php echo esc_attr( $somvio_qc_uid ); ?>-welcome_pack" data-quote-field="welcome_pack" value="yes">
						<span><?php esc_html_e( 'Yes', 'somvio' ); ?></span>
					</label>
					<label class="quote-calculator__welcome-option is-selected">
						<input type="radio" name="<?php echo esc_attr( $somvio_qc_uid ); ?>-welcome_pack" data-quote-field="welcome_pack" value="no" checked>
						<span><?php esc_html_e( 'No', 'somvio' ); ?></span>
					</label>
				</div>
			</fieldset>
		</div>

		<?php /* —— Step 2: Extra Services (Deep / EOT / After Builders only) —— */ ?>
		<div class="quote-calculator__step" data-quote-step="2" data-quote-panel hidden>
			<p class="quote-card__label"><?php esc_html_e( 'Extra Services', 'somvio' ); ?></p>
			<div class="quote-calculator__addons" role="group" aria-label="<?php esc_attr_e( 'Extra services', 'somvio' ); ?>">
				<?php foreach ( $somvio_qc_addons as $somvio_qc_akey => $somvio_qc_addon ) : ?>
					<?php
					$somvio_qc_alabel = isset( $somvio_qc_addon['label'] ) ? (string) $somvio_qc_addon['label'] : $somvio_qc_akey;
					$somvio_qc_aprice = isset( $somvio_qc_addon['price'] ) ? (float) $somvio_qc_addon['price'] : 0;
					$somvio_qc_aicon  = isset( $somvio_qc_addon['icon'] ) ? (string) $somvio_qc_addon['icon'] : '';
					$somvio_qc_auri   = '';
					if ( '' !== $somvio_qc_aicon ) {
						$somvio_qc_auri  = $somvio_qc_icons_uri . $somvio_qc_aicon;
						$somvio_qc_apath = get_stylesheet_directory() . '/assets/icons/' . $somvio_qc_aicon;
						if ( file_exists( $somvio_qc_apath ) ) {
							$somvio_qc_auri .= '?v=' . rawurlencode( (string) filemtime( $somvio_qc_apath ) );
						}
					}
					?>
					<button
						type="button"
						class="quote-calculator__addon"
						data-quote-addon="<?php echo esc_attr( $somvio_qc_akey ); ?>"
						aria-pressed="false"
					>
						<?php if ( '' !== $somvio_qc_auri ) : ?>
							<img
								class="quote-calculator__addon-icon"
								src="<?php echo esc_url( $somvio_qc_auri ); ?>"
								alt=""
								width="28"
								height="28"
								decoding="async"
							>
						<?php endif; ?>
						<span class="quote-calculator__addon-label"><?php echo esc_html( $somvio_qc_alabel ); ?></span>
						<span class="quote-calculator__addon-price">
							<?php echo esc_html( $somvio_qc_symbol . number_format_i18n( $somvio_qc_aprice, 0 ) ); ?>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="hidden" name="addons" data-quote-field="addons" value="">
		</div>

		<?php /* —— Step 3: Date —— */ ?>
		<div class="quote-calculator__step" data-quote-step="3" data-quote-panel hidden>
			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-date-display">
					<?php esc_html_e( 'Preferred date:', 'somvio' ); ?>
				</label>
				<div class="quote-card__select-wrap">
					<input
						type="text"
						class="quote-card__select quote-calculator__date-input"
						id="<?php echo esc_attr( $somvio_qc_uid ); ?>-date-display"
						data-quote-date-display
						value=""
						placeholder="<?php esc_attr_e( 'Select date', 'somvio' ); ?>"
						readonly
						aria-live="polite"
					>
					<input type="hidden" name="date" data-quote-field="date" value="">
					<span class="quote-card__chevron" aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</div>
			</div>

			<div
				class="quote-calculator__calendar"
				data-quote-calendar
				role="group"
				aria-label="<?php esc_attr_e( 'Choose a date', 'somvio' ); ?>"
			>
				<div class="quote-calculator__cal-header">
					<button
						type="button"
						class="quote-calculator__cal-nav"
						data-quote-cal-prev
						aria-label="<?php esc_attr_e( 'Previous month', 'somvio' ); ?>"
					>
						<span class="quote-calculator__cal-nav-icon" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</button>
					<p class="quote-calculator__cal-month" data-quote-cal-label> </p>
					<button
						type="button"
						class="quote-calculator__cal-nav"
						data-quote-cal-next
						aria-label="<?php esc_attr_e( 'Next month', 'somvio' ); ?>"
					>
						<span class="quote-calculator__cal-nav-icon quote-calculator__cal-nav-icon--next" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</button>
				</div>
				<div class="quote-calculator__cal-weekdays" data-quote-cal-weekdays aria-hidden="true"></div>
				<div class="quote-calculator__cal-grid" data-quote-cal-grid role="listbox"></div>
			</div>
		</div>

		<?php /* —— Step 4: Time slots —— */ ?>
		<div class="quote-calculator__step" data-quote-step="4" data-quote-panel hidden>
			<div
				class="quote-calculator__slots"
				data-quote-slots
				role="radiogroup"
				aria-label="<?php esc_attr_e( 'Preferred time', 'somvio' ); ?>"
			>
				<?php foreach ( $somvio_qc_slots as $slot ) : ?>
					<?php
					$somvio_qc_slot_raw   = (string) $slot;
					$somvio_qc_slot_start = strpos( $somvio_qc_slot_raw, '-' ) !== false
						? trim( explode( '-', $somvio_qc_slot_raw, 2 )[0] )
						: $somvio_qc_slot_raw;
					?>
					<button
						type="button"
						class="quote-calculator__slot"
						data-quote-slot="<?php echo esc_attr( $somvio_qc_slot_raw ); ?>"
						role="radio"
						aria-checked="false"
					>
						<?php echo esc_html( $somvio_qc_slot_start ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="hidden" name="time" data-quote-field="time" value="">
			<p class="quote-calculator__field-error" data-quote-field-error="time" hidden role="alert"></p>
		</div>

		<?php /* —— Step 5: Contact —— */ ?>
		<div class="quote-calculator__step" data-quote-step="5" data-quote-panel hidden>
			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label is-required" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-name">
					<?php esc_html_e( 'Full name', 'somvio' ); ?>
					<span class="somvio-required" aria-hidden="true">*</span>
				</label>
				<input
					type="text"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-name"
					name="name"
					data-quote-field="name"
					autocomplete="name"
					placeholder="<?php esc_attr_e( 'Full Name', 'somvio' ); ?>"
					required
				>
				<p class="quote-calculator__field-error" data-quote-field-error="name" hidden role="alert"></p>
			</div>

			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label is-required" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-email">
					<?php esc_html_e( 'Email', 'somvio' ); ?>
					<span class="somvio-required" aria-hidden="true">*</span>
				</label>
				<input
					type="email"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-email"
					name="email"
					data-quote-field="email"
					autocomplete="email"
					inputmode="email"
					placeholder="<?php esc_attr_e( 'name@example.com', 'somvio' ); ?>"
					required
				>
				<p class="quote-calculator__field-error" data-quote-field-error="email" hidden role="alert"></p>
			</div>

			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label is-required" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-phone">
					<?php esc_html_e( 'Phone', 'somvio' ); ?>
					<span class="somvio-required" aria-hidden="true">*</span>
				</label>
				<input
					type="tel"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-phone"
					name="phone"
					data-quote-field="phone"
					autocomplete="tel"
					inputmode="tel"
					placeholder="<?php esc_attr_e( '+44 7000 000000', 'somvio' ); ?>"
					required
				>
				<p class="quote-calculator__field-error" data-quote-field-error="phone" hidden role="alert"></p>
			</div>

			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-comment">
					<?php esc_html_e( 'Comment', 'somvio' ); ?>
				</label>
				<textarea
					class="quote-calculator__textarea"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-comment"
					name="comment"
					data-quote-field="comment"
					rows="5"
					placeholder="<?php esc_attr_e( 'Any special instructions or comments...', 'somvio' ); ?>"
				></textarea>
			</div>

			<div class="quote-calculator__summary" data-quote-summary>
				<p class="quote-calculator__summary-label"><?php esc_html_e( 'Estimated total', 'somvio' ); ?></p>
				<p class="quote-calculator__summary-total" data-price-total aria-hidden="false">£0.00</p>
				<p class="quote-calculator__summary-note"><?php esc_html_e( 'Preview only — final price confirmed after review.', 'somvio' ); ?></p>
				<p class="sr-only" data-price-live aria-live="polite" aria-atomic="true"></p>
			</div>
		</div>

		<?php /* —— Success —— */ ?>
		<div class="quote-calculator__step quote-calculator__step--success" data-quote-step="6" data-quote-panel hidden>
			<div class="quote-calculator__success">
				<span class="quote-calculator__success-icon" aria-hidden="true">
					<?php echo somvio_get_icon( 'icon-check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<p class="quote-calculator__success-title"><?php esc_html_e( 'Thank you!', 'somvio' ); ?></p>
				<p class="quote-calculator__success-subtitle"><?php esc_html_e( 'Your request has been sent', 'somvio' ); ?></p>
				<p class="quote-calculator__success-text">
					<?php esc_html_e( 'We’ll contact you shortly to confirm the details.', 'somvio' ); ?>
				</p>
			</div>
		</div>

		<p class="quote-calculator__error" data-quote-error hidden role="alert"></p>

		<div class="quote-card__footer quote-calculator__footer" data-quote-footer>
			<div class="quote-calculator__actions">
				<button
					type="button"
					class="btn btn--outline btn--sm btn--has-icon quote-calculator__back"
					data-quote-back
					hidden
				>
					<span class="btn__icon" aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="btn__label"><?php esc_html_e( 'Back', 'somvio' ); ?></span>
				</button>
				<button
					type="button"
					class="btn btn--primary btn--sm btn--has-icon"
					data-quote-next
					aria-busy="false"
				>
					<span class="quote-calculator__spinner" data-quote-spinner hidden aria-hidden="true"></span>
					<span class="btn__label" data-quote-next-label><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
					<span class="btn__icon" data-quote-next-icon aria-hidden="true">
						<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</button>
			</div>
			<p class="quote-card__step" data-quote-step-label>
				<?php
				/* translators: 1: current step, 2: total steps */
				echo esc_html( sprintf( __( 'Step %1$d of %2$d', 'somvio' ), 1, 4 ) );
				?>
			</p>
		</div>
	</form>
</aside>
