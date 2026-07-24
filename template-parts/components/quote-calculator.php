<?php
/**
 * Multi-step Instant Quote calculator component.
 *
 * Figma: 300:1766 (step1), 300:1852 (step2), 300:1818 (step3),
 * 300:1792 (step4), 409:6039 (success).
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
$somvio_qc_uid       = 'qc-' . wp_unique_id();

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
	data-quote-uid="<?php echo esc_attr( $somvio_qc_uid ); ?>"
	aria-label="<?php esc_attr_e( 'Get Your Instant Quote', 'somvio' ); ?>"
>
	<h2 class="quote-card__title" data-quote-title>
		<?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?>
	</h2>

	<form class="quote-card__form quote-calculator__form" data-quote-form novalidate>
		<?php /* —— Step 1: Property details (Figma 300:1766) —— */ ?>
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

			<div class="quote-card__field quote-card__field--full">
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

			<div class="quote-card__row">
				<div class="quote-card__field">
					<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-bedrooms">
						<?php esc_html_e( 'Bedrooms', 'somvio' ); ?>
					</label>
					<div class="quote-card__select-wrap">
						<select
							class="quote-card__select"
							id="<?php echo esc_attr( $somvio_qc_uid ); ?>-bedrooms"
							name="bedrooms"
							data-quote-field="bedrooms"
							required
						>
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( 1, $i ); ?>>
									<?php echo esc_html( 5 === $i ? '5+' : (string) $i ); ?>
								</option>
							<?php endfor; ?>
						</select>
						<span class="quote-card__chevron" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>
				</div>

				<div class="quote-card__field">
					<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-bathrooms">
						<?php esc_html_e( 'Bathrooms', 'somvio' ); ?>
					</label>
					<div class="quote-card__select-wrap">
						<select
							class="quote-card__select"
							id="<?php echo esc_attr( $somvio_qc_uid ); ?>-bathrooms"
							name="bathrooms"
							data-quote-field="bathrooms"
							required
						>
							<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
								<option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( 2, $i ); ?>>
									<?php echo esc_html( 4 === $i ? '4+' : (string) $i ); ?>
								</option>
							<?php endfor; ?>
						</select>
						<span class="quote-card__chevron" aria-hidden="true">
							<?php echo somvio_get_icon( 'icon-chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<?php /* —— Step 2: Date (Figma 300:1852) —— */ ?>
		<div class="quote-calculator__step" data-quote-step="2" data-quote-panel hidden>
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

		<?php /* —— Step 3: Time slots (Figma 300:1818) —— */ ?>
		<div class="quote-calculator__step" data-quote-step="3" data-quote-panel hidden>
			<div
				class="quote-calculator__slots"
				data-quote-slots
				role="radiogroup"
				aria-label="<?php esc_attr_e( 'Preferred time', 'somvio' ); ?>"
			>
				<?php foreach ( $somvio_qc_slots as $index => $slot ) : ?>
					<button
						type="button"
						class="quote-calculator__slot"
						data-quote-slot="<?php echo esc_attr( $slot ); ?>"
						role="radio"
						aria-checked="false"
					>
						<?php echo esc_html( str_replace( '-', ' - ', $slot ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="hidden" name="time" data-quote-field="time" value="">
		</div>

		<?php /* —— Step 4: Contact (Figma 300:1792) —— */ ?>
		<div class="quote-calculator__step" data-quote-step="4" data-quote-panel hidden>
			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-name">
					<?php esc_html_e( 'Full name', 'somvio' ); ?>
				</label>
				<input
					type="text"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-name"
					name="name"
					data-quote-field="name"
					autocomplete="name"
					required
				>
			</div>

			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-email">
					<?php esc_html_e( 'Email', 'somvio' ); ?>
				</label>
				<input
					type="email"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-email"
					name="email"
					data-quote-field="email"
					autocomplete="email"
					required
				>
			</div>

			<div class="quote-card__field quote-card__field--full">
				<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qc_uid ); ?>-phone">
					<?php esc_html_e( 'Phone', 'somvio' ); ?>
				</label>
				<input
					type="tel"
					class="quote-card__select quote-calculator__input"
					id="<?php echo esc_attr( $somvio_qc_uid ); ?>-phone"
					name="phone"
					data-quote-field="phone"
					autocomplete="tel"
					required
				>
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
				></textarea>
			</div>

			<div class="quote-calculator__summary" data-quote-summary>
				<p class="quote-calculator__summary-label"><?php esc_html_e( 'Estimated total', 'somvio' ); ?></p>
				<p class="quote-calculator__summary-total" data-price-total aria-hidden="false">£0.00</p>
				<p class="quote-calculator__summary-note"><?php esc_html_e( 'Preview only — final price confirmed after review.', 'somvio' ); ?></p>
				<p class="sr-only" data-price-live aria-live="polite" aria-atomic="true"></p>
			</div>
		</div>

		<?php /* —— Step 5: Success (Figma 409:6039) —— */ ?>
		<div class="quote-calculator__step quote-calculator__step--success" data-quote-step="5" data-quote-panel hidden>
			<div class="quote-calculator__success">
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
				>
					<span class="btn__label" data-quote-next-label><?php esc_html_e( 'Next Step', 'somvio' ); ?></span>
					<span class="btn__icon" aria-hidden="true">
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
