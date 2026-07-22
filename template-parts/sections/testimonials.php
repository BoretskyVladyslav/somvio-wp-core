<?php
/**
 * Testimonials section markup — Figma 325:5029.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_images_uri = get_stylesheet_directory_uri() . '/assets/images';

$somvio_testimonials = array(
	array(
		'title'  => __( 'Absolutely flawless deep clean!', 'somvio' ),
		'quote'  => __( 'The team from Somvio did a deep clean of my living room and kitchen, and the results are incredible—everything looks brand new. The booking process was so easy with their online calculator, and I love that they use eco-friendly products safe for my pets. Will definitely be setting up a regular cleaning schedule!', 'somvio' ),
		'author' => __( '– Sarah M., London (Verified Regular/Deep Clean Customer)', 'somvio' ),
		'offset' => false,
	),
	array(
		'title'  => __( 'A lifesaver for my Airbnb turnovers!', 'somvio' ),
		'quote'  => __( 'As a property host, quick and spotless turnovers are everything. Somvio has been an absolute lifesaver. They are reliable, thorough, and their attention to detail keeps my guests leaving 5-star reviews. The professional look of the team gives me total peace of mind.', 'somvio' ),
		'author' => __( '– David K., Manchester (Verified Airbnb Premier Clean Customer)', 'somvio' ),
		'offset' => true,
	),
	array(
		'title'  => __( 'Got my full deposit back!', 'somvio' ),
		'quote'  => __( 'I booked their End of Tenancy cleaning service before moving out. The instant quote widget gave me a fixed price right away with zero hidden fees. The cleaners left the apartment immaculate, and the landlord passed the inspection with no complaints. Highly recommend!', 'somvio' ),
		'author' => __( '– Emma L., Birmingham (Verified End of Tenancy Customer)', 'somvio' ),
		'offset' => false,
	),
);

$somvio_trust_badges = array(
	array(
		'logo'   => 'logo-google.svg',
		'alt'    => __( 'Google', 'somvio' ),
		'width'  => 50,
		'height' => 50,
		'score'  => '5/5',
		'count'  => __( '636+ reviews', 'somvio' ),
		'mod'    => 'google',
	),
	array(
		'logo'   => 'logo-yelp.svg',
		'alt'    => __( 'Yelp', 'somvio' ),
		'width'  => 41,
		'height' => 50,
		'score'  => '4.7/5',
		'count'  => __( '33+ reviews', 'somvio' ),
		'mod'    => 'yelp',
	),
	array(
		'logo'   => 'logo-trustpilot.svg',
		'alt'    => __( 'Trustpilot', 'somvio' ),
		'width'  => 52,
		'height' => 50,
		'score'  => '4.9/5',
		'count'  => __( '76+ reviews', 'somvio' ),
		'mod'    => 'trustpilot',
	),
);
?>
<section class="testimonials" aria-labelledby="testimonials-title">
	<div class="testimonials__inner">
		<header class="testimonials__header">
			<p class="testimonials__badge"><?php esc_html_e( 'Social Proof', 'somvio' ); ?></p>
			<h2 id="testimonials-title" class="testimonials__title reveal-on-scroll">
				<?php esc_html_e( 'Testimonials', 'somvio' ); ?>
			</h2>
		</header>

		<ul class="testimonials__grid">
			<?php foreach ( $somvio_testimonials as $index => $card ) : ?>
				<li class="testimonials__card reveal-on-scroll<?php echo ! empty( $card['offset'] ) ? ' testimonials__card--offset' : ''; ?>" style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.1 ) ); ?>s;">
					<div class="testimonials__stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'somvio' ); ?>">
						<?php for ( $i = 0; $i < 5; $i++ ) : ?>
							<span class="testimonials__star" aria-hidden="true">
								<?php
								$star = function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-star-rating' ) : '';
								echo $star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</span>
						<?php endfor; ?>
					</div>
					<h3 class="testimonials__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<blockquote class="testimonials__quote">
						<p class="testimonials__quote-text"><?php echo esc_html( '"' . $card['quote'] . '"' ); ?></p>
					</blockquote>
					<p class="testimonials__author"><?php echo esc_html( $card['author'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="testimonials__trust-bar">
			<?php foreach ( $somvio_trust_badges as $badge ) : ?>
				<div class="testimonials__trust-item testimonials__trust-item--<?php echo esc_attr( $badge['mod'] ); ?>">
					<img
						class="testimonials__trust-logo"
						src="<?php echo esc_url( $somvio_images_uri . '/' . $badge['logo'] ); ?>"
						alt="<?php echo esc_attr( $badge['alt'] ); ?>"
						width="<?php echo esc_attr( (string) $badge['width'] ); ?>"
						height="<?php echo esc_attr( (string) $badge['height'] ); ?>"
						loading="lazy"
						decoding="async"
					>
					<div class="testimonials__trust-meta">
						<span class="testimonials__trust-score"><?php echo esc_html( $badge['score'] ); ?></span>
						<span class="testimonials__trust-count"><?php echo esc_html( $badge['count'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
