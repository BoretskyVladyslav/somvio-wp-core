<?php
/**
 * Areas We Cover — city tag cloud.
 *
 * Args:
 * - variant: 'contact'|'footer' (spacing/density modifier)
 * - class: extra classes on the root
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_sa_args    = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$somvio_sa_variant = isset( $somvio_sa_args['variant'] ) ? sanitize_key( (string) $somvio_sa_args['variant'] ) : 'contact';
$somvio_sa_extra   = isset( $somvio_sa_args['class'] ) ? sanitize_text_field( (string) $somvio_sa_args['class'] ) : '';

if ( ! in_array( $somvio_sa_variant, array( 'contact', 'footer' ), true ) ) {
	$somvio_sa_variant = 'contact';
}

$somvio_sa_areas = function_exists( 'somvio_get_service_areas' ) ? somvio_get_service_areas() : array();
if ( ! $somvio_sa_areas ) {
	return;
}

$somvio_sa_classes = array(
	'service-areas',
	'service-areas--' . $somvio_sa_variant,
);
if ( '' !== $somvio_sa_extra ) {
	$somvio_sa_classes[] = $somvio_sa_extra;
}
?>
<div class="<?php echo esc_attr( implode( ' ', $somvio_sa_classes ) ); ?>">
	<p class="service-areas__heading"><?php esc_html_e( 'Areas We Cover', 'somvio' ); ?></p>
	<ul class="service-areas__list">
		<?php foreach ( $somvio_sa_areas as $somvio_sa_area ) : ?>
			<li class="service-areas__item">
				<span class="service-areas__tag"><?php echo esc_html( $somvio_sa_area ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
