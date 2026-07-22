<?php
/**
 * Services list zig-zag section — Figma 300:2170 (desktop) / 300:2648 (mobile).
 *
 * Desktop: alternating image ↔ text panels (gap 30px, h 400).
 * Mobile: image above text; price + CTA on one row.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_images_uri = get_stylesheet_directory_uri() . '/assets/images';
$somvio_images_dir = get_stylesheet_directory() . '/assets/images';
$somvio_book_url   = function_exists( 'somvio_get_book_now_url' ) ? somvio_get_book_now_url() : home_url( '/booking/' );

/*
 * Figma 300:2170 — five service_item rows (no Office).
 * Even index = media start; odd = media end.
 */
$somvio_list_services = array(
	array(
		'id'    => 'regular-cleaning',
		'image' => 'service-regular-cleaning.png',
		'title' => __( 'Regular Cleaning', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
		'text'  => __( 'Keep your home consistently fresh, clean, and welcoming with our routine maintenance service. Our trusted professionals handle dusting, vacuuming, and surface sanitization on a schedule that perfectly fits your lifestyle. Enjoy a stress-free, tidy living space every single week without lifting a finger.', 'somvio' ),
	),
	array(
		'id'    => 'deep-cleaning',
		'image' => 'service-deep-cleaning.png',
		'title' => __( 'Deep Cleaning', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
		'text'  => __( 'Give your home a comprehensive reset with our intensive, top-to-bottom detailing service. We target hidden dirt, stubborn grime, and overlooked areas that regular cleaning simply misses. It is the perfect seasonal refresh to restore absolute health and sparkle to your living spaces.', 'somvio' ),
	),
	array(
		'id'    => 'end-of-tenancy',
		'image' => 'service-end-of-tenancy.png',
		'title' => __( 'End of Tenancy', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
		'text'  => __( 'Move out with absolute confidence using our specialized deposit-back guaranteed cleaning service. Our team follows a rigorous post-cleaning inspection checklist to ensure every corner meets strict landlord standards. We take the stress out of moving by leaving your old property in flawless condition.', 'somvio' ),
	),
	array(
		'id'    => 'airbnb-cleaning',
		'image' => 'service-airbnb-cleaning.png',
		'title' => __( 'Airbnb Cleaning', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
		'text'  => __( 'Ensure a five-star guest experience with our ultra-reliable, high-speed turnover service. We meticulously clean, sanitize, and reset your rental property to look picture-perfect for every new arrival. Maximize your booking ratings while we handle the hard work behind the scenes.', 'somvio' ),
	),
	array(
		'id'    => 'after-builders',
		'image' => 'service-after-builders.png',
		'title' => __( 'After Builders', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
		'text'  => __( 'Clear away the heavy dust, debris, and residue left behind after your recent home renovation. Our professionals use specialized equipment to safely eliminate fine particles and construction mess from every surface. Step right into a beautifully completed, clean home ready for immediate living.', 'somvio' ),
	),
);
?>
<section class="services-list" aria-label="<?php esc_attr_e( 'Our services', 'somvio' ); ?>">
	<div class="services-list__inner">
		<?php foreach ( $somvio_list_services as $index => $service ) : ?>
			<?php
			$image_path  = $somvio_images_dir . '/' . $service['image'];
			$image_url   = esc_url( $somvio_images_uri . '/' . $service['image'] );
			$media_start = ( 0 === ( $index % 2 ) );
			$item_mod    = $media_start ? 'services-list__item--media-start' : 'services-list__item--media-end';
			?>
			<article
				id="<?php echo esc_attr( $service['id'] ); ?>"
				class="services-list__item <?php echo esc_attr( $item_mod ); ?> reveal-on-scroll"
				style="--reveal-delay: <?php echo esc_attr( (string) ( $index * 0.05 ) ); ?>s;"
			>
				<div class="services-list__media">
					<?php if ( file_exists( $image_path ) ) : ?>
						<img
							class="services-list__image"
							src="<?php echo $image_url; ?>"
							alt="<?php echo esc_attr( $service['title'] ); ?>"
							width="570"
							height="400"
							loading="lazy"
							decoding="async"
						>
					<?php else : ?>
						<span class="services-list__media-missing">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: relative image path */
									__( 'Missing image: assets/images/%s', 'somvio' ),
									$service['image']
								)
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<div class="services-list__body">
					<h3 class="services-list__title"><?php echo esc_html( $service['title'] ); ?></h3>
					<p class="services-list__price"><?php echo esc_html( $service['price'] ); ?></p>
					<p class="services-list__text"><?php echo esc_html( $service['text'] ); ?></p>
					<a class="btn btn--primary btn--sm btn--has-icon services-list__cta" href="<?php echo esc_url( $somvio_book_url ); ?>">
						<span class="btn__label"><?php esc_html_e( 'Book Now', 'somvio' ); ?></span>
						<span class="btn__icon" aria-hidden="true">
							<?php
							// Trusted local theme SVG from assets/icons/.
							echo function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-arrow-right' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</span>
					</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
