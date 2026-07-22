<?php
/**
 * Service benefits checklist strip — Figma 300:1734 (client copy).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_benefits = array(
	__( 'Instant Online Quote', 'somvio' ),
	__( 'Book & Pay Online', 'somvio' ),
	__( 'Fully Insured & Reliable', 'somvio' ),
	__( 'Satisfaction Guarantee', 'somvio' ),
);
?>
<section class="service-benefits" aria-label="<?php esc_attr_e( 'Service benefits', 'somvio' ); ?>">
	<div class="service-benefits__inner">
		<ul class="service-benefits__list">
			<?php foreach ( $somvio_benefits as $benefit ) : ?>
				<li class="service-benefits__item">
					<span class="service-benefits__icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-check-circle' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="service-benefits__label"><?php echo esc_html( $benefit ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
