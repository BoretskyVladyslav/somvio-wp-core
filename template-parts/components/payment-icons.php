<?php
/**
 * Payment method badges — Visa, Mastercard, Google Pay, Apple Pay.
 *
 * Figma: 460:5750–460:5783 (quote), footer brand slot 460:5524.
 *
 * Args:
 * - variant: 'default'|'sm' (footer uses sm ≈ 47×31)
 * - class: extra classes on the list
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_pay_args    = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$somvio_pay_variant = isset( $somvio_pay_args['variant'] ) ? sanitize_key( (string) $somvio_pay_args['variant'] ) : 'default';
$somvio_pay_extra   = isset( $somvio_pay_args['class'] ) ? sanitize_text_field( (string) $somvio_pay_args['class'] ) : '';

$somvio_pay_icons = array(
	array(
		'file' => 'visa.svg',
		'alt'  => 'Visa',
	),
	array(
		'file' => 'mastercard.svg',
		'alt'  => 'Mastercard',
	),
	array(
		'file' => 'google-pay.svg',
		'alt'  => 'Google Pay',
	),
	array(
		'file' => 'apple-pay.svg',
		'alt'  => 'Apple Pay',
	),
);

$somvio_pay_base = get_stylesheet_directory() . '/assets/icons/payments/';
$somvio_pay_uri  = get_stylesheet_directory_uri() . '/assets/icons/payments/';

$somvio_pay_classes = array( 'somvio-payment-icons' );
if ( 'sm' === $somvio_pay_variant ) {
	$somvio_pay_classes[] = 'somvio-payment-icons--sm';
}
if ( '' !== $somvio_pay_extra ) {
	foreach ( preg_split( '/\s+/', $somvio_pay_extra ) as $somvio_pay_class ) {
		if ( '' !== $somvio_pay_class ) {
			$somvio_pay_classes[] = sanitize_html_class( $somvio_pay_class );
		}
	}
}
?>
<ul
	class="<?php echo esc_attr( implode( ' ', $somvio_pay_classes ) ); ?>"
	aria-label="<?php esc_attr_e( 'Accepted payment methods', 'somvio' ); ?>"
>
	<?php foreach ( $somvio_pay_icons as $somvio_pay_icon ) : ?>
		<?php
		$somvio_pay_path = $somvio_pay_base . $somvio_pay_icon['file'];
		if ( ! file_exists( $somvio_pay_path ) ) {
			continue;
		}
		$somvio_pay_src = $somvio_pay_uri . $somvio_pay_icon['file'] . '?v=' . rawurlencode( (string) filemtime( $somvio_pay_path ) );
		?>
		<li class="somvio-payment-icons__item">
			<img
				class="somvio-payment-icons__img"
				src="<?php echo esc_url( $somvio_pay_src ); ?>"
				alt="<?php echo esc_attr( $somvio_pay_icon['alt'] ); ?>"
				width="<?php echo 'sm' === $somvio_pay_variant ? 47 : 61; ?>"
				height="<?php echo 'sm' === $somvio_pay_variant ? 31 : 40; ?>"
				loading="lazy"
				decoding="async"
			>
		</li>
	<?php endforeach; ?>
</ul>
