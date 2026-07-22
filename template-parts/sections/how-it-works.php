<?php
/**
 * How It Works section markup — Figma 300:1456.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return icon SVG with unique mask IDs for repeated accordion instances.
 *
 * @param string $name Icon name.
 * @param string $uid  Unique suffix.
 * @return string
 */
$somvio_accordion_icon = static function ( $name, $uid ) {
	$svg = function_exists( 'somvio_get_icon' ) ? somvio_get_icon( $name ) : '';

	if ( '' === $svg ) {
		return '';
	}

	return preg_replace(
		'/mask0_(\d+)_(\d+)/',
		'mask0_$1_$2_' . preg_replace( '/[^a-zA-Z0-9_-]/', '', $uid ),
		$svg
	);
};

$somvio_steps = array(
	array(
		'id'    => 'get-quote',
		'title' => __( 'Get Quote', 'somvio' ),
		'text'  => __( 'Enter your details and parameters to get an instant, fixed price', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'choose-date',
		'title' => __( 'Choose Date', 'somvio' ),
		'text'  => __( 'Pick a convenient date and time that works best for your schedule.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'secure-payment',
		'title' => __( 'Secure Payment', 'somvio' ),
		'text'  => __( 'Complete your booking with a fast and secure online payment.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'we-clean',
		'title' => __( 'We Clean', 'somvio' ),
		'text'  => __( 'Our professional cleaners arrive on time and handle everything for you.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'enjoy-clean-home',
		'title' => __( 'Enjoy Clean Home', 'somvio' ),
		'text'  => __( 'Relax and enjoy a fresh, spotless space — stress-free.', 'somvio' ),
		'open'  => false,
	),
);
?>
<section class="how-it-works" aria-labelledby="how-it-works-title">
	<div class="how-it-works__inner">
		<header class="how-it-works__header">
			<p class="how-it-works__badge reveal-on-scroll"><?php esc_html_e( 'Process Steps', 'somvio' ); ?></p>
			<h2 id="how-it-works-title" class="how-it-works__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
				<?php esc_html_e( 'How It Works', 'somvio' ); ?>
			</h2>
			<p class="how-it-works__subtitle reveal-on-scroll" style="--reveal-delay: 0.1s;">
				<?php esc_html_e( 'Simple. Fast. Convenient.', 'somvio' ); ?>
			</p>
		</header>

		<div class="how-it-works__grid">
			<div class="how-it-works__media reveal-on-scroll" style="--reveal-delay: 0.05s;">
				<img
					class="how-it-works__image"
					src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/how-it-works-preview.jpg' ); ?>"
					alt="<?php esc_attr_e( 'Modern clean living room with city view', 'somvio' ); ?>"
					width="795"
					height="516"
					loading="lazy"
					decoding="async"
				>
			</div>

			<div class="how-it-works__accordion" data-accordion>
				<?php foreach ( $somvio_steps as $index => $step ) : ?>
					<?php
					$is_open   = ! empty( $step['open'] );
					$panel_id  = 'how-it-works-panel-' . $step['id'];
					$button_id = 'how-it-works-trigger-' . $step['id'];
					$uid       = $step['id'] . '-' . (string) $index;
					?>
					<div
						class="how-it-works__item reveal-on-scroll<?php echo $is_open ? ' is-open' : ''; ?>"
						style="--reveal-delay: <?php echo esc_attr( (string) ( 0.1 + ( $index * 0.05 ) ) ); ?>s;"
						data-accordion-item
					>
						<button
							type="button"
							id="<?php echo esc_attr( $button_id ); ?>"
							class="how-it-works__trigger"
							aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							data-accordion-trigger
						>
							<span class="how-it-works__item-title"><?php echo esc_html( $step['title'] ); ?></span>
							<span class="how-it-works__icon" aria-hidden="true">
								<span class="how-it-works__icon-plus">
									<?php echo $somvio_accordion_icon( 'icon-plus', $uid . '-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
								<span class="how-it-works__icon-minus">
									<?php echo $somvio_accordion_icon( 'icon-minus', $uid . '-minus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							</span>
						</button>
						<div
							id="<?php echo esc_attr( $panel_id ); ?>"
							class="how-it-works__panel"
							role="region"
							aria-labelledby="<?php echo esc_attr( $button_id ); ?>"
							<?php echo $is_open ? '' : 'hidden'; ?>
							data-accordion-panel
						>
							<div class="how-it-works__panel-inner" data-accordion-panel-inner>
								<p class="how-it-works__item-text"><?php echo esc_html( $step['text'] ); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
