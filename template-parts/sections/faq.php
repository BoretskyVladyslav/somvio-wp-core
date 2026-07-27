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
);

/**
 * Optional FAQ variant from get_template_part() $args.
 *
 * @var array{variant?: string} $args
 */
$somvio_faq_args    = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$somvio_faq_variant = isset( $somvio_faq_args['variant'] ) ? sanitize_key( (string) $somvio_faq_args['variant'] ) : 'default';

if ( 'airbnb' === $somvio_faq_variant ) {
	$somvio_faq_items = array(
		array(
			'id'    => 'airbnb-turnover-time',
			'title' => __( 'How fast can you turn over my Airbnb?', 'somvio' ),
			'text'  => __( 'We specialise in same-day and same-window turnovers across Glasgow. Book a slot that matches your guest checkout and next check-in, and our team will clean, reset, and prepare the property on time.', 'somvio' ),
			'open'  => true,
		),
		array(
			'id'    => 'airbnb-linen',
			'title' => __( 'Do you change bed linen and towels?', 'somvio' ),
			'text'  => __( 'Yes. You can add linen changes when booking. We strip used bedding, remake beds with fresh linen, and replace towels so the property is guest-ready.', 'somvio' ),
			'open'  => false,
		),
		array(
			'id'    => 'airbnb-welcome-pack',
			'title' => __( 'Can you restock a welcome pack?', 'somvio' ),
			'text'  => __( 'Absolutely. Select “Welcome Pack Required” during booking and tell us what to include in the comments — toiletries, tea/coffee, or your own host checklist.', 'somvio' ),
			'open'  => false,
		),
		array(
			'id'    => 'airbnb-keys-access',
			'title' => __( 'How do cleaners access the property?', 'somvio' ),
			'text'  => __( 'Share your preferred access method when booking (key safe code, lockbox, smart lock, or meet-and-greet). We confirm access details before every visit.', 'somvio' ),
			'open'  => false,
		),
		array(
			'id'    => 'airbnb-quality',
			'title' => __( 'Will the clean meet guest review standards?', 'somvio' ),
			'text'  => __( 'Yes. Our Airbnb cleans follow a hospitality checklist focused on bathrooms, kitchens, beds, and high-touch surfaces — the details that protect your ratings.', 'somvio' ),
			'open'  => false,
		),
		array(
			'id'    => 'airbnb-recurring',
			'title' => __( 'Can I book recurring Airbnb cleans?', 'somvio' ),
			'text'  => __( 'Yes. Contact us after your first booking to set up a recurring schedule aligned with your calendar, including last-minute turnovers where capacity allows.', 'somvio' ),
			'open'  => false,
		),
	);
}
?>
<section class="faq" aria-labelledby="faq-title">
	<div class="faq__inner">
		<header class="faq__header reveal-on-scroll">
			<h2 id="faq-title" class="faq__title">
				<?php
				echo esc_html(
					'airbnb' === $somvio_faq_variant
						? __( 'Airbnb Cleaning FAQ', 'somvio' )
						: __( 'Frequently Asked Questions', 'somvio' )
				);
				?>
			</h2>
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
