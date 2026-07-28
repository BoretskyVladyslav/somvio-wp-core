<?php
/**
 * Why Choose Somvio — advantages section markup.
 *
 * Figma node: 300:1393 (desktop) / 300:2790 (mobile carousel).
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

/**
 * Simple chevron SVG for carousel nav — no masks / duplicate IDs.
 *
 * @param string $dir left|right
 * @return string
 */
$somvio_why_choose_chevron = static function ( $dir ) {
	$is_left = 'left' === $dir;
	$d       = $is_left
		? 'M14.5 6.5L9 12l5.5 5.5'
		: 'M9.5 6.5L15 12l-5.5 5.5';

	return '<svg class="why-choose__nav-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
		. '<path d="' . esc_attr( $d ) . '" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';
};
?>
<section class="why-choose" aria-labelledby="why-choose-title" data-why-choose>
	<div class="why-choose__inner">
		<header class="why-choose__header">
			<p class="why-choose__badge"><?php esc_html_e( 'Our Promate', 'somvio' ); ?></p>
			<h2 id="why-choose-title" class="why-choose__title reveal-on-scroll">
				<span class="why-choose__title-line"><?php esc_html_e( 'Why Choose', 'somvio' ); ?></span>
				<span class="why-choose__title-line"><?php esc_html_e( 'Somvio?', 'somvio' ); ?></span>
			</h2>
		</header>

		<div class="why-choose__carousel">
			<div class="why-choose__viewport" data-why-choose-viewport>
				<ul class="why-choose__grid" data-why-choose-track>
					<?php foreach ( $somvio_advantages as $index => $item ) : ?>
						<li
							class="why-choose__card<?php echo 1 === (int) $index ? ' why-choose__card--offset' : ''; ?>"
							data-why-choose-slide
						>
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

			<div class="why-choose__controls" data-why-choose-controls hidden>
				<button
					class="why-choose__nav why-choose__nav--prev"
					type="button"
					data-why-choose-prev
					aria-label="<?php esc_attr_e( 'Previous advantage', 'somvio' ); ?>"
				>
					<span class="why-choose__nav-icon" aria-hidden="true">
						<?php echo $somvio_why_choose_chevron( 'left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</button>
				<button
					class="why-choose__nav why-choose__nav--next"
					type="button"
					data-why-choose-next
					aria-label="<?php esc_attr_e( 'Next advantage', 'somvio' ); ?>"
				>
					<span class="why-choose__nav-icon" aria-hidden="true">
						<?php echo $somvio_why_choose_chevron( 'right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</button>
			</div>
		</div>
	</div>
</section>
