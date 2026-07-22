<?php
/**
 * Single Service — Gallery / See the Difference slider.
 *
 * Figma: 366:5439 (desktop) / 300:3006 (mobile peek carousel).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_gallery_base = get_stylesheet_directory() . '/assets/images/';
$somvio_gallery_uri  = get_stylesheet_directory_uri() . '/assets/images/';

$somvio_gallery_images = array(
	array(
		'file' => 'service-gallery-1.jpg',
		'alt'  => __( 'Modern dark kitchen with island seating', 'somvio' ),
	),
	array(
		'file' => 'service-gallery-2.jpg',
		'alt'  => __( 'Professional cleaner wiping a conference table', 'somvio' ),
	),
	array(
		'file' => 'service-gallery-3.jpg',
		'alt'  => __( 'Luxury home library with wood cabinetry', 'somvio' ),
	),
);

/**
 * Triple the set for a seamless infinite-loop track (JS rewinds clones).
 *
 * @var array<int, array{file: string, alt: string, loop: int}>
 */
$somvio_gallery_slides = array();
for ( $somvio_loop = 0; $somvio_loop < 3; $somvio_loop++ ) {
	foreach ( $somvio_gallery_images as $somvio_image ) {
		$somvio_gallery_slides[] = array_merge(
			$somvio_image,
			array( 'loop' => $somvio_loop )
		);
	}
}

$somvio_unique_count = count( $somvio_gallery_images );
?>
<section
	class="service-gallery"
	aria-labelledby="service-gallery-title"
	data-service-gallery
	data-service-gallery-count="<?php echo esc_attr( (string) $somvio_unique_count ); ?>"
>
	<div class="service-gallery__inner">
		<header class="service-gallery__header">
			<p class="service-gallery__badge"><?php esc_html_e( 'Gallery', 'somvio' ); ?></p>
			<h2 id="service-gallery-title" class="service-gallery__title reveal-on-scroll">
				<?php esc_html_e( 'See the Difference', 'somvio' ); ?>
			</h2>
			<p class="service-gallery__subtitle reveal-on-scroll" style="--reveal-delay: 0.05s;">
				<?php
				esc_html_e(
					'Explore examples of beautifully maintained homes cleaned by our professional team.',
					'somvio'
				);
				?>
			</p>
		</header>

		<div class="service-gallery__carousel" data-service-gallery-carousel>
			<div class="service-gallery__viewport" data-service-gallery-viewport>
				<ul class="service-gallery__track" data-service-gallery-track>
					<?php foreach ( $somvio_gallery_slides as $index => $slide ) : ?>
						<?php
						$path = $somvio_gallery_base . $slide['file'];
						$uri  = $somvio_gallery_uri . $slide['file'];

						if ( file_exists( $path ) ) {
							$uri .= '?v=' . rawurlencode( (string) filemtime( $path ) );
						}
						?>
						<li
							class="service-gallery__slide"
							data-service-gallery-slide
							data-gallery-index="<?php echo esc_attr( (string) ( $index % $somvio_unique_count ) ); ?>"
						>
							<figure class="service-gallery__media">
								<?php if ( file_exists( $path ) ) : ?>
									<img
										class="service-gallery__image"
										src="<?php echo esc_url( $uri ); ?>"
										alt="<?php echo esc_attr( $slide['alt'] ); ?>"
										width="1170"
										height="658"
										loading="<?php echo 0 === (int) $slide['loop'] && $index < 3 ? 'eager' : 'lazy'; ?>"
										decoding="async"
										draggable="false"
									>
								<?php else : ?>
									<span class="service-gallery__media-missing">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: relative image path */
												__( 'Missing image: %s', 'somvio' ),
												'assets/images/' . $slide['file']
											)
										);
										?>
									</span>
								<?php endif; ?>
							</figure>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="service-gallery__stage">
				<button
					class="service-gallery__nav service-gallery__nav--prev"
					type="button"
					data-service-gallery-prev
					aria-label="<?php esc_attr_e( 'Previous gallery image', 'somvio' ); ?>"
				>
					<span class="service-gallery__nav-icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-left' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</button>
				<button
					class="service-gallery__nav service-gallery__nav--next"
					type="button"
					data-service-gallery-next
					aria-label="<?php esc_attr_e( 'Next gallery image', 'somvio' ); ?>"
				>
					<span class="service-gallery__nav-icon" aria-hidden="true">
						<?php
						echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-right' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</span>
				</button>
			</div>
		</div>
	</div>
</section>
