<?php
/**
 * Single Service — Our Story / overview section.
 *
 * Figma node: 362:5002
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_image_path = get_stylesheet_directory() . '/assets/images/service-single-story.jpg';
$somvio_image_uri  = get_stylesheet_directory_uri() . '/assets/images/service-single-story.jpg';

$somvio_story_features = array(
	__( 'Flexible Scheduling', 'somvio' ),
	__( 'Trusted & Insured Cleaners', 'somvio' ),
	__( 'Eco-Friendly Products', 'somvio' ),
	__( 'Satisfaction Guaranteed', 'somvio' ),
);
?>
<section class="service-story" aria-labelledby="service-story-title">
	<div class="service-story__inner">
		<div class="service-story__grid">
			<figure class="service-story__media reveal-on-scroll">
				<?php if ( file_exists( $somvio_image_path ) ) : ?>
					<img
						class="service-story__image"
						src="<?php echo esc_url( $somvio_image_uri ); ?>"
						alt="<?php esc_attr_e( 'Professionally cleaned modern bathroom interior', 'somvio' ); ?>"
						width="795"
						height="503"
						loading="lazy"
						decoding="async"
					>
				<?php else : ?>
					<span class="service-story__media-missing">
						<?php esc_html_e( 'Missing image: assets/images/service-single-story.jpg', 'somvio' ); ?>
					</span>
				<?php endif; ?>
			</figure>

			<div class="service-story__content">
				<p class="service-story__badge reveal-on-scroll"><?php esc_html_e( 'Our Story', 'somvio' ); ?></p>

				<h2 id="service-story-title" class="service-story__title reveal-on-scroll" style="--reveal-delay: 0.05s;">
					<?php esc_html_e( 'Keep Your Home Fresh, Clean & Comfortable Every Week', 'somvio' ); ?>
				</h2>

				<div class="service-story__copy reveal-on-scroll" style="--reveal-delay: 0.1s;">
					<p>
						<?php
						esc_html_e(
							"A clean home shouldn't take up all your free time. Our Regular Cleaning service is designed to keep your home consistently spotless with flexible weekly, bi-weekly, or monthly visits. Whether you live in an apartment, house, or rental property, our experienced cleaners deliver reliable results using professional equipment and eco-friendly products.",
							'somvio'
						);
						?>
					</p>
					<p>
						<?php
						esc_html_e(
							'We take care of everyday cleaning tasks, so you can enjoy more time doing what matters most. Every visit follows a detailed checklist to ensure every room is cleaned to the highest standard.',
							'somvio'
						);
						?>
					</p>
				</div>
			</div>
		</div>

		<ul class="service-story__features">
			<?php foreach ( $somvio_story_features as $index => $label ) : ?>
				<li class="service-story__feature reveal-on-scroll" style="--reveal-delay: <?php echo esc_attr( (string) ( 0.05 * $index ) ); ?>s;">
					<span class="service-story__feature-icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-check-circle' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
					<span class="service-story__feature-label"><?php echo esc_html( $label ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
