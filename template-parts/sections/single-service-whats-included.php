<?php
/**
 * Single Service — What's Included room checklist.
 *
 * Figma: 366:5375 (desktop) / 300:2943 (mobile carousel).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_quote_anchor = '#somvio-instant-quote';
$somvio_book_url     = function_exists( 'get_permalink' ) && get_permalink()
	? esc_url( get_permalink() . $somvio_quote_anchor )
	: $somvio_quote_anchor;

$somvio_rooms = array(
	array(
		'icon'  => 'icon-room-living',
		'title' => __( 'Living Room', 'somvio' ),
		'items' => array(
			__( 'Dust all accessible surfaces', 'somvio' ),
			__( 'Vacuum carpets and rugs', 'somvio' ),
			__( 'Mop hard floors', 'somvio' ),
			__( 'Clean mirrors', 'somvio' ),
			__( 'Wipe furniture', 'somvio' ),
			__( 'Empty bins', 'somvio' ),
			__( 'Remove cobwebs', 'somvio' ),
		),
	),
	array(
		'icon'  => 'icon-room-kitchen',
		'title' => __( 'Kitchen', 'somvio' ),
		'items' => array(
			__( 'Clean countertops', 'somvio' ),
			__( 'Wipe cabinet fronts', 'somvio' ),
			__( 'Clean sink and taps', 'somvio' ),
			__( 'Clean hob', 'somvio' ),
			__( 'Exterior of appliances', 'somvio' ),
			__( 'Vacuum & mop floor', 'somvio' ),
			__( 'Empty rubbish bins', 'somvio' ),
		),
	),
	array(
		'icon'  => 'icon-room-bathroom',
		'title' => __( 'Bathroom', 'somvio' ),
		'items' => array(
			__( 'Clean shower and bathtub', 'somvio' ),
			__( 'Sanitize toilet', 'somvio' ),
			__( 'Polish mirrors', 'somvio' ),
			__( 'Clean sink and taps', 'somvio' ),
			__( 'Wipe tiles', 'somvio' ),
			__( 'Mop floors', 'somvio' ),
			__( 'Empty bins', 'somvio' ),
		),
	),
	array(
		'icon'  => 'icon-room-bedroom',
		'title' => __( 'Bedroom', 'somvio' ),
		'items' => array(
			__( 'Dust furniture', 'somvio' ),
			__( 'Make beds', 'somvio' ),
			__( 'Vacuum carpets', 'somvio' ),
			__( 'Mop hard flooring', 'somvio' ),
			__( 'Clean mirrors', 'somvio' ),
			__( 'Empty bins', 'somvio' ),
		),
	),
	array(
		'icon'  => 'icon-room-hallway',
		'title' => __( 'Hallway', 'somvio' ),
		'items' => array(
			__( 'Vacuum flooring', 'somvio' ),
			__( 'Mop hard floors', 'somvio' ),
			__( 'Dust surfaces', 'somvio' ),
			__( 'Clean mirrors', 'somvio' ),
			__( 'Remove cobwebs', 'somvio' ),
		),
	),
);
?>
<section class="whats-included" aria-labelledby="whats-included-title">
	<div class="whats-included__inner">
		<header class="whats-included__header">
			<p class="whats-included__badge"><?php esc_html_e( "What's Included", 'somvio' ); ?></p>
			<h2 id="whats-included-title" class="whats-included__title reveal-on-scroll">
				<?php esc_html_e( "What's Included", 'somvio' ); ?>
			</h2>
			<p class="whats-included__subtitle reveal-on-scroll" style="--reveal-delay: 0.05s;">
				<?php esc_html_e( 'Our standard regular cleaning service covers all essential living areas.', 'somvio' ); ?>
			</p>
		</header>

		<ul class="whats-included__grid">
			<?php foreach ( $somvio_rooms as $index => $room ) : ?>
				<li class="whats-included__card reveal-on-scroll" style="--reveal-delay: <?php echo esc_attr( (string) ( ( $index % 3 ) * 0.08 ) ); ?>s;">
					<div class="whats-included__icon-wrap">
						<span class="whats-included__icon" aria-hidden="true">
							<?php
							echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( $room['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</span>
					</div>
					<h3 class="whats-included__card-title"><?php echo esc_html( $room['title'] ); ?></h3>
					<ul class="whats-included__list">
						<?php foreach ( $room['items'] as $item ) : ?>
							<li class="whats-included__list-item"><?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="whats-included__actions reveal-on-scroll">
			<a class="btn btn--primary btn--md" href="<?php echo esc_url( $somvio_book_url ); ?>">
				<span class="btn__label"><?php esc_html_e( 'Book Now', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</section>
