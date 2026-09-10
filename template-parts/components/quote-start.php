<?php
/**
 * Homepage hero quick-start: service + postcode → booking calculator.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_qs_args    = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$somvio_qs_extra   = isset( $somvio_qs_args['class'] ) ? sanitize_text_field( (string) $somvio_qs_args['class'] ) : '';
$somvio_qs_default = isset( $somvio_qs_args['default_service'] )
	? sanitize_key( (string) $somvio_qs_args['default_service'] )
	: 'regular-cleaning';
$somvio_qs_services = function_exists( 'somvio_get_quote_service_options' )
	? somvio_get_quote_service_options()
	: array();
$somvio_qs_uid = 'qs-' . wp_unique_id();

if ( ! isset( $somvio_qs_services[ $somvio_qs_default ] ) ) {
	$somvio_qs_default = 'regular-cleaning';
}

$somvio_qs_classes = array( 'quote-card', 'quote-start' );
if ( '' !== $somvio_qs_extra ) {
	foreach ( preg_split( '/\s+/', $somvio_qs_extra ) as $somvio_qs_extra_class ) {
		if ( '' !== $somvio_qs_extra_class ) {
			$somvio_qs_classes[] = $somvio_qs_extra_class;
		}
	}
}

$somvio_qs_class_attr = implode( ' ', array_map( 'sanitize_html_class', $somvio_qs_classes ) );
?>
<aside
	id="quote"
	class="<?php echo esc_attr( $somvio_qs_class_attr ); ?>"
	data-quote-start
	aria-label="<?php esc_attr_e( 'Get Your Instant Quote', 'somvio' ); ?>"
>
	<h2 class="quote-card__title"><?php esc_html_e( 'Get Your Instant Quote', 'somvio' ); ?></h2>

	<form class="quote-card__form quote-start__form" data-quote-start-form novalidate>
		<div class="quote-card__field quote-card__field--full">
			<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qs_uid ); ?>-service">
				<?php esc_html_e( 'Service Type', 'somvio' ); ?>
			</label>
			<div class="quote-card__select-wrap">
				<select
					class="quote-card__select"
					id="<?php echo esc_attr( $somvio_qs_uid ); ?>-service"
					name="service"
					data-quote-start-service
					required
				>
					<?php foreach ( $somvio_qs_services as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $somvio_qs_default, $value ); ?>>
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
			<label class="quote-card__label" for="<?php echo esc_attr( $somvio_qs_uid ); ?>-postcode">
				<?php esc_html_e( 'Postcode', 'somvio' ); ?>
			</label>
			<input
				class="quote-card__select quote-start__input"
				id="<?php echo esc_attr( $somvio_qs_uid ); ?>-postcode"
				type="text"
				name="postcode"
				data-quote-start-postcode
				autocomplete="postal-code"
				inputmode="text"
				placeholder="<?php esc_attr_e( 'e.g. G20 8NN', 'somvio' ); ?>"
				required
			>
			<p class="quote-start__error" id="<?php echo esc_attr( $somvio_qs_uid ); ?>-error" data-quote-start-error hidden role="alert"></p>
		</div>

		<div class="quote-card__footer">
			<button type="submit" class="btn btn--primary btn--sm btn--has-icon" data-quote-start-submit>
				<span class="btn__label"><?php esc_html_e( 'Get Instant Quote', 'somvio' ); ?></span>
				<span class="btn__icon" aria-hidden="true">
					<?php echo somvio_get_icon( 'icon-arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			</button>
		</div>
	</form>
</aside>
