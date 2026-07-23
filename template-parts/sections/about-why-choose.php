<?php
/**
 * About Us — Why Choose Somvio benefits grid.
 *
 * Figma node: 300:2112 (6-card promise grid).
 * Note: composition 389:6012 is Numbers + Social Proof (not this grid).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_about_why_cards = array(
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
	// Bottom row mirrors top row in Figma 300:2112.
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
<section class="about-why" aria-labelledby="about-why-title">
	<div class="about-why__inner">
		<header class="about-why__header">
			<p class="about-why__badge"><?php esc_html_e( 'Our Promise', 'somvio' ); ?></p>
			<h2 id="about-why-title" class="about-why__title reveal-on-scroll">
				<span class="about-why__title-line"><?php esc_html_e( 'Why Choose', 'somvio' ); ?></span>
				<span class="about-why__title-line"><?php esc_html_e( 'Somvio?', 'somvio' ); ?></span>
			</h2>
		</header>

		<ul class="about-why__grid">
			<?php foreach ( $somvio_about_why_cards as $index => $item ) : ?>
				<li
					class="about-why__card reveal-on-scroll"
					style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.08 ) ); ?>s;"
				>
					<div class="about-why__card-top">
						<div class="about-why__icon-wrap">
							<span class="about-why__icon" aria-hidden="true">
								<?php
								echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( $item['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						</div>
						<h3 class="about-why__card-title"><?php echo esc_html( $item['title'] ); ?></h3>
					</div>
					<p class="about-why__card-text"><?php echo esc_html( $item['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
