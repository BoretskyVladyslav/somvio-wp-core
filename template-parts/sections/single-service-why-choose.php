<?php
/**
 * Single Service — Why Choose Our [Service] benefits grid.
 *
 * Figma node: 366:5552
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_service_title = get_the_title();
$somvio_heading       = $somvio_service_title
	? sprintf(
		/* translators: %s: service page title, e.g. Regular Cleaning */
		__( 'Why Choose Our %s?', 'somvio' ),
		$somvio_service_title
	)
	: __( 'Why Choose Our Regular Cleaning?', 'somvio' );

$somvio_benefits = array(
	array(
		'icon'  => 'icon-user',
		'title' => __( 'Instant Online Quote', 'somvio' ),
		'text'  => __( 'Background-checked, vetted, and highly experienced cleaners.', 'somvio' ),
	),
	array(
		'icon'  => 'icon-star',
		'title' => __( 'Satisfaction Guarantee:', 'somvio' ),
		'text'  => __( "We're not happy until you are. If you're unsatisfied, we re-clean for free.", 'somvio' ),
	),
	array(
		'icon'  => 'icon-calendar',
		'title' => __( 'Book & Pay Online', 'somvio' ),
		'text'  => __( 'Book a professional clean in minutes, anytime, on any device.', 'somvio' ),
	),
	array(
		'icon'  => 'icon-user',
		'title' => __( 'Fully Insured', 'somvio' ),
		'text'  => __( 'Background-checked, vetted, and highly experienced cleaners.', 'somvio' ),
	),
);
?>
<section
	class="service-why"
	aria-labelledby="service-why-title"
	data-why-choose
>
	<div class="service-why__inner">
		<header class="service-why__header">
			<p class="service-why__badge"><?php esc_html_e( 'Our Promise', 'somvio' ); ?></p>
			<h2 id="service-why-title" class="service-why__title reveal-on-scroll">
				<?php echo esc_html( $somvio_heading ); ?>
			</h2>
		</header>

		<div class="service-why__carousel">
			<ul class="service-why__grid" data-why-choose-track>
				<?php foreach ( $somvio_benefits as $index => $item ) : ?>
					<li
						class="service-why__card reveal-on-scroll"
						style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.08 ) ); ?>s;"
						data-why-choose-slide
					>
						<div class="service-why__card-top">
							<div class="service-why__icon-wrap">
								<span class="service-why__icon" aria-hidden="true">
									<?php
									echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( $item['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</span>
							</div>
							<h3 class="service-why__card-title"><?php echo esc_html( $item['title'] ); ?></h3>
						</div>
						<p class="service-why__card-text"><?php echo esc_html( $item['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="service-why__controls" data-why-choose-controls hidden>
				<button
					class="service-why__nav service-why__nav--prev"
					type="button"
					data-why-choose-prev
					aria-label="<?php esc_attr_e( 'Previous benefit', 'somvio' ); ?>"
				>
					<span class="service-why__nav-icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-left' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</button>
				<button
					class="service-why__nav service-why__nav--next"
					type="button"
					data-why-choose-next
					aria-label="<?php esc_attr_e( 'Next benefit', 'somvio' ); ?>"
				>
					<span class="service-why__nav-icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-right' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</button>
			</div>
		</div>
	</div>
</section>
