<?php
/**
 * About Us — By the Numbers stats grid.
 *
 * Figma node: 300:2124
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_about_stats = array(
	array(
		'value' => '5,000+',
		'label' => __( 'Happy Customers', 'somvio' ),
	),
	array(
		'value' => '4.9★',
		'label' => __( 'Average Rating', 'somvio' ),
	),
	array(
		'value' => '98%',
		'label' => __( 'Repeat Customers', 'somvio' ),
	),
	array(
		'value' => '7 Days',
		'label' => __( 'Customer Support', 'somvio' ),
	),
);
?>
<section class="about-numbers" aria-labelledby="about-numbers-title">
	<div class="about-numbers__inner">
		<header class="about-numbers__header">
			<p class="about-numbers__badge"><?php esc_html_e( 'Our Promise', 'somvio' ); ?></p>
			<h2 id="about-numbers-title" class="about-numbers__title reveal-on-scroll">
				<?php esc_html_e( 'By the Numbers', 'somvio' ); ?>
			</h2>
		</header>

		<ul class="about-numbers__grid">
			<?php foreach ( $somvio_about_stats as $index => $stat ) : ?>
				<li
					class="about-numbers__card reveal-on-scroll"
					style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.08 ) ); ?>s;"
				>
					<p class="about-numbers__value"><?php echo esc_html( $stat['value'] ); ?></p>
					<p class="about-numbers__label"><?php echo esc_html( $stat['label'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
