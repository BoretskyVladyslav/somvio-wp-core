<?php
/**
 * About Us — Our Story section.
 *
 * Figma node: 300:2032
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_image_path = get_stylesheet_directory() . '/assets/images/about-story.jpg';
$somvio_image_uri  = get_stylesheet_directory_uri() . '/assets/images/about-story.jpg';
?>
<section class="about-story" aria-labelledby="about-story-title">
	<div class="about-story__inner">
		<div class="about-story__grid">
			<figure class="about-story__media reveal-on-scroll">
				<?php if ( file_exists( $somvio_image_path ) ) : ?>
					<img
						class="about-story__image"
						src="<?php echo esc_url( $somvio_image_uri ); ?>"
						alt="<?php esc_attr_e( 'Modern living room interior with city skyline view', 'somvio' ); ?>"
						width="795"
						height="503"
						loading="lazy"
						decoding="async"
					>
				<?php else : ?>
					<span class="about-story__media-missing">
						<?php esc_html_e( 'Missing image: assets/images/about-story.jpg', 'somvio' ); ?>
					</span>
				<?php endif; ?>
			</figure>

			<div class="about-story__content">
				<p class="about-story__badge reveal-on-scroll"><?php esc_html_e( 'Our Story', 'somvio' ); ?></p>

				<h2 id="about-story-title" class="about-story__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
					<?php esc_html_e( 'Built Around Quality, Trust & Simplicity', 'somvio' ); ?>
				</h2>

				<div class="about-story__copy reveal-on-scroll" style="--reveal-delay: 0.1s;">
					<p>
						<?php
						esc_html_e(
							'Somvio was founded with one goal in mind — to make professional cleaning simple, affordable and reliable for everyone.',
							'somvio'
						);
						?>
					</p>
					<p>
						<?php
						esc_html_e(
							"Over the years, we've helped thousands of homeowners, landlords, tenants and businesses maintain spotless properties through carefully designed cleaning solutions. Every member of our team is professionally trained, fully insured and committed to delivering outstanding results.",
							'somvio'
						);
						?>
					</p>
					<p>
						<?php
						esc_html_e(
							'Whether you need a one-time deep clean or regular maintenance, we approach every booking with the same level of care and attention to detail.',
							'somvio'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>
