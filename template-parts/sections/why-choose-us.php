<?php
/**
 * Why Choose Somvio — advantages section markup.
 *
 * Figma node: 300:1393
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_advantages = array(
	array(
		'icon'  => 'icon-user',
		'title' => __( 'Trusted Professionals', 'somvio' ),
		'text'  => __( 'Background-checked, vetted, and highly experienced cleaners.', 'somvio' ),
	),
	array(
		'icon'  => 'icon-star',
		'title' => __( 'Satisfaction Guarantee:', 'somvio' ),
		'text'  => __( "We're not happy until you are. If you're unsatisfied, we re-clean for free.", 'somvio' ),
	),
	array(
		'icon'  => 'icon-calendar',
		'title' => __( 'Easy Online Booking', 'somvio' ),
		'text'  => __( 'Book a professional clean in minutes, anytime, on any device.', 'somvio' ),
	),
);
?>
<section class="why-choose" aria-labelledby="why-choose-title">
	<div class="why-choose__inner">
		<header class="why-choose__header">
			<p class="why-choose__badge"><?php esc_html_e( 'Our Promate', 'somvio' ); ?></p>
			<h2 id="why-choose-title" class="why-choose__title reveal-on-scroll">
				<span class="why-choose__title-line"><?php esc_html_e( 'Why Choose', 'somvio' ); ?></span>
				<span class="why-choose__title-line"><?php esc_html_e( 'Somvio?', 'somvio' ); ?></span>
			</h2>
		</header>

		<ul class="why-choose__grid">
			<?php foreach ( $somvio_advantages as $index => $item ) : ?>
				<li class="why-choose__card reveal-on-scroll<?php echo 1 === (int) $index ? ' why-choose__card--offset' : ''; ?>" style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.1 ) ); ?>s;">
					<div class="why-choose__icon-wrap">
						<span class="why-choose__icon" aria-hidden="true">
							<?php
							// Trusted local theme SVG from assets/icons/.
							echo somvio_get_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</span>
					</div>
					<h3 class="why-choose__card-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="why-choose__card-text"><?php echo esc_html( $item['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
