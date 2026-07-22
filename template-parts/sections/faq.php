<?php
/**
 * FAQ accordion section markup — Figma 300:2375 (Services slot 300:2177).
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
$somvio_faq_icon = static function ( $name, $uid ) {
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

$somvio_faq_items = array(
	array(
		'id'    => 'book-cleaning',
		'title' => __( 'How do I book a cleaning service?', 'somvio' ),
		'text'  => __( 'Booking is quick and simple. Select your preferred cleaning service, choose a convenient date and time, enter your details and securely complete your payment online.', 'somvio' ),
		'open'  => true,
	),
	array(
		'id'    => 'cleaners-insured',
		'title' => __( 'Are your cleaners insured?', 'somvio' ),
		'text'  => __( 'Yes. Every Somvio cleaner is fully insured and professionally trained, so your home and belongings are protected on every visit.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'cancel-reschedule',
		'title' => __( 'Can I cancel or reschedule my booking?', 'somvio' ),
		'text'  => __( 'Yes. You can cancel or reschedule through your booking confirmation details. Please check the notice period in your booking terms for any applicable fees.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'cleaning-supplies',
		'title' => __( 'Do I need to provide cleaning supplies?', 'somvio' ),
		'text'  => __( 'No. Our team brings professional-grade, eco-friendly cleaning products and equipment. If you prefer specific products, just let us know when booking.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'payment-methods',
		'title' => __( 'What payment methods do you accept?', 'somvio' ),
		'text'  => __( 'We accept major debit and credit cards through our secure online checkout. Your fixed price is confirmed before you pay.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'payment-secure',
		'title' => __( 'Is my payment secure?', 'somvio' ),
		'text'  => __( 'Yes. All payments are processed through encrypted, PCI-compliant payment providers. We never store your full card details on our servers.', 'somvio' ),
		'open'  => false,
	),
	array(
		'id'    => 'commercial-cleaning',
		'title' => __( 'Do you clean offices and commercial spaces?', 'somvio' ),
		'text'  => __( 'Yes. We provide commercial and office cleaning across the UK, with flexible schedules tailored to your business needs.', 'somvio' ),
		'open'  => false,
	),
);
?>
<section class="faq" aria-labelledby="faq-title">
	<div class="faq__inner">
		<header class="faq__header reveal-on-scroll">
			<h2 id="faq-title" class="faq__title"><?php esc_html_e( 'Frequently Asked Questions', 'somvio' ); ?></h2>
		</header>

		<div class="faq__accordion" data-accordion>
			<?php foreach ( $somvio_faq_items as $index => $item ) : ?>
				<?php
				$is_open   = ! empty( $item['open'] );
				$panel_id  = 'faq-panel-' . $item['id'];
				$button_id = 'faq-trigger-' . $item['id'];
				$uid       = 'faq-' . $item['id'] . '-' . (string) $index;
				?>
				<div
					class="faq__item reveal-on-scroll<?php echo $is_open ? ' is-open' : ''; ?>"
					style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.05 ) ); ?>s;"
					data-accordion-item
				>
					<button
						type="button"
						id="<?php echo esc_attr( $button_id ); ?>"
						class="faq__trigger"
						aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
						data-accordion-trigger
					>
						<span class="faq__item-title"><?php echo esc_html( $item['title'] ); ?></span>
						<span class="faq__icon" aria-hidden="true">
							<span class="faq__icon-plus">
								<?php echo $somvio_faq_icon( 'icon-plus', $uid . '-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<span class="faq__icon-minus">
								<?php echo $somvio_faq_icon( 'icon-minus', $uid . '-minus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</span>
					</button>
					<div
						id="<?php echo esc_attr( $panel_id ); ?>"
						class="faq__panel"
						role="region"
						aria-labelledby="<?php echo esc_attr( $button_id ); ?>"
						<?php echo $is_open ? '' : 'hidden'; ?>
						data-accordion-panel
					>
						<div class="faq__panel-inner" data-accordion-panel-inner>
							<p class="faq__item-text"><?php echo esc_html( $item['text'] ); ?></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
