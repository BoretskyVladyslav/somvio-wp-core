<?php
/**
 * Services grid section markup — Figma 300:1401.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_images_uri   = get_stylesheet_directory_uri() . '/assets/images';
$somvio_images_dir   = get_stylesheet_directory() . '/assets/images';
$somvio_services_url = esc_url( home_url( '/services/' ) );

$somvio_services = array(
	array(
		'slug'  => 'regular-cleaning',
		'image' => 'service-regular-cleaning.png',
		'title' => __( 'Regular Cleaning', 'somvio' ),
		'text'  => __( 'Keep your home consistently clean, tidy, and fresh.', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
	),
	array(
		'slug'  => 'deep-cleaning',
		'image' => 'service-deep-cleaning.png',
		'title' => __( 'Deep Cleaning', 'somvio' ),
		'text'  => __( 'Keep your home consistently clean, tidy, and fresh.', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
	),
	array(
		'slug'  => 'end-of-tenancy',
		'image' => 'service-end-of-tenancy.png',
		'title' => __( 'End of Tenancy', 'somvio' ),
		'text'  => __( 'Keep your home consistently clean, tidy, and fresh.', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
	),
	array(
		'slug'  => 'airbnb-cleaning',
		'image' => 'service-airbnb-cleaning.png',
		'title' => __( 'Airbnb Cleaning', 'somvio' ),
		'text'  => __( 'Keep your home consistently clean, tidy, and fresh.', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
	),
	array(
		'slug'  => 'after-builders',
		'image' => 'service-after-builders.png',
		'title' => __( 'After Builders', 'somvio' ),
		'text'  => __( 'Keep your home consistently clean, tidy, and fresh.', 'somvio' ),
		'price' => __( 'From £35', 'somvio' ),
	),
);
?>
<section class="services-grid" aria-labelledby="services-grid-title">
	<div class="services-grid__inner">
		<header class="services-grid__header">
			<p class="services-grid__badge"><?php esc_html_e( 'Our Services', 'somvio' ); ?></p>
			<h2 id="services-grid-title" class="services-grid__title reveal-on-scroll">
				<?php esc_html_e( 'Tailored Cleaning Solutions', 'somvio' ); ?>
			</h2>
			<p class="services-grid__subtitle">
				<?php esc_html_e( 'We offer a wide range of professional cleaning services to suit your exact needs.', 'somvio' ); ?>
			</p>
		</header>

		<ul class="services-grid__list">
			<?php foreach ( $somvio_services as $index => $service ) : ?>
				<?php
				$image_path = $somvio_images_dir . '/' . $service['image'];
				$image_url  = esc_url( $somvio_images_uri . '/' . $service['image'] );
				$service_url = function_exists( 'somvio_get_service_page_url' )
					? esc_url( somvio_get_service_page_url( $service['slug'] ) )
					: esc_url( home_url( '/services/' . $service['slug'] . '/' ) );
				?>
				<li class="services-card reveal-on-scroll" style="--reveal-delay: <?php echo esc_attr( (string) ( ( $index % 3 ) * 0.1 ) ); ?>s;">
					<a class="services-card__link" href="<?php echo $service_url; ?>">
						<div class="services-card__media">
							<?php if ( file_exists( $image_path ) ) : ?>
								<img
									class="services-card__image"
									src="<?php echo $image_url; ?>"
									alt="<?php echo esc_attr( $service['title'] ); ?>"
									width="370"
									height="320"
									loading="lazy"
									decoding="async"
								>
							<?php else : ?>
								<span class="services-card__media-missing">
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
						<div class="services-card__body">
							<h3 class="services-card__title"><?php echo esc_html( $service['title'] ); ?></h3>
							<p class="services-card__text"><?php echo esc_html( $service['text'] ); ?></p>
							<p class="services-card__price"><?php echo esc_html( $service['price'] ); ?></p>
						</div>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="services-grid__actions">
			<a class="btn btn--primary btn--md" href="<?php echo $somvio_services_url; ?>">
				<span class="btn__label"><?php esc_html_e( 'View All Services', 'somvio' ); ?></span>
			</a>
		</div>
	</div>
</section>
