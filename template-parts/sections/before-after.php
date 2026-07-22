<?php
/**
 * Before/After comparison section markup — Figma 323:5028.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$somvio_images_dir = get_stylesheet_directory() . '/assets/images';
$somvio_images_uri = get_stylesheet_directory_uri() . '/assets/images';

$somvio_before_path = $somvio_images_dir . '/before-cleaning.jpg';
$somvio_after_path  = $somvio_images_dir . '/after-cleaning.jpg';
$somvio_before_uri  = $somvio_images_uri . '/before-cleaning.jpg';
$somvio_after_uri   = $somvio_images_uri . '/after-cleaning.jpg';

$somvio_has_before = file_exists( $somvio_before_path );
$somvio_has_after  = file_exists( $somvio_after_path );
$somvio_has_images = $somvio_has_before && $somvio_has_after;

$somvio_handle_icon = function_exists( 'somvio_get_icon' ) ? somvio_get_icon( 'icon-expand-all' ) : '';
?>
<section class="before-after" aria-labelledby="before-after-title">
	<div class="before-after__inner">
		<header class="before-after__header">
			<p class="before-after__badge"><?php esc_html_e( 'Before / After', 'somvio' ); ?></p>
			<h2 id="before-after-title" class="before-after__title reveal-on-scroll">
				<?php esc_html_e( 'See the Difference', 'somvio' ); ?>
			</h2>
			<p class="before-after__subtitle">
				<?php esc_html_e( 'Spotless Results Every Time', 'somvio' ); ?>
			</p>
		</header>

		<?php if ( $somvio_has_images ) : ?>
			<div
				class="before-after__slider"
				data-before-after
				style="--ba-pos: 50%;"
			>
				<div class="before-after__frame">
					<div class="before-after__layer before-after__layer--after">
						<img
							class="before-after__image"
							src="<?php echo esc_url( $somvio_after_uri ); ?>"
							alt="<?php esc_attr_e( 'Living room after professional cleaning', 'somvio' ); ?>"
							width="1170"
							height="658"
							loading="lazy"
							decoding="async"
							draggable="false"
						>
					</div>

					<div class="before-after__layer before-after__layer--before" data-before-after-before>
						<img
							class="before-after__image"
							src="<?php echo esc_url( $somvio_before_uri ); ?>"
							alt="<?php esc_attr_e( 'Living room before professional cleaning', 'somvio' ); ?>"
							width="1170"
							height="658"
							loading="lazy"
							decoding="async"
							draggable="false"
						>
					</div>

					<div class="before-after__divider" data-before-after-divider aria-hidden="true">
						<span class="before-after__handle">
							<?php if ( '' !== $somvio_handle_icon ) : ?>
								<span class="before-after__handle-icon">
									<?php echo $somvio_handle_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							<?php endif; ?>
						</span>
					</div>
				</div>

				<label class="before-after__control-label screen-reader-text" for="before-after-range">
					<?php esc_html_e( 'Compare before and after cleaning', 'somvio' ); ?>
				</label>
				<input
					id="before-after-range"
					class="before-after__range"
					type="range"
					min="0"
					max="100"
					value="50"
					step="0.1"
					data-before-after-range
					aria-valuemin="0"
					aria-valuemax="100"
					aria-valuenow="50"
					aria-valuetext="<?php esc_attr_e( '50 percent before', 'somvio' ); ?>"
				>
			</div>
		<?php else : ?>
			<p class="before-after__missing">
				<?php
				if ( ! $somvio_has_before ) {
					esc_html_e( 'Missing image: assets/images/before-cleaning.jpg', 'somvio' );
				}
				if ( ! $somvio_has_after ) {
					echo $somvio_has_before ? '<br>' : '';
					esc_html_e( 'Missing image: assets/images/after-cleaning.jpg', 'somvio' );
				}
				?>
			</p>
		<?php endif; ?>
	</div>
</section>
